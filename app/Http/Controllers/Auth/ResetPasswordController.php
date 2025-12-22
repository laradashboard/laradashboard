<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Enums\Hooks\AuthFilterHook;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Support\Facades\Hook;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::ADMIN_DASHBOARD;

    /**
     * Display the password reset view for the given token.
     *
     * @param  string|null  $token
     * @return \Illuminate\View\View
     */
    public function showResetForm(Request $request, $token = null)
    {
        $viewName = Hook::applyFilters(
            AuthFilterHook::PASSWORD_RESET_VIEW,
            'backend.auth.passwords.reset'
        );

        return view($viewName)->with([
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    /**
     * Get the post password reset redirect path.
     */
    protected function redirectTo(): string
    {
        $defaultRedirect = config('settings.auth_redirect_after_login', RouteServiceProvider::ADMIN_DASHBOARD);

        return Hook::applyFilters(AuthFilterHook::PASSWORD_RESET_REDIRECT_PATH, $defaultRedirect);
    }
}
