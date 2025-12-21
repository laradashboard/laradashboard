<?php

namespace App\Providers;

use App\Enums\Hooks\AdminFilterHook;
use App\Models\Setting;
use App\Models\User;
use App\Services\InstallationService;
use App\Observers\EmailObserver;
use App\Services\EmailConnectionService;
use App\Services\EmailProviderRegistry;
use App\Services\Emails\Mailer;
use App\Services\EmailProviders\PhpMailProvider;
use App\Services\EmailProviders\SmtpProvider;
use App\Support\Facades\Hook;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if (PHP_VERSION_ID >= 80400) {
            error_reporting(E_ALL & ~E_DEPRECATED);
        }

        // Register Mailer service as singleton for unified email sending
        $this->app->singleton(Mailer::class, function ($app) {
            return new Mailer($app->make(EmailConnectionService::class));
        });

        // Register Debugbar only in local environment
        if (App::environment('local') && class_exists(\Barryvdh\Debugbar\ServiceProvider::class)) {
            $this->app->register(\Barryvdh\Debugbar\ServiceProvider::class);
        }

        // Register Dump Server only in local environment (for CLI debugging)
        if (App::environment('local') && class_exists(\BeyondCode\DumpServer\DumpServerServiceProvider::class)) {
            $this->app->register(\BeyondCode\DumpServer\DumpServerServiceProvider::class);
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

        // Check if database is configured before attempting to load settings
        // This prevents errors during installation when database isn't configured yet
        if (InstallationService::isDatabaseConfigured()) {
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
        }

        // Only allowed people can view the pulse.
        Gate::define('viewPulse', function (User $user) {
            return $user->can('pulse.view');
        });

        // Register email observer for automatic unsubscribe links
        Event::listen(MessageSending::class, [EmailObserver::class, 'sending']);

        // Register built-in email providers
        EmailProviderRegistry::registerProvider(PhpMailProvider::class);
        EmailProviderRegistry::registerProvider(SmtpProvider::class);

        // Configure Laravel's default mail driver to use the best email connection
        // This ensures all emails (including notifications like password reset) use the unified email system
        $this->configureDefaultMailer();
    }

    /**
     * Configure Laravel's default mail driver from the best email connection.
     *
     * This ensures all emails (notifications, password resets, etc.) go through
     * the admin-configured email connection instead of the .env mail settings.
     */
    protected function configureDefaultMailer(): void
    {
        // Skip if database isn't configured (during installation)
        if (! InstallationService::isDatabaseConfigured()) {
            return;
        }

        // Skip if email_connections table doesn't exist yet (during installation before migrations)
        try {
            if (! Schema::hasTable('email_connections')) {
                return;
            }
        } catch (\Exception $e) {
            return;
        }

        try {
            $connectionService = app(EmailConnectionService::class);
            $connection = $connectionService->getBestConnection();

            if (! $connection) {
                return;
            }

            $provider = EmailProviderRegistry::getProvider($connection->provider_type);

            if (! $provider) {
                return;
            }

            $transportConfig = $provider->getTransportConfig($connection);

            // Set the default mailer configuration.
            Config::set('mail.default', 'dynamic_email_connection');
            Config::set('mail.mailers.dynamic_email_connection', $transportConfig);

            // Set the global from address if configured.
            if ($connection->from_email) {
                Config::set('mail.from.address', $connection->from_email);
                Config::set('mail.from.name', $connection->from_name ?: config('app.name'));
            }
        } catch (\Exception $e) {
            // Silently fail during installation - allow fallback to default .env mail config
            // Don't log during installation as it would flood logs with expected errors
        }
    }
}
