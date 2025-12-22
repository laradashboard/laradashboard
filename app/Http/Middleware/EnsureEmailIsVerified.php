<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to ensure email is verified when the setting is enabled.
 *
 * This middleware checks the `auth_enable_email_verification` setting
 * and enforces email verification only when it's enabled.
 */
class EnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  string|null  $redirectToRoute
     */
    public function handle(Request $request, Closure $next, ?string $redirectToRoute = null): Response
    {
        // Check if email verification is enabled in settings
        if (! $this->isEmailVerificationEnabled()) {
            return $next($request);
        }

        // Check if user needs to verify email
        if (
            ! $request->user() ||
            ($request->user() instanceof MustVerifyEmail && ! $request->user()->hasVerifiedEmail())
        ) {
            return $request->expectsJson()
                ? abort(403, __('Your email address is not verified.'))
                : Redirect::guest(URL::route($redirectToRoute ?: 'verification.notice'));
        }

        return $next($request);
    }

    /**
     * Check if email verification is enabled in settings.
     */
    protected function isEmailVerificationEnabled(): bool
    {
        $setting = config('settings.auth_enable_email_verification', '0');

        return filter_var($setting, FILTER_VALIDATE_BOOLEAN);
    }
}
