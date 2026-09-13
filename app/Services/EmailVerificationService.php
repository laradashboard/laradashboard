<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Mailbox-existence email verification via AbstractAPI's free-tier email
 * validation endpoint, with a monthly quota tracked in-app so a free plan
 * never gets silently billed/blocked once the quota runs out.
 *
 * Tier 3 only — callers must run EmailDomainCheckService / EmailSubmissionValidator
 * local checks first. This service never calls the API for addresses that fail
 * local validation.
 *
 * Set the monthly limit setting to 0 for an unlimited/paid AbstractAPI plan.
 */
class EmailVerificationService
{
    private const API_URL = 'https://emailvalidation.abstractapi.com/v1/';

    public function __construct(
        private readonly EmailDomainCheckService $localCheck = new EmailDomainCheckService()
    ) {
    }

    public function isEnabled(): bool
    {
        return filter_var(
            config('settings.'.Setting::EMAIL_VERIFICATION_ENABLED, '0'),
            FILTER_VALIDATE_BOOLEAN
        ) && $this->apiKey() !== '';
    }

    /**
     * Monthly call quota. 0 means unlimited (e.g. a paid AbstractAPI plan).
     */
    public function getMonthlyLimit(): int
    {
        return max(0, (int) config('settings.'.Setting::EMAIL_VERIFICATION_MONTHLY_LIMIT, 100));
    }

    /**
     * How many AbstractAPI calls have been made so far this calendar month.
     */
    public function usageThisMonth(): int
    {
        return (int) Cache::get($this->usageCacheKey(), 0);
    }

    /**
     * Whether calling the API right now would stay within the configured monthly quota.
     */
    public function hasQuotaRemaining(): bool
    {
        $limit = $this->getMonthlyLimit();

        return $limit === 0 || $this->usageThisMonth() < $limit;
    }

    /**
     * Full deliverability check: local first, then API when enabled.
     *
     * Prefer EmailSubmissionValidator in form validation — this method remains
     * for settings UI, tests, and direct service use.
     */
    public function isDeliverable(string $email): bool
    {
        if (! $this->localCheck->isPlausiblyDeliverable($email)) {
            return false;
        }

        if (! $this->isEnabled() || ! $this->hasQuotaRemaining()) {
            return true;
        }

        return $this->verifyViaApi($email);
    }

    /**
     * Call AbstractAPI only — assumes local checks already passed.
     */
    public function verifyViaApi(string $email): bool
    {
        if (! $this->isEnabled() || ! $this->hasQuotaRemaining()) {
            return $this->localCheck->isPlausiblyDeliverable($email);
        }

        try {
            $response = Http::timeout(6)->get(self::API_URL, [
                'api_key' => $this->apiKey(),
                'email' => $email,
            ]);

            $this->recordUsage();

            if ($response->failed()) {
                Log::warning('AbstractAPI email verification request failed', [
                    'status' => $response->status(),
                ]);

                return $this->localCheck->isPlausiblyDeliverable($email);
            }

            return $this->isDeliverableFromResponse($response->json() ?? []);
        } catch (\Throwable $e) {
            Log::warning('AbstractAPI email verification error: '.$e->getMessage());

            return $this->localCheck->isPlausiblyDeliverable($email);
        }
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function isDeliverableFromResponse(array $result): bool
    {
        if ((bool) ($result['is_disposable_email']['value'] ?? false)) {
            return false;
        }

        if (($result['is_mx_found']['value'] ?? true) === false) {
            return false;
        }

        if (($result['deliverability'] ?? null) === 'UNDELIVERABLE') {
            return false;
        }

        if (($result['is_smtp_valid']['value'] ?? null) === false) {
            return false;
        }

        return true;
    }

    private function recordUsage(): void
    {
        $key = $this->usageCacheKey();
        $ttl = max(3600, (int) now()->diffInSeconds(now()->endOfMonth()));

        if (! Cache::has($key)) {
            Cache::put($key, 0, $ttl);
        }

        Cache::increment($key);
    }

    private function usageCacheKey(): string
    {
        return 'email_verification:usage:'.now()->format('Y-m');
    }

    private function apiKey(): string
    {
        return trim((string) config('settings.'.Setting::EMAIL_VERIFICATION_API_KEY, ''));
    }
}
