<?php

declare(strict_types=1);

namespace App\Rules;

use App\Support\Security\MenuTargetSanitizer;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Rejects a menu item `target` whose scheme could execute script from an href
 * (`javascript:`, `vbscript:`, `data:`, etc.) — see GHSA-883p-xm36-3p99.
 *
 * Applies to every menu item type, not only 'custom': the target is still attacker-
 * controlled free-form input before the controller knows the type, and rejecting it
 * here gives the admin a validation error instead of the value silently turning into
 * '#' later when {@see \App\Models\MenuItem::getUrl()} renders it.
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
