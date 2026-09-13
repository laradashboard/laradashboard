<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Enums\Hooks\CommonFilterHook;
use App\Models\Setting;
use App\Services\EmailDomainCheckService;
use App\Services\EmailSubmissionValidator;
use App\Support\Facades\Hook;
use Illuminate\Support\Facades\Cache;

class RegistrationGuardService
{
    public const HONEYPOT_FIELD = 'company_website';

    /**
     * @var list<string>
     */
    private const SPAM_NAME_PATTERNS = [
        '/graph\.org/i',
        '/coinbase/i',
        '/\bbtc\b/i',
        '/https?:\/\//i',
        '/www\./i',
        '/Transfer\s*№/iu',
        '/\+[\d.]+\s*BTC/i',
        '/->+/',
        '/telegram\.me/i',
        '/telegra\.ph/i',
    ];

    public function __construct(
        private readonly EmailDomainCheckService $emailDomainCheck,
        private readonly EmailSubmissionValidator $emailSubmissionValidator,
    ) {
    }

    public function isHoneypotEnabled(): bool
    {
        return filter_var(
            config('settings.'.Setting::AUTH_REGISTRATION_HONEYPOT_ENABLED, '1'),
            FILTER_VALIDATE_BOOLEAN
        );
    }

    public function isIpLimitEnabled(): bool
    {
        return filter_var(
            config('settings.'.Setting::AUTH_REGISTRATION_IP_LIMIT_ENABLED, '1'),
            FILTER_VALIDATE_BOOLEAN
        );
    }

    public function getMaxRegistrationsPerIpPerDay(): int
    {
        return max(0, (int) config('settings.'.Setting::AUTH_REGISTRATION_MAX_PER_IP_PER_DAY, 3));
    }

    public function isEmailDomainCheckEnabled(): bool
    {
        return filter_var(
            config('settings.'.Setting::AUTH_REGISTRATION_EMAIL_DOMAIN_CHECK_ENABLED, '1'),
            FILTER_VALIDATE_BOOLEAN
        );
    }

    public function shouldDeferWelcomeEmailUntilVerified(): bool
    {
        return filter_var(
            config('settings.'.Setting::AUTH_DEFER_WELCOME_EMAIL_UNTIL_VERIFIED, '1'),
            FILTER_VALIDATE_BOOLEAN
        );
    }

    public function hasExceededIpLimit(?string $ip): bool
    {
        if (! $this->isIpLimitEnabled() || $ip === null || $ip === '') {
            return false;
        }

        $max = $this->getMaxRegistrationsPerIpPerDay();

        if ($max === 0) {
            return false;
        }

        return (int) Cache::get($this->ipLimitCacheKey($ip), 0) >= $max;
    }

    public function recordRegistration(?string $ip): void
    {
        if (! $this->isIpLimitEnabled() || $ip === null || $ip === '') {
            return;
        }

        $key = $this->ipLimitCacheKey($ip);
        $ttl = max(60, now()->diffInSeconds(now()->endOfDay()));

        if (! Cache::has($key)) {
            Cache::put($key, 0, $ttl);
        }

        Cache::increment($key);
    }

    /**
     * @return array<string, mixed>
     */
    public function additionalValidationRules(): array
    {
        $rules = [];

        if ($this->isHoneypotEnabled()) {
            $rules[self::HONEYPOT_FIELD] = [
                'nullable',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (filled($value)) {
                        $fail(__('Registration could not be completed.'));
                    }
                },
            ];
        }

        $nameRule = function (string $attribute, mixed $value, \Closure $fail): void {
            if (! is_string($value) || ! $this->looksLikeSpamName($value)) {
                return;
            }

            $fail(__('Please enter a valid name.'));
        };

        $rules['first_name'][] = $nameRule;
        $rules['last_name'][] = $nameRule;

        if ($this->emailSubmissionValidator->shouldValidate()) {
            $rules['email'][] = $this->emailSubmissionValidator->validationClosure();
        }

        return Hook::applyFilters(CommonFilterHook::REGISTRATION_GUARD_VALIDATION_RULES, $rules);
    }

    /**
     * Whether the email's domain is a known disposable/throwaway provider.
     */
    public function isDisposableEmailDomain(string $email): bool
    {
        return $this->emailDomainCheck->isDisposableEmailDomain($email);
    }

    /**
     * Whether the email's domain resolves to a mail server (MX) or at least a host (A/AAAA).
     * Real DNS lookups are skipped during automated tests to avoid network dependency/flakiness.
     */
    public function domainAcceptsMail(string $email): bool
    {
        return $this->emailDomainCheck->domainAcceptsMail($email);
    }

    public function looksLikeSpamName(string $value): bool
    {
        $value = trim($value);

        if ($value === '') {
            return false;
        }

        $patterns = Hook::applyFilters(
            CommonFilterHook::REGISTRATION_SPAM_NAME_PATTERNS,
            self::SPAM_NAME_PATTERNS
        );

        foreach ($patterns as $pattern) {
            if (! is_string($pattern) || $pattern === '') {
                continue;
            }

            if (@preg_match($pattern, $value) === 1) {
                return true;
            }
        }

        return false;
    }

    private function ipLimitCacheKey(string $ip): string
    {
        return 'registration:ip:'.$ip.':'.now()->toDateString();
    }
}
