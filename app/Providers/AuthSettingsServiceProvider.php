<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\Hooks\SettingFilterHook;
use App\Support\Facades\Hook;
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
            'auth_enable_public_registration' => '0',
            'auth_enable_password_reset' => '1',
            'auth_enable_email_verification' => '0',
            'auth_default_user_role' => 'user',
            'auth_redirect_after_login' => '/',
            'auth_redirect_after_register' => '/',
        ];

        foreach ($defaults as $key => $value) {
            if (config('settings.'.$key) === null) {
                config(['settings.'.$key => $value]);
            }
        }
    }
}
