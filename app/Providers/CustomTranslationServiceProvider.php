<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class CustomTranslationServiceProvider extends ServiceProvider
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
     *
     * Load user-provided translations that override core translations.
     * User translations are stored in resources/user-lang/ and are not
     * affected by system updates.
     */
    public function boot(): void
    {
        $userLangPath = resource_path('user-lang');

        if (! is_dir($userLangPath)) {
            return;
        }

        $loader = $this->app['translator']->getLoader();

        // Use addPath instead of addJsonPath - paths are loaded AFTER jsonPaths
        // and later paths override earlier ones, so user translations will take priority
        $loader->addPath($userLangPath);
    }
}
