<?php

declare(strict_types=1);

namespace App\Services\Modules;

use App\Contracts\Modules\ModuleComposerInterface;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

/**
 * Service for handling composer operations within module directories.
 *
 * This service enables modules to maintain their own independent dependencies
 * by running composer commands within each module's directory.
 */
class ModuleComposerService implements ModuleComposerInterface
{
    /**
     * The base path to the modules directory.
     */
    protected string $modulesPath;

    /**
     * Timeout for composer commands in seconds.
     */
    protected int $timeout;

    public function __construct(?string $modulesPath = null, int $timeout = 300)
    {
        $this->modulesPath = $modulesPath ?? base_path('modules');
        $this->timeout = $timeout;
    }

    /**
     * {@inheritdoc}
     */
    public function run(string $moduleName, string $command, array $arguments = []): array
    {
        $modulePath = $this->getModulePath($moduleName);

        if ($modulePath === null) {
            return [
                'success' => false,
                'output' => "Module '{$moduleName}' not found or has no composer.json",
                'exit_code' => 1,
            ];
        }

        $composerCommand = array_merge(
            ['composer', $command],
            $arguments,
            ['--working-dir=' . $modulePath, '--no-interaction']
        );

        $process = new Process($composerCommand);
        $process->setTimeout($this->timeout);
        $process->run();

        return [
            'success' => $process->isSuccessful(),
            'output' => $process->getOutput() . $process->getErrorOutput(),
            'exit_code' => $process->getExitCode() ?? 1,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function install(string $moduleName, bool $devDependencies = true): array
    {
        $arguments = $devDependencies ? [] : ['--no-dev'];

        return $this->run($moduleName, 'install', $arguments);
    }

    /**
     * {@inheritdoc}
     */
    public function update(string $moduleName): array
    {
        return $this->run($moduleName, 'update');
    }

    /**
     * {@inheritdoc}
     */
    public function installAll(bool $devDependencies = true): array
    {
        $results = [];

        foreach ($this->getModulesWithComposer() as $moduleName) {
            $results[$moduleName] = $this->install($moduleName, $devDependencies);
        }

        return $results;
    }

    /**
     * {@inheritdoc}
     */
    public function updateAll(): array
    {
        $results = [];

        foreach ($this->getModulesWithComposer() as $moduleName) {
            $results[$moduleName] = $this->update($moduleName);
        }

        return $results;
    }

    /**
     * {@inheritdoc}
     */
    public function hasComposerFile(string $moduleName): bool
    {
        $path = $this->getModulePath($moduleName);

        return $path !== null && file_exists($path . '/composer.json');
    }

    /**
     * {@inheritdoc}
     */
    public function getModulePath(string $moduleName): ?string
    {
        // Try exact match first
        $path = $this->modulesPath . '/' . $moduleName;
        if (is_dir($path) && file_exists($path . '/composer.json')) {
            return $path;
        }

        // Try kebab-case
        $kebabPath = $this->modulesPath . '/' . Str::kebab($moduleName);
        if (is_dir($kebabPath) && file_exists($kebabPath . '/composer.json')) {
            return $kebabPath;
        }

        // Try lowercase
        $lowerPath = $this->modulesPath . '/' . strtolower($moduleName);
        if (is_dir($lowerPath) && file_exists($lowerPath . '/composer.json')) {
            return $lowerPath;
        }

        // Case-insensitive search
        if (is_dir($this->modulesPath)) {
            foreach (scandir($this->modulesPath) as $dir) {
                if ($dir === '.' || $dir === '..') {
                    continue;
                }

                if (strtolower($dir) === strtolower($moduleName)) {
                    $fullPath = $this->modulesPath . '/' . $dir;
                    if (is_dir($fullPath) && file_exists($fullPath . '/composer.json')) {
                        return $fullPath;
                    }
                }
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getModulesWithComposer(): array
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

            if (is_dir($fullPath) && file_exists($fullPath . '/composer.json')) {
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
}
