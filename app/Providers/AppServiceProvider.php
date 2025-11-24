<?php

namespace App\Providers;

use App\Enums\Hooks\AdminFilterHook;
use App\Models\Setting;
use App\Models\User;
use App\Support\Facades\Hook;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Handle PHP 8.4 deprecation notices for development
        if (PHP_VERSION_ID >= 80400) {
            error_reporting(E_ALL & ~E_DEPRECATED);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Handle "/" route redirection.
        if (
            ! $this->app->runningInConsole() &&
            request()->is('/') &&
            Hook::applyFilters(AdminFilterHook::ADMIN_SITE_ONLY, true)
        ) {
            redirect('/admin')->send();
            exit;
        }

        // Scramable auth configuration.
        Scramble::configure()
            ->withDocumentTransformers(function (OpenApi $openApi) {
                $openApi->secure(
                    SecurityScheme::http('bearer')
                );
            });

        if ($this->app->runningUnitTests()) {
            return;
        }

        if (env('REDIRECT_HTTPS')) {
            URL::forceScheme('https');
        }

        // Skip database checks in CI environment
        if (env('SKIP_DB_CHECK_IN_CI') === 'true') {
            return;
        }

        // Check if settings table schema is present.
        try {
            if (Schema::hasTable('settings')) {
                $settings = Setting::pluck('option_value', 'option_name')->toArray();
                foreach ($settings as $key => $value) {
                    config(['settings.' . $key => $value]);
                }
            }
        } catch (\Exception $e) {
            // Skip loading settings if database connection fails
            // This prevents errors during package discovery in CI environment
        }

        // Only allowed people can view the pulse.
        Gate::define('viewPulse', function (User $user) {
            return $user->can('pulse.view');
        });
    }
}
