<?php

namespace App\Providers;

use App\Services\MenuService\AdminMenuService;
use Illuminate\Support\ServiceProvider;

class MenuServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('admin.menu', function ($app) {
            return new AdminMenuService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
