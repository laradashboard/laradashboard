<?php

namespace App\Providers;

use App\Services\MenuService;
use Illuminate\Support\ServiceProvider;

class MenuServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(MenuService::class, function ($app) {
            return new MenuService();
        });
    }

    public function boot()
    {
        $this->app->booted(function () {
            $this->app->make(MenuService::class)->bootstrap();
        });
    }
}