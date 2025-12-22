<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to check if public authentication features are enabled.
 *
 * This middleware checks the authentication settings to determine if public
 * login, registration, or password reset features are enabled.
 */
class CheckPublicAuthEnabled
{
    /**
     * Handle an incoming request.
     *
     * @param  string  $feature  The feature to check (login, register, password_reset)
     */
    public function handle(Request $request, Closure $next, string $feature = 'login'): Response
    {
        $isEnabled = match ($feature) {
            'login' => $this->isPublicLoginEnabled(),
            'register' => $this->isPublicRegistrationEnabled(),
            'password_reset' => $this->isPasswordResetEnabled(),
            default => false,
        };

        if (! $isEnabled) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __('This feature is currently disabled.'),
                ], 403);
            }

            abort(404);
        }

        return $next($request);
    }

    /**
     * Check if public login is enabled.
     */
    protected function isPublicLoginEnabled(): bool
    {
        $setting = get_setting('auth_enable_public_login', '1');

        return filter_var($setting, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Check if public registration is enabled.
     */
    protected function isPublicRegistrationEnabled(): bool
    {
        $setting = get_setting('auth_enable_public_registration', '0');

        return filter_var($setting, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Check if password reset is enabled.
     */
    protected function isPasswordResetEnabled(): bool
    {
        $setting = get_setting('auth_enable_password_reset', '1');

        return filter_var($setting, FILTER_VALIDATE_BOOLEAN);
    }
}
