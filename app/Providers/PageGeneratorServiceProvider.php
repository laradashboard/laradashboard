<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class PageGeneratorServiceProvider extends ServiceProvider
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
        // Register view namespace for page-generator
        $this->loadViewsFrom(resource_path('views/page-generator'), 'page-generator');

        // Register view composer for components
        view()->addNamespace('page-generator', resource_path('views/page-generator'));
    }
}
