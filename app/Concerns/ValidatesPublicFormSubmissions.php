<?php

declare(strict_types=1);

namespace App\Concerns;

use App\Services\PublicFormGuardService;
use Livewire\Component;

/**
 * @mixin Component
 */
trait ValidatesPublicFormSubmissions
{
    public string $company_website = '';

    public string $recaptchaToken = '';

    protected function publicFormGuard(): PublicFormGuardService
    {
        return app(PublicFormGuardService::class);
    }

    /**
     * Validate a public form with shared honeypot, email, spam-text, and reCAPTCHA guards.
     *
     * @param  array<string, list<mixed>>  $rules
     * @param  array<string, string>  $messages
     * @param  list<string>  $emailFields
     * @param  list<string>  $spamTextFields
     */
    /**
     * @return bool True when the submission should proceed; false when silently rejected.
     */
    protected function validatePublicForm(
        string $recaptchaPage,
        array $rules,
        array $messages = [],
        array $emailFields = [],
        array $spamTextFields = [],
        bool $silentRejectInvalidEmails = true,
    ): bool {
        $guard = $this->publicFormGuard();

        $this->validate(
            $guard->mergeRules(
                $rules,
                $emailFields,
                $spamTextFields,
                includeAdvancedEmailValidation: ! $silentRejectInvalidEmails,
            ),
            $messages
        );

        if ($silentRejectInvalidEmails && $guard->emailsFailAdvancedValidation(
            $this->collectPublicFormEmailValues($emailFields)
        )) {
            $this->onSilentPublicFormRejection();

            return false;
        }

        $guard->assertRecaptcha($this->recaptchaToken, $recaptchaPage);

        $this->recaptchaToken = '';

        return true;
    }

    /**
     * Called when advanced email validation fails on a public form. Shows success
     * without persisting the submission so spammers cannot probe the rules.
     */
    protected function onSilentPublicFormRejection(): void
    {
        if (property_exists($this, 'success')) {
            $this->success = true;
        }

        if (property_exists($this, 'errorMessage')) {
            $this->errorMessage = '';
        }

        $this->recaptchaToken = '';

        if (property_exists($this, 'formData') && is_array($this->formData)) {
            foreach ($this->formData as $key => $value) {
                $this->formData[$key] = is_array($value) ? [] : '';
            }
        }
    }

    /**
     * @param  list<string>  $emailFields
     * @return list<string>
     */
    protected function collectPublicFormEmailValues(array $emailFields): array
    {
        $values = [];

        foreach ($emailFields as $field) {
            $value = $this->resolvePublicFormFieldValue($field);

            if (is_string($value) && $value !== '') {
                $values[] = $value;
            }
        }

        return $values;
    }

    private function resolvePublicFormFieldValue(string $field): mixed
    {
        if (str_starts_with($field, 'formData.')) {
            $key = substr($field, strlen('formData.'));

            if (property_exists($this, 'formData') && is_array($this->formData)) {
                return $this->formData[$key] ?? '';
            }

            return '';
        }

        if (property_exists($this, $field)) {
            return $this->{$field};
        }

        if (property_exists($this, 'formData') && is_array($this->formData)) {
            return $this->formData[$field] ?? '';
        }

        return '';
    }
}
