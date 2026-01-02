<?php

declare(strict_types=1);

namespace App\Services\Modules;

use App\Contracts\Modules\ModuleAutoloaderInterface;
use Composer\Autoload\ClassLoader;
use Illuminate\Support\Str;

/**
 * Service for handling module vendor autoloader registration.
 *
 * This service registers module-specific vendor autoloaders, enabling modules
 * to maintain their own independent dependencies without polluting the core
 * composer.lock file.
 */
class ModuleAutoloaderService implements ModuleAutoloaderInterface
{
    /**
     * The base path to the modules directory.
     */
    protected string $modulesPath;

    /**
     * Registered ClassLoader instances for each module.
     *
     * @var array<string, ClassLoader>
     */
    protected array $loaders = [];

    public function __construct(?string $modulesPath = null)
    {
        $this->modulesPath = $modulesPath ?? base_path('modules');
    }

    /**
     * {@inheritdoc}
     */
    public function register(string $moduleName): bool
    {
        $autoloadPath = $this->getAutoloadPath($moduleName);

        if ($autoloadPath === null || ! file_exists($autoloadPath)) {
            return false;
        }

        // Avoid double registration
        if (isset($this->loaders[$moduleName])) {
            return true;
        }

        try {
            $loader = require $autoloadPath;

            if ($loader instanceof ClassLoader) {
                $this->loaders[$moduleName] = $loader;

                return true;
            }

            // Some autoload.php files return the loader, some don't
            // In case it doesn't return a ClassLoader, we still consider it registered
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function registerAll(): array
    {
        $results = [];

        foreach ($this->getModulesWithVendor() as $moduleName) {
            $results[$moduleName] = $this->register($moduleName);
        }

        return $results;
    }

    /**
     * {@inheritdoc}
     */
    public function hasVendorAutoloader(string $moduleName): bool
    {
        $autoloadPath = $this->getAutoloadPath($moduleName);

        return $autoloadPath !== null && file_exists($autoloadPath);
    }

    /**
     * {@inheritdoc}
     */
    public function getAutoloadPath(string $moduleName): ?string
    {
        $modulePath = $this->findModulePath($moduleName);

        if ($modulePath === null) {
            return null;
        }

        $autoloadPath = $modulePath . '/vendor/autoload.php';

        return file_exists($autoloadPath) ? $autoloadPath : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getModulesWithVendor(): array
    {
        $modules = [];

        if (! is_dir($this->modulesPath)) {
            return $modules;
        }

        foreach (scandir($this->modulesPath) as $dir) {
            if ($dir === '.' || $dir === '..') {
                continue;
            }

            $fullPath = $this->modulesPath . '/' . $dir;
            $autoloadPath = $fullPath . '/vendor/autoload.php';

            if (is_dir($fullPath) && file_exists($autoloadPath)) {
                // Get the actual module name from module.json if available
                $moduleJsonPath = $fullPath . '/module.json';
                if (file_exists($moduleJsonPath)) {
                    $config = json_decode(file_get_contents($moduleJsonPath), true);
                    if (is_array($config) && ! empty($config['name'])) {
                        $modules[] = $config['name'];

                        continue;
                    }
                }

                $modules[] = $dir;
            }
        }

        return $modules;
    }

    /**
     * {@inheritdoc}
     */
    public function getLoader(string $moduleName): ?ClassLoader
    {
        return $this->loaders[$moduleName] ?? null;
    }

    /**
     * Find the actual path to a module directory.
     *
     * @param  string  $moduleName  The name of the module
     */
    protected function findModulePath(string $moduleName): ?string
    {
        // Try exact match first
        $path = $this->modulesPath . '/' . $moduleName;
        if (is_dir($path)) {
            return $path;
        }

        // Try kebab-case
        $kebabPath = $this->modulesPath . '/' . Str::kebab($moduleName);
        if (is_dir($kebabPath)) {
            return $kebabPath;
        }

        // Try lowercase
        $lowerPath = $this->modulesPath . '/' . strtolower($moduleName);
        if (is_dir($lowerPath)) {
            return $lowerPath;
        }

        // Case-insensitive search
        if (is_dir($this->modulesPath)) {
            foreach (scandir($this->modulesPath) as $dir) {
                if ($dir === '.' || $dir === '..') {
                    continue;
                }

                if (strtolower($dir) === strtolower($moduleName)) {
                    return $this->modulesPath . '/' . $dir;
                }
            }
        }

        return null;
    }

    /**
     * Static method to register all module autoloaders.
     * This can be called from bootstrap before Laravel is fully loaded.
     *
     * @param  string  $modulesPath  Path to the modules directory
     * @return array<string, bool> Map of module names to registration success
     */
    public static function registerAllStatic(string $modulesPath): array
    {
        $results = [];

        if (! is_dir($modulesPath)) {
            return $results;
        }

        foreach (scandir($modulesPath) as $dir) {
            if ($dir === '.' || $dir === '..') {
                continue;
            }

            $fullPath = $modulesPath . '/' . $dir;
            $autoloadPath = $fullPath . '/vendor/autoload.php';

            if (is_dir($fullPath) && file_exists($autoloadPath)) {
                try {
                    require $autoloadPath;
                    $results[$dir] = true;
                } catch (\Throwable $e) {
                    $results[$dir] = false;
                }
            }
        }

        return $results;
    }
}
