<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\EmailTemplateVariableService;
use App\Models\Setting;

class EmailTemplateServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(EmailTemplateVariableService::class, function ($app) {
            return new EmailTemplateVariableService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Clear email template variable cache when settings are updated
        Setting::saved(function ($setting) {
            if (in_array($setting->option_name, [
                'site_logo',
                'site_logo_lite',
                'site_logo_dark',
                'company_name',
                'primary_color',
                'secondary_color',
                'body_bg_color',
                'app_name',
            ])) {
                app(EmailTemplateVariableService::class)->clearCache();
            }
        });
    }
}
