<?php

declare(strict_types=1);

namespace App\Rules;

use App\Models\User;
use App\Support\Security\OllamaUrlValidator;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Guards changes to the `ai_ollama_base_url` setting.
 *
 * The server sends requests to this URL, so it is infrastructure-level config: only a
 * Superadmin may change it, and the new value must pass OllamaUrlValidator. Submitting
 * the current value unchanged (other admins saving the AI settings form) always passes.
 */
class SafeOllamaBaseUrl implements ValidationRule
{
    public function __construct(
        private readonly ?User $user,
    ) {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $value = trim((string) $value);

        if ($value === trim((string) config('settings.ai_ollama_base_url'))) {
            return;
        }

        if (! $this->user?->isSuperAdmin()) {
            $fail(__('Only a Superadmin can change the Ollama base URL.'));

            return;
        }

        if ($value !== '' && ($error = app(OllamaUrlValidator::class)->validate($value))) {
            $fail($error);
        }
    }
}
