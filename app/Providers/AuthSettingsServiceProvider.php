<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\Hooks\CommonFilterHook;
use App\Enums\Hooks\SettingFilterHook;
use App\Http\Controllers\Auth\LoginController;
use App\Support\Facades\Hook;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * Service provider for authentication settings and hooks.
 *
 * This provider registers the authentication settings tab and related hooks.
 */
class AuthSettingsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerSettingsTab();
        $this->registerDefaultSettings();
        $this->registerCustomLoginRoute();
        $this->registerRecaptchaPages();
    }

    protected function registerRecaptchaPages(): void
    {
        Hook::addFilter(CommonFilterHook::RECAPTCHA_AVAILABLE_PAGES, function (array $pages): array {
            return array_merge($pages, [
                'public_form' => __('Public Forms (Contact, etc.)'),
                'support_ticket' => __('Support Ticket'),
                'newsletter' => __('Newsletter'),
                'reset_password' => __('Reset Password'),
            ]);
        });
    }

    /**
     * Register the authentication settings tab in the admin settings page.
     */
    protected function registerSettingsTab(): void
    {
        Hook::addFilter(SettingFilterHook::SETTINGS_TABS, function (array $tabs) {
            // Insert the auth tab after general settings
            $newTabs = [];
            foreach ($tabs as $key => $tab) {
                $newTabs[$key] = $tab;
                if ($key === 'general') {
                    $newTabs['authentication'] = [
                        'title' => __('Authentication'),
                        'icon' => 'lucide:lock',
                        'view' => 'backend.pages.settings.auth-settings',
                    ];
                }
            }

            return $newTabs;
        }, 10);
    }

    /**
     * Register default authentication settings.
     */
    protected function registerDefaultSettings(): void
    {
        // Set default values for auth settings if not already set
        $defaults = [
            'auth_enable_public_login' => '1',
            'auth_enable_public_registration' => '',
            'auth_enable_password_reset' => '1',
            'auth_enable_email_verification' => '0',
            'auth_registration_honeypot_enabled' => '1',
            'auth_registration_ip_limit_enabled' => '1',
            'auth_registration_max_per_ip_per_day' => '3',
            'auth_registration_email_domain_check_enabled' => '1',
            'auth_defer_welcome_email_until_verified' => '1',
            'email_verification_enabled' => '0',
            'email_verification_api_key' => '',
            'email_verification_monthly_limit' => '100',
            'auth_default_user_role' => 'Subscriber',
            'auth_redirect_after_login' => '/',
            'auth_redirect_after_register' => '/',
        ];

        foreach ($defaults as $key => $value) {
            if (config('settings.'.$key) === null) {
                config(['settings.'.$key => $value]);
            }
        }

        // In demo mode, force redirect paths to /admin regardless of saved settings.
        if (config('app.demo_mode', false)) {
            config([
                'settings.auth_redirect_after_login' => '/admin',
                'settings.auth_redirect_after_register' => '/admin',
            ]);
        }
    }

    /**
     * Register custom login route if configured.
     */
    protected function registerCustomLoginRoute(): void
    {
        $customLoginRoute = config('settings.custom_login_route');

        if (empty($customLoginRoute)) {
            return;
        }

        // Sanitize the route path
        $customLoginRoute = trim($customLoginRoute, '/');

        // Don't register if it's the same as the default login route
        if ($customLoginRoute === 'login') {
            return;
        }

        Route::middleware(['web', 'guest'])->group(function () use ($customLoginRoute) {
            Route::get($customLoginRoute, [LoginController::class, 'showLoginForm'])
                ->name('login.custom');
            Route::post($customLoginRoute, [LoginController::class, 'login'])
                ->middleware(['recaptcha:login', 'throttle:20,1']);
        });
    }
}
