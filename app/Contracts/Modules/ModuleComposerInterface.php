<?php

declare(strict_types=1);

namespace App\Contracts\Modules;

/**
 * Contract for module composer operations.
 *
 * Handles running composer commands within individual module directories,
 * enabling modules to maintain their own independent dependencies.
 */
interface ModuleComposerInterface
{
    /**
     * Run a composer command in a specific module's directory.
     *
     * @param  string  $moduleName  The name of the module
     * @param  string  $command  The composer command to run (e.g., 'install', 'update')
     * @param  array<string>  $arguments  Additional arguments for the command
     * @return array{success: bool, output: string, exit_code: int}
     */
    public function run(string $moduleName, string $command, array $arguments = []): array;

    /**
     * Install dependencies for a specific module.
     *
     * @param  string  $moduleName  The name of the module
     * @param  bool  $devDependencies  Whether to include dev dependencies
     * @return array{success: bool, output: string, exit_code: int}
     */
    public function install(string $moduleName, bool $devDependencies = true): array;

    /**
     * Update dependencies for a specific module.
     *
     * @param  string  $moduleName  The name of the module
     * @return array{success: bool, output: string, exit_code: int}
     */
    public function update(string $moduleName): array;

    /**
     * Install dependencies for all modules that have a composer.json.
     *
     * @param  bool  $devDependencies  Whether to include dev dependencies
     * @return array<string, array{success: bool, output: string, exit_code: int}>
     */
    public function installAll(bool $devDependencies = true): array;

    /**
     * Update dependencies for all modules that have a composer.json.
     *
     * @return array<string, array{success: bool, output: string, exit_code: int}>
     */
    public function updateAll(): array;

    /**
     * Check if a module has a composer.json file.
     *
     * @param  string  $moduleName  The name of the module
     */
    public function hasComposerFile(string $moduleName): bool;

    /**
     * Get the path to a module's directory.
     *
     * @param  string  $moduleName  The name of the module
     */
    public function getModulePath(string $moduleName): ?string;

    /**
     * Get all modules that have composer.json files.
     *
     * @return array<string>
     */
    public function getModulesWithComposer(): array;
}
