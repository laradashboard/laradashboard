<?php

declare(strict_types=1);

namespace App\Rules;

use App\Support\Security\MenuTargetSanitizer;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Rejects a menu item `target` with a script-capable scheme at save time (GHSA-883p-xm36-3p99).
 */
class SafeMenuTargetUrl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            return;
        }

        if (! MenuTargetSanitizer::isSafe($value)) {
            $fail(__('The :attribute must be a valid link (http, https, mailto, tel or sms), not a script URL.'));
        }
    }
}
