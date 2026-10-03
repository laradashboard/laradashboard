<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Modules\ModuleAutoloaderInterface;
use App\Contracts\Modules\ModuleComposerInterface;
use App\Services\Modules\ModuleAutoloaderService;
use App\Services\Modules\ModuleComposerService;
use App\Support\Modules\CustomFileRepository;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use Nwidart\Modules\Contracts\RepositoryInterface;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Override the module repository with our custom implementation
        $this->app->singleton(RepositoryInterface::class, function ($app) {
            $path = $app['config']->get('modules.paths.modules');

            return new CustomFileRepository($app, $path);
        });

        // Register module composer service
        $this->app->singleton(ModuleComposerInterface::class, function ($app) {
            return new ModuleComposerService(
                $app['config']->get('modules.paths.modules')
            );
        });

        // Register module autoloader service
        $this->app->singleton(ModuleAutoloaderInterface::class, function ($app) {
            return new ModuleAutoloaderService(
                $app['config']->get('modules.paths.modules')
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->flashAutoDisabledModuleNotices(storage_path('framework/modules_auto_disabled.json'));
    }

    /**
     * Flash notices written before the application booted, then remove the file.
     *
     * Pest workers share storage/framework, so another process can remove the
     * file after this one has already seen it.
     */
    public function flashAutoDisabledModuleNotices(string $disabledPath): void
    {
        $contents = @file_get_contents($disabledPath);

        if (! is_string($contents) || $contents === '') {
            return;
        }

        $disabledModules = json_decode($contents, true);

        if (! is_array($disabledModules) || $disabledModules === []) {
            return;
        }

        foreach ($disabledModules as $moduleName => $reason) {
            session()->flash('warning', __('Module ":module" was auto-disabled: :reason', [
                'module' => $moduleName,
                'reason' => $reason,
            ]));
        }

        File::delete($disabledPath);
    }
}
