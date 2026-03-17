<?php

declare(strict_types=1);

namespace App\Providers;

use App\Support\HookManager;
use App\Support\ThemeManager;
use Illuminate\Support\ServiceProvider;

class HookServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('hook', function ($app) {
            return new HookManager();
        });

        $this->app->singleton('theme', function ($app) {
            return new ThemeManager();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Additional boot logic can be added here if needed in the future.
    }
}
