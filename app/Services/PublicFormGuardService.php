<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Hooks\CommonFilterHook;
use App\Services\Auth\RegistrationGuardService;
use App\Support\Facades\Hook;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Shared anti-spam guards for public-facing forms (Livewire modules, contact
 * pages, etc.). Email validation uses the same three-tier pipeline as
 * registration via EmailSubmissionValidator (format → local DNS/disposable →
 * AbstractAPI only when enabled and local checks pass).
 */
class PublicFormGuardService
{
    public function __construct(
        private readonly RegistrationGuardService $registrationGuard,
        private readonly RecaptchaService $recaptchaService,
        private readonly EmailSubmissionValidator $emailSubmissionValidator,
    ) {
    }

    public function honeypotField(): string
    {
        return RegistrationGuardService::HONEYPOT_FIELD;
    }

    public function isHoneypotEnabled(): bool
    {
        return $this->registrationGuard->isHoneypotEnabled();
    }

    public function isEmailDomainCheckEnabled(): bool
    {
        return $this->emailSubmissionValidator->isLocalDomainCheckEnabled();
    }

    public function isEmailValidationEnabled(): bool
    {
        return $this->emailSubmissionValidator->shouldValidate();
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function honeypotRules(): array
    {
        if (! $this->isHoneypotEnabled()) {
            return [];
        }

        $field = $this->honeypotField();

        return [
            $field => [
                'nullable',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (filled($value)) {
                        $fail(__('Your submission could not be processed.'));
                    }
                },
            ],
        ];
    }

    /**
     * Append a local disposable/MX email rule to an existing rules array.
     *
     * @param  array<string, list<mixed>>  $rules
     * @return array<string, list<mixed>>
     */
    public function appendEmailRule(array $rules, string $field): array
    {
        if (! $this->emailSubmissionValidator->shouldValidate()) {
            return $rules;
        }

        $rules[$field] = $this->normalizeFieldRules($rules[$field] ?? []);
        $rules[$field][] = $this->emailSubmissionValidator->validationClosure();

        return $rules;
    }

    /**
     * Append spam-text rules for the given fields.
     *
     * @param  array<string, list<mixed>>  $rules
     * @param  list<string>  $fields
     * @return array<string, list<mixed>>
     */
    public function appendSpamTextRules(array $rules, array $fields): array
    {
        $validator = $this->spamTextValidator();

        foreach ($fields as $field) {
            $rules[$field] = $this->normalizeFieldRules($rules[$field] ?? []);
            $rules[$field][] = $validator;
        }

        return $rules;
    }

    /**
     * Merge honeypot, email, and spam-text rules into a Livewire rule set.
     *
     * @param  array<string, list<mixed>>  $rules
     * @param  list<string>  $emailFields
     * @param  list<string>  $spamTextFields
     * @return array<string, list<mixed>>
     */
    public function mergeRules(
        array $rules,
        array $emailFields = [],
        array $spamTextFields = [],
        bool $includeAdvancedEmailValidation = true,
    ): array {
        $merged = array_merge($rules, $this->honeypotRules());

        if ($includeAdvancedEmailValidation) {
            foreach ($emailFields as $field) {
                $merged = $this->appendEmailRule($merged, $field);
            }
        }

        $merged = $this->appendSpamTextRules($merged, $spamTextFields);

        return Hook::applyFilters(CommonFilterHook::PUBLIC_FORM_GUARD_VALIDATION_RULES, $merged);
    }

    /**
     * Whether any submitted email fails advanced validation (disposable domain,
     * DNS/MX, or API verification). Used to silently reject public form spam.
     *
     * @param  list<string>  $emails
     */
    public function emailsFailAdvancedValidation(array $emails): bool
    {
        if (! $this->emailSubmissionValidator->shouldValidate()) {
            return false;
        }

        foreach ($emails as $email) {
            if (! is_string($email) || $email === '') {
                continue;
            }

            if ($this->emailSubmissionValidator->validate($email) !== null) {
                return true;
            }
        }

        return false;
    }

    public function verifyRecaptcha(?string $token, string $page): bool
    {
        if (! $this->recaptchaService->isEnabledForPage($page)) {
            return true;
        }

        if (config('app.demo_mode', false) && config('app.skip_recaptcha_in_demo', true)) {
            return true;
        }

        if ($token === null || $token === '') {
            return false;
        }

        $request = Request::create('/', 'POST', [
            'g-recaptcha-response' => $token,
        ]);

        return $this->recaptchaService->verify($request, $page);
    }

    /**
     * @throws ValidationException
     */
    public function assertRecaptcha(?string $token, string $page): void
    {
        if ($this->verifyRecaptcha($token, $page)) {
            return;
        }

        throw ValidationException::withMessages([
            'recaptcha' => [__('reCAPTCHA verification failed. Please try again.')],
        ]);
    }

    private function spamTextValidator(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            if (! is_string($value) || ! $this->registrationGuard->looksLikeSpamName($value)) {
                return;
            }

            $fail(__('Please enter valid text.'));
        };
    }

    /**
     * @return list<mixed>
     */
    private function normalizeFieldRules(mixed $rules): array
    {
        if (is_string($rules)) {
            return array_values(array_filter(
                array_map(trim(...), explode('|', $rules)),
                fn (string $rule): bool => $rule !== '',
            ));
        }

        return is_array($rules) ? $rules : [];
    }
}
