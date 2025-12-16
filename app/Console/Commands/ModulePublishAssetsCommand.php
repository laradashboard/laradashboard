<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Modules\ModuleService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ModulePublishAssetsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:publish-assets
                            {module? : The module name (e.g., Crm). If not provided, publishes all enabled modules}
                            {--force : Overwrite existing assets}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish pre-built module assets to the public directory';

    public function __construct(private readonly ModuleService $moduleService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $moduleName = $this->argument('module');

        if ($moduleName) {
            // Publish single module
            $moduleName = $this->findModuleName($moduleName);

            if (! $moduleName) {
                $this->error("Module '{$this->argument('module')}' not found.");
                $this->showAvailableModules();

                return self::FAILURE;
            }

            return $this->publishModule($moduleName) ? self::SUCCESS : self::FAILURE;
        }

        // Publish all enabled modules
        $modules = $this->getEnabledModulesWithAssets();

        if (empty($modules)) {
            $this->info('No modules with pre-built assets found.');

            return self::SUCCESS;
        }

        $this->info('Publishing assets for ' . count($modules) . ' module(s)...');
        $this->line('');

        $success = true;
        foreach ($modules as $module) {
            if (! $this->publishModule($module)) {
                $success = false;
            }
        }

        return $success ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Publish assets for a single module.
     */
    private function publishModule(string $moduleName): bool
    {
        $moduleSlug = Str::slug($moduleName);

        // Check if module has pre-built assets
        if (! $this->moduleService->hasPrebuiltAssets($moduleName)) {
            $this->warn("Module '{$moduleName}' has no pre-built assets at:");
            $this->line("  modules/{$moduleName}/dist/build-{$moduleSlug}/");
            $this->line('');
            $this->line('Run the following to build:');
            $this->line("  php artisan module:compile-css {$moduleName} --dist");

            return false;
        }

        $targetPath = public_path("build-{$moduleSlug}");

        // Check if target exists and not forcing
        if (is_dir($targetPath) && ! $this->option('force')) {
            $this->warn("Assets already exist at public/build-{$moduleSlug}/");

            if (! $this->confirm('Overwrite existing assets?', false)) {
                $this->line("Skipped {$moduleName}");

                return true;
            }
        }

        // Use ModuleService to publish assets
        $result = $this->moduleService->publishModuleAssets($moduleName, force: true);

        if ($result) {
            $this->info("Published: {$moduleName}");
            $this->line("  From: modules/{$moduleName}/dist/build-{$moduleSlug}/");
            $this->line("  To:   public/build-{$moduleSlug}/");
        } else {
            $this->error("Failed to publish assets for {$moduleName}");
        }

        return $result;
    }

    /**
     * Get list of enabled modules that have pre-built assets.
     */
    private function getEnabledModulesWithAssets(): array
    {
        $modules = [];
        $statuses = $this->moduleService->getModuleStatuses();

        foreach ($statuses as $moduleName => $enabled) {
            if ($enabled && $this->moduleService->hasPrebuiltAssets($moduleName)) {
                $modules[] = $moduleName;
            }
        }

        return $modules;
    }

    /**
     * Find the actual module directory name (case-insensitive).
     */
    private function findModuleName(string $module): ?string
    {
        $modulesPath = base_path('modules');

        if (! is_dir($modulesPath)) {
            return null;
        }

        if (is_dir("{$modulesPath}/{$module}")) {
            return $module;
        }

        $dirs = scandir($modulesPath);
        foreach ($dirs as $dir) {
            if ($dir === '.' || $dir === '..') {
                continue;
            }
            if (strtolower($dir) === strtolower($module)) {
                return $dir;
            }
        }

        return null;
    }

    /**
     * Show available modules.
     */
    private function showAvailableModules(): void
    {
        $modulesPath = base_path('modules');
        $modules = [];

        if (is_dir($modulesPath)) {
            $dirs = scandir($modulesPath);
            foreach ($dirs as $dir) {
                if ($dir === '.' || $dir === '..' || ! is_dir("{$modulesPath}/{$dir}")) {
                    continue;
                }
                if (str_starts_with($dir, '.') || str_starts_with($dir, '_')) {
                    continue;
                }
                $modules[] = $dir;
            }
        }

        if ($modules) {
            $this->line('');
            $this->line('Available modules:');
            foreach ($modules as $mod) {
                $hasAssets = $this->moduleService->hasPrebuiltAssets($mod);
                $status = $hasAssets ? '(has pre-built assets)' : '';
                $this->line("  - {$mod} {$status}");
            }
        }
    }
}
