<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Builder\BlockRegistryService;
use App\Services\Builder\BuilderService;
use Illuminate\Support\ServiceProvider;

/**
 * Builder Service Provider
 *
 * Registers the LaraBuilder services with the Laravel container.
 */
class BuilderServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register the block registry as a singleton
        $this->app->singleton(BlockRegistryService::class, function () {
            return new BlockRegistryService();
        });

        // Register the main builder service as a singleton
        $this->app->singleton(BuilderService::class, function ($app) {
            return new BuilderService(
                $app->make(BlockRegistryService::class)
            );
        });

        // Register aliases for easier access
        $this->app->alias(BuilderService::class, 'builder');
        $this->app->alias(BlockRegistryService::class, 'builder.blocks');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Share builder data with views
        $this->app->make(BuilderService::class)->shareWithViews();
    }
}
