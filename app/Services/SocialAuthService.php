<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Hooks\AuthActionHook;
use App\Enums\Hooks\AuthFilterHook;
use App\Models\SocialAccount;
use App\Models\User;
use App\Support\Facades\Hook;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthService
{
    /**
     * Supported social providers.
     */
    public const PROVIDERS = [
        'google' => [
            'name' => 'Google',
            'icon' => 'logos:google-icon',
            'color' => '#DB4437',
            'setting_enable' => 'auth_social_enable_google',
            'setting_client_id' => 'auth_social_google_client_id',
            'setting_client_secret' => 'auth_social_google_client_secret',
            'env_client_id' => 'GOOGLE_CLIENT_ID',
            'env_client_secret' => 'GOOGLE_CLIENT_SECRET',
            'console_url' => 'https://console.cloud.google.com/apis/credentials',
            'docs_url' => 'https://developers.google.com/identity/protocols/oauth2',
        ],
        'github' => [
            'name' => 'GitHub',
            'icon' => 'mdi:github',
            'color' => '#333333',
            'setting_enable' => 'auth_social_enable_github',
            'setting_client_id' => 'auth_social_github_client_id',
            'setting_client_secret' => 'auth_social_github_client_secret',
            'env_client_id' => 'GITHUB_CLIENT_ID',
            'env_client_secret' => 'GITHUB_CLIENT_SECRET',
            'console_url' => 'https://github.com/settings/developers',
            'docs_url' => 'https://docs.github.com/en/apps/oauth-apps/building-oauth-apps/creating-an-oauth-app',
        ],
        'facebook' => [
            'name' => 'Facebook',
            'icon' => 'logos:facebook',
            'color' => '#1877F2',
            'setting_enable' => 'auth_social_enable_facebook',
            'setting_client_id' => 'auth_social_facebook_client_id',
            'setting_client_secret' => 'auth_social_facebook_client_secret',
            'env_client_id' => 'FACEBOOK_CLIENT_ID',
            'env_client_secret' => 'FACEBOOK_CLIENT_SECRET',
            'console_url' => 'https://developers.facebook.com/apps/',
            'docs_url' => 'https://developers.facebook.com/docs/facebook-login/web',
        ],
        'twitter' => [
            'name' => 'Twitter/X',
            'icon' => 'ri:twitter-x-fill',
            'color' => '#000000',
            'setting_enable' => 'auth_social_enable_twitter',
            'setting_client_id' => 'auth_social_twitter_client_id',
            'setting_client_secret' => 'auth_social_twitter_client_secret',
            'env_client_id' => 'TWITTER_CLIENT_ID',
            'env_client_secret' => 'TWITTER_CLIENT_SECRET',
            'console_url' => 'https://developer.twitter.com/en/portal/dashboard',
            'docs_url' => 'https://developer.twitter.com/en/docs/authentication/oauth-2-0',
        ],
        'linkedin' => [
            'name' => 'LinkedIn',
            'icon' => 'mdi:linkedin',
            'color' => '#0A66C2',
            'setting_enable' => 'auth_social_enable_linkedin',
            'setting_client_id' => 'auth_social_linkedin_client_id',
            'setting_client_secret' => 'auth_social_linkedin_client_secret',
            'env_client_id' => 'LINKEDIN_CLIENT_ID',
            'env_client_secret' => 'LINKEDIN_CLIENT_SECRET',
            'console_url' => 'https://www.linkedin.com/developers/apps',
            'docs_url' => 'https://learn.microsoft.com/en-us/linkedin/shared/authentication/authorization-code-flow',
        ],
    ];

    /**
     * Check if social login is enabled globally.
     */
    public function isSocialLoginEnabled(): bool
    {
        return filter_var(
            config('settings.auth_show_social_login', '0'),
            FILTER_VALIDATE_BOOLEAN
        );
    }

    /**
     * Get all enabled social providers.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getEnabledProviders(): array
    {
        if (! $this->isSocialLoginEnabled()) {
            return [];
        }

        $enabledProviders = [];

        foreach (self::PROVIDERS as $provider => $config) {
            if ($this->isProviderEnabled($provider)) {
                $enabledProviders[$provider] = $config;
            }
        }

        return Hook::applyFilters(AuthFilterHook::AUTH_SOCIAL_LOGIN_PROVIDERS, $enabledProviders);
    }

    /**
     * Check if a specific provider is enabled and configured.
     */
    public function isProviderEnabled(string $provider): bool
    {
        if (! isset(self::PROVIDERS[$provider])) {
            return false;
        }

        $config = self::PROVIDERS[$provider];

        // Check if enabled in settings
        $isEnabled = filter_var(
            config('settings.'.$config['setting_enable'], '0'),
            FILTER_VALIDATE_BOOLEAN
        );

        if (! $isEnabled) {
            return false;
        }

        // Check if credentials are configured
        $clientId = $this->getProviderCredential($provider, 'client_id');
        $clientSecret = $this->getProviderCredential($provider, 'client_secret');

        return ! empty($clientId) && ! empty($clientSecret);
    }

    /**
     * Get provider credential with settings/env fallback.
     */
    public function getProviderCredential(string $provider, string $type): ?string
    {
        if (! isset(self::PROVIDERS[$provider])) {
            return null;
        }

        $config = self::PROVIDERS[$provider];

        if ($type === 'client_id') {
            $settingValue = config('settings.'.$config['setting_client_id']);
            $envValue = env($config['env_client_id']);
        } elseif ($type === 'client_secret') {
            $settingValue = config('settings.'.$config['setting_client_secret']);
            $envValue = env($config['env_client_secret']);
        } else {
            return null;
        }

        return ! empty($settingValue) ? $settingValue : $envValue;
    }

    /**
     * Configure Socialite driver with credentials from settings/env.
     */
    public function configureProvider(string $provider): void
    {
        $clientId = $this->getProviderCredential($provider, 'client_id');
        $clientSecret = $this->getProviderCredential($provider, 'client_secret');

        // Use route() helper which respects the actual request URL
        // This handles cases where APP_URL might not match the actual domain
        $redirect = route('social.callback', ['provider' => $provider]);

        config([
            'services.'.$provider.'.client_id' => $clientId,
            'services.'.$provider.'.client_secret' => $clientSecret,
            'services.'.$provider.'.redirect' => $redirect,
        ]);
    }

    /**
     * Redirect to social provider.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirect(string $provider)
    {
        $this->configureProvider($provider);

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle callback from social provider.
     */
    public function handleCallback(string $provider): User
    {
        $this->configureProvider($provider);

        $socialUser = Socialite::driver($provider)->user();

        return $this->findOrCreateUser($provider, $socialUser);
    }

    /**
     * Find or create user from social provider data.
     */
    public function findOrCreateUser(string $provider, SocialiteUser $socialUser): User
    {
        /** @var User $user */
        $user = DB::transaction(function () use ($provider, $socialUser): User {
            // First, check if social account already exists
            $socialAccount = SocialAccount::query()
                ->where('provider', $provider)
                ->where('provider_user_id', $socialUser->getId())
                ->first();

            if ($socialAccount) {
                // Update tokens
                $this->updateSocialAccount($socialAccount, $socialUser);

                /** @var User $existingUser */
                $existingUser = $socialAccount->user;

                return $existingUser;
            }

            // Check if user exists with same email
            $email = $socialUser->getEmail();
            $user = $email ? User::where('email', $email)->first() : null;

            if (! $user) {
                // Create new user
                $user = $this->createUser($socialUser);
            }

            // Create social account link
            $this->createSocialAccount($user, $provider, $socialUser);

            return $user;
        });

        return $user;
    }

    /**
     * Create a new user from social provider data.
     */
    protected function createUser(SocialiteUser $socialUser): User
    {
        $name = $socialUser->getName() ?? $socialUser->getNickname() ?? 'User';
        $nameParts = explode(' ', $name, 2);
        $firstName = $nameParts[0] ?? 'User';
        $lastName = $nameParts[1] ?? '';

        $email = $socialUser->getEmail();
        $username = $this->generateUniqueUsername($email ?? $name);

        Hook::doAction(AuthActionHook::BEFORE_REGISTRATION, [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'social_login' => true,
        ]);

        $userData = Hook::applyFilters(AuthFilterHook::REGISTER_USER_DATA, [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'username' => $username,
            'email' => $email,
            'password' => Hash::make(Str::random(32)),
            'email_verified_at' => $email ? now() : null,
        ]);

        $user = User::create($userData);

        // Assign default role
        $defaultRole = Hook::applyFilters(
            AuthFilterHook::REGISTER_DEFAULT_ROLE,
            config('settings.auth_default_user_role', 'Subscriber')
        );

        if ($defaultRole) {
            try {
                $user->assignRole($defaultRole);
            } catch (\Exception $e) {
                Log::warning("Could not assign role '{$defaultRole}' to social user: ".$e->getMessage());
            }
        }

        Hook::doAction(AuthActionHook::AFTER_REGISTRATION_SUCCESS, $user, [
            'social_login' => true,
        ]);

        return $user;
    }

    /**
     * Create social account link.
     */
    protected function createSocialAccount(User $user, string $provider, SocialiteUser $socialUser): SocialAccount
    {
        return SocialAccount::create([
            'user_id' => $user->id,
            'provider' => $provider,
            'provider_user_id' => $socialUser->getId(),
            'provider_email' => $socialUser->getEmail(),
            'provider_avatar' => $socialUser->getAvatar(),
            'access_token' => $socialUser->token ?? null,
            'refresh_token' => $socialUser->refreshToken ?? null,
            'token_expires_at' => isset($socialUser->expiresIn)
                ? now()->addSeconds($socialUser->expiresIn)
                : null,
        ]);
    }

    /**
     * Update social account tokens.
     */
    protected function updateSocialAccount(SocialAccount $socialAccount, SocialiteUser $socialUser): void
    {
        $socialAccount->update([
            'provider_email' => $socialUser->getEmail(),
            'provider_avatar' => $socialUser->getAvatar(),
            'access_token' => $socialUser->token ?? null,
            'refresh_token' => $socialUser->refreshToken ?? null,
            'token_expires_at' => isset($socialUser->expiresIn)
                ? now()->addSeconds($socialUser->expiresIn)
                : null,
        ]);
    }

    /**
     * Generate a unique username from name or email.
     */
    protected function generateUniqueUsername(string $source): string
    {
        $baseUsername = strtolower(str_contains($source, '@')
            ? explode('@', $source)[0]
            : preg_replace('/[^a-z0-9]/', '', strtolower($source)));

        if (empty($baseUsername)) {
            $baseUsername = 'user';
        }

        $username = $baseUsername;
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $baseUsername.$counter;
            $counter++;
        }

        return $username;
    }
}
