<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Enums\Hooks\AuthFilterHook;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Support\Facades\Hook;
use Illuminate\Foundation\Auth\VerifiesEmails;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Email Verification Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling email verification for any
    | user that recently registered with the application. Emails may also
    | be re-sent if the user didn't receive the original email message.
    |
    */

    use VerifiesEmails;

    /**
     * Where to redirect users after verification.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::ADMIN_DASHBOARD;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }

    /**
     * Show the email verification notice.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show(Request $request)
    {
        // If email verification is disabled, redirect to dashboard
        if (! $this->isEmailVerificationEnabled()) {
            return redirect($this->redirectPath());
        }

        return $request->user()->hasVerifiedEmail()
            ? redirect($this->redirectPath())
            : view('backend.auth.verify');
    }

    /**
     * Get the post verification redirect path.
     */
    protected function redirectTo(): string
    {
        $defaultRedirect = config('settings.auth_redirect_after_login', RouteServiceProvider::ADMIN_DASHBOARD);

        return Hook::applyFilters(AuthFilterHook::LOGIN_REDIRECT_PATH, $defaultRedirect);
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
