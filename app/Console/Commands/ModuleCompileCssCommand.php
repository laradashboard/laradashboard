<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class ModuleCompileCssCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:compile-css
                            {module : The module name (e.g., Crm, crm)}
                            {--watch : Watch for changes and recompile}
                            {--minify : Minify the output (production build)}
                            {--dist : Build for distribution (assets inside module directory)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compile assets for a module using Vite (for pre-built distribution)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $moduleName = $this->findModuleName($this->argument('module'));

        if (! $moduleName) {
            $this->error("Module '{$this->argument('module')}' not found.");
            $this->line('');
            $this->line('Available modules:');
            foreach ($this->getAvailableModules() as $mod) {
                $this->line("  - {$mod}");
            }

            return self::FAILURE;
        }

        $modulePath = base_path("modules/{$moduleName}");
        $moduleSlug = Str::slug($moduleName);

        // Check if module has a vite.config.js
        $viteConfigPath = "{$modulePath}/vite.config.js";
        if (! file_exists($viteConfigPath)) {
            $this->error("No vite.config.js found for module '{$moduleName}'.");
            $this->line('');
            $this->line("Expected: modules/{$moduleName}/vite.config.js");
            $this->line('');
            $this->line('Module developers need to create a vite.config.js that:');
            $this->line('  1. Imports @tailwindcss/vite plugin');
            $this->line('  2. Specifies input CSS/JS files');
            $this->line('  3. Outputs to public/build-{module}/');

            return self::FAILURE;
        }

        $this->info("Building assets for module: {$moduleName}");
        $this->line("Vite config: {$viteConfigPath}");
        $this->line('');

        // Build the Vite command
        $relativeConfigPath = "modules/{$moduleName}/vite.config.js";
        $command = $this->buildViteCommand($relativeConfigPath);

        $this->comment('Running: ' . implode(' ', $command));
        if ($this->option('dist')) {
            $this->line('Mode: Distribution (assets inside module)');
        } else {
            $this->line('Mode: Development (assets in public/)');
        }
        $this->line('');

        // Execute the command with environment variables
        $process = new Process($command, base_path());
        $process->setTimeout($this->option('watch') ? null : 300);

        // Set environment variable for dist build
        if ($this->option('dist')) {
            $env = $process->getEnv();
            $env['MODULE_DIST_BUILD'] = 'true';
            $process->setEnv($env);
        }

        if ($this->option('watch')) {
            $this->info('Watching for changes... Press Ctrl+C to stop.');
            $process->setTty(Process::isTtySupported());
        }

        $process->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        if (! $process->isSuccessful() && ! $this->option('watch')) {
            $this->error('Build failed.');
            $this->line('');
            $this->line('Common issues:');
            $this->line('  - Missing dependencies: run `npm install`');
            $this->line('  - Invalid vite.config.js syntax');
            $this->line('  - CSS file not found at specified path');

            return self::FAILURE;
        }

        if (! $this->option('watch')) {
            $this->newLine();
            $this->info("Module assets built successfully!");

            // Show output directory info
            $outputDir = $this->option('dist')
                ? base_path("modules/{$moduleName}/dist/build-{$moduleSlug}")
                : public_path("build-{$moduleSlug}");

            $outputDisplay = $this->option('dist')
                ? "modules/{$moduleName}/dist/build-{$moduleSlug}/"
                : "public/build-{$moduleSlug}/";

            if (is_dir($outputDir)) {
                $this->line("Output: {$outputDisplay}");

                // Count all files recursively
                $files = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($outputDir, \RecursiveDirectoryIterator::SKIP_DOTS)
                );

                $fileCount = 0;
                $totalSize = 0;

                foreach ($files as $file) {
                    if ($file->isFile()) {
                        $fileCount++;
                        $totalSize += $file->getSize();
                    }
                }

                $this->line("Files: {$fileCount}");
                $this->line("Total size: " . $this->formatBytes($totalSize));
            }

            $this->newLine();
            $this->comment('Next steps:');
            $this->line('  1. Test your module in the browser');
            $this->line('  2. Run `php artisan module:package ' . $moduleName . '` to create a distributable ZIP');
            $this->line('  3. The ZIP will include pre-compiled assets (no npm needed on target server)');
        }

        return self::SUCCESS;
    }

    /**
     * Find the actual module directory name (case-insensitive)
     */
    private function findModuleName(string $module): ?string
    {
        $modulesPath = base_path('modules');

        if (! is_dir($modulesPath)) {
            return null;
        }

        // Try exact match first
        if (is_dir("{$modulesPath}/{$module}")) {
            return $module;
        }

        // Try case-insensitive search
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
     * Get list of available modules
     */
    private function getAvailableModules(): array
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
                // Only include modules with vite.config.js
                if (file_exists("{$modulesPath}/{$dir}/vite.config.js")) {
                    $modules[] = $dir;
                }
            }
        }

        return $modules;
    }

    /**
     * Build the Vite build command
     */
    private function buildViteCommand(string $configPath): array
    {
        if ($this->option('watch')) {
            // For watch mode, use vite dev
            return ['npx', 'vite', '--config', $configPath];
        }

        $command = ['npx', 'vite', 'build', '--config', $configPath];

        if ($this->option('minify')) {
            $command[] = '--minify';
        }

        return $command;
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
