<?php

declare(strict_types=1);

namespace App\Services;

/**
 * This class is for demo app.
 *
 * This class method will only be executed if the demo app is enabled in the .env file.
 *
 * Before every method, it will check if the demo app is enabled.
 */
class DemoAppService
{
    public function isDemoAppEnabled(): bool
    {
        return config('app.demo_mode', false);
    }

    public function getDemoAppUrl(): string
    {
        return env('DEMO_APP_URL', 'https://demo.laradashboard.com');
    }

    public function maybeSetDemoLocaleToEnByDefault(): void
    {
        if ($this->isDemoAppEnabled()) {
            app()->setLocale('en');
        }
    }
}
