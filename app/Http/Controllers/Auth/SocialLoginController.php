<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Enums\Hooks\AuthActionHook;
use App\Enums\Hooks\AuthFilterHook;
use App\Http\Controllers\Controller;
use App\Services\SocialAuthService;
use App\Support\Facades\Hook;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

class SocialLoginController extends Controller
{
    public function __construct(
        private readonly SocialAuthService $socialAuthService
    ) {
        $this->middleware('guest');
    }

    /**
     * Redirect the user to the social provider's authentication page.
     */
    public function redirect(string $provider): RedirectResponse|SymfonyRedirectResponse
    {
        // Validate provider
        if (! $this->socialAuthService->isProviderEnabled($provider)) {
            return redirect()->route('login')
                ->with('error', __('This social login provider is not available.'));
        }

        try {
            return $this->socialAuthService->redirect($provider);
        } catch (\Exception $e) {
            Log::error('Social login redirect failed', [
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('login')
                ->with('error', __('Unable to connect to :provider. Please try again.', ['provider' => ucfirst($provider)]));
        }
    }

    /**
     * Handle the callback from the social provider.
     */
    public function callback(string $provider): RedirectResponse
    {
        // Validate provider
        if (! $this->socialAuthService->isProviderEnabled($provider)) {
            return redirect()->route('login')
                ->with('error', __('This social login provider is not available.'));
        }

        try {
            $user = $this->socialAuthService->handleCallback($provider);

            // Log the user in
            Auth::login($user, true);

            Hook::doAction(AuthActionHook::AFTER_LOGIN_SUCCESS, $user, request());

            session()->flash('success', __('Successfully logged in with :provider!', ['provider' => ucfirst($provider)]));

            // Redirect to configured path
            $redirectPath = Hook::applyFilters(
                AuthFilterHook::LOGIN_REDIRECT_PATH,
                config('settings.auth_redirect_after_login', '/')
            );

            return redirect()->intended($redirectPath);
        } catch (\Exception $e) {
            Log::error('Social login callback failed', [
                'provider' => $provider,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            Hook::doAction(AuthActionHook::AFTER_LOGIN_FAILED, request());

            return redirect()->route('login')
                ->with('error', __('Unable to login with :provider. Please try again or use another method.', ['provider' => ucfirst($provider)]));
        }
    }
}
