<?php

declare(strict_types=1);

namespace App\Support\Modules;

use Illuminate\Container\Container;
use Illuminate\Support\Str;
use Nwidart\Modules\Exceptions\ModuleNotFoundException;
use Nwidart\Modules\Laravel\LaravelFileRepository;
use Nwidart\Modules\Laravel\Module as LaravelModule;

class CustomFileRepository extends LaravelFileRepository
{
    /**
     * Get a module path for a specific module with lowercase folder names.
     */
    public function getModulePath($module): string
    {
        try {
            return $this->findOrFail($module)->getPath() . '/';
        } catch (ModuleNotFoundException $e) {
            // Use lowercase for folder names (e.g. userstorybook instead of user-story-book)
            return $this->getPath() . '/' . Str::lower($module) . '/';
        }
    }

    /**
     * Create a new Module instance using SafeModule for error-protected boot.
     *
     * {@inheritdoc}
     */
    protected function createModule(Container $app, string $name, string $path): LaravelModule
    {
        return new SafeModule($app, $name, $path);
    }
}
