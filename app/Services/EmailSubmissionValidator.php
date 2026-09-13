<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Setting;
use App\Support\Facades\Hook;
use App\Enums\Hooks\CommonFilterHook;

/**
 * Three-tier email validation for registration and public forms:
 *
 * 1. Syntax/format (free, always when this validator runs)
 * 2. Local domain checks — disposable list + DNS MX/A/AAAA (free)
 * 3. AbstractAPI mailbox verification (paid quota) — only after tier 2 passes
 */
class EmailSubmissionValidator
{
    public function __construct(
        private readonly EmailDomainCheckService $localCheck,
        private readonly EmailVerificationService $apiVerification,
    ) {
    }

    public function isLocalDomainCheckEnabled(): bool
    {
        return filter_var(
            config('settings.'.Setting::AUTH_REGISTRATION_EMAIL_DOMAIN_CHECK_ENABLED, '1'),
            FILTER_VALIDATE_BOOLEAN
        );
    }

    public function isApiVerificationEnabled(): bool
    {
        return $this->apiVerification->isEnabled();
    }

    /**
     * Whether any tier beyond Laravel's built-in `email` rule should run.
     */
    public function shouldValidate(): bool
    {
        return $this->isLocalDomainCheckEnabled() || $this->isApiVerificationEnabled();
    }

    /**
     * Validate an email through all enabled tiers. Returns an error message or null.
     */
    public function validate(string $email): ?string
    {
        if (! $this->shouldValidate()) {
            return null;
        }

        if (! $this->localCheck->isValidFormat($email)) {
            return __('Please enter a valid email address.');
        }

        if ($this->shouldRunLocalDomainChecks()) {
            if ($this->localCheck->isDisposableEmailDomain($email)) {
                return __('Please use a permanent email address rather than a disposable/temporary one.');
            }

            if (! $this->localCheck->domainAcceptsMail($email)) {
                return __('The email domain does not appear to accept mail. Please check for typos.');
            }
        }

        if ($this->shouldRunApiVerification()) {
            if (! $this->apiVerification->verifyViaApi($email)) {
                return __('This email address does not appear to be deliverable. Please check it and try again.');
            }
        }

        return null;
    }

    /**
     * Laravel validation closure for email fields.
     */
    public function validationClosure(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            if (! is_string($value) || $value === '') {
                return;
            }

            $message = $this->validate($value);

            if ($message !== null) {
                $fail($message);
            }
        };
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function validationRulesFor(string $field): array
    {
        if (! $this->shouldValidate()) {
            return [];
        }

        return Hook::applyFilters(
            CommonFilterHook::EMAIL_SUBMISSION_VALIDATION_RULES,
            [$field => [$this->validationClosure()]]
        );
    }

    /**
     * Local DNS/disposable checks run when enabled, or whenever API verification
     * is on (to avoid wasting quota on addresses local checks would reject).
     */
    private function shouldRunLocalDomainChecks(): bool
    {
        return $this->isLocalDomainCheckEnabled() || $this->isApiVerificationEnabled();
    }

    private function shouldRunApiVerification(): bool
    {
        return $this->isApiVerificationEnabled() && $this->apiVerification->hasQuotaRemaining();
    }
}
