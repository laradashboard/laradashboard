<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Enums\Hooks\AuthFilterHook;
use App\Http\Controllers\Controller;
use App\Support\Facades\Hook;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Display the form to request a password reset link.
     *
     * @return \Illuminate\View\View
     */
    public function showLinkRequestForm()
    {
        $viewName = Hook::applyFilters(
            AuthFilterHook::PASSWORD_RESET_REQUEST_VIEW,
            'backend.auth.passwords.email'
        );

        return view($viewName);
    }

    /**
     * Get the response for a successful password reset link.
     *
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendResetLinkResponse(Request $request, $response)
    {
        return back()->with('status', trans($response));
    }
}
