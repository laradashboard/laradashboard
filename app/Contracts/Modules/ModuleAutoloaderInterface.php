<?php

declare(strict_types=1);

namespace App\Contracts\Modules;

use Composer\Autoload\ClassLoader;

/**
 * Contract for module autoloader operations.
 *
 * Handles registering module vendor autoloaders to enable independent
 * module dependencies without polluting the core composer.lock.
 */
interface ModuleAutoloaderInterface
{
    /**
     * Register the autoloader for a specific module's vendor directory.
     *
     * @param  string  $moduleName  The name of the module
     * @return bool Whether the autoloader was registered successfully
     */
    public function register(string $moduleName): bool;

    /**
     * Register autoloaders for all modules that have vendor directories.
     *
     * @return array<string, bool> Map of module names to registration success
     */
    public function registerAll(): array;

    /**
     * Check if a module has a vendor directory with an autoloader.
     *
     * @param  string  $moduleName  The name of the module
     */
    public function hasVendorAutoloader(string $moduleName): bool;

    /**
     * Get the path to a module's vendor autoload file.
     *
     * @param  string  $moduleName  The name of the module
     */
    public function getAutoloadPath(string $moduleName): ?string;

    /**
     * Get all modules that have vendor autoloaders.
     *
     * @return array<string>
     */
    public function getModulesWithVendor(): array;

    /**
     * Get the ClassLoader instance for a module if registered.
     *
     * @param  string  $moduleName  The name of the module
     */
    public function getLoader(string $moduleName): ?ClassLoader;
}
