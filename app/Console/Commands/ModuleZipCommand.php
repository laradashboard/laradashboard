<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Process\Process;
use ZipArchive;

class ModuleZipCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:zip
                            {module : The module name (e.g., Crm, LaraDashboard)}
                            {--skip-composer : Skip composer install step}
                            {--skip-npm : Skip npm install step}
                            {--skip-compile : Skip asset compilation}
                            {--no-minify : Do not minify assets}
                            {--output= : Custom output path for the ZIP file}
                            {--no-vendor : Exclude vendor directory from package}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build and package a module into a distributable ZIP file (compile + package in one step)';

    /**
     * Directories and files to exclude from the package.
     */
    private array $excludePatterns = [
        '.git',
        '.gitignore',
        '.gitattributes',
        'node_modules',
        '.DS_Store',
        'Thumbs.db',
        '.idea',
        '.vscode',
        '*.log',
        '.env',
        '.env.*',
        'tests',
        'phpunit.xml',
        'phpstan.neon',
        '.php-cs-fixer.cache',
        '.claude',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $moduleName = $this->findModuleName($this->argument('module'));

        if (! $moduleName) {
            $this->error("Module '{$this->argument('module')}' not found.");
            $this->showAvailableModules();

            return self::FAILURE;
        }

        $modulePath = base_path("modules/{$moduleName}");
        $moduleSlug = Str::slug($moduleName);

        // Get module version from module.json
        $moduleInfo = $this->getModuleInfo($modulePath);
        $version = $moduleInfo['version'] ?? '1.0.0';

        $this->newLine();
        $this->info("╔═══════════════════════════════════════════════════════════╗");
        $this->info("║  Building Module: {$moduleName} v{$version}");
        $this->info("╚═══════════════════════════════════════════════════════════╝");
        $this->newLine();

        // Check if module has a vite.config.js
        $hasViteConfig = file_exists("{$modulePath}/vite.config.js");
        $hasComposerJson = file_exists("{$modulePath}/composer.json");

        // Step 1: Install composer dependencies (if composer.json exists)
        if (! $this->option('skip-composer') && $hasComposerJson) {
            $this->comment('Step 1/5: Installing composer dependencies (production)...');

            if (! $this->runComposerInstall($modulePath)) {
                $this->error('composer install failed.');

                return self::FAILURE;
            }
            $this->info('  ✓ Composer dependencies installed (--no-dev)');
            $this->newLine();
        } else {
            $this->comment('Step 1/5: Skipping composer install');
            if (! $hasComposerJson) {
                $this->line('  (No composer.json found)');
            }
            $this->newLine();
        }

        // Step 2: Install npm dependencies (if package.json exists)
        if (! $this->option('skip-npm') && file_exists("{$modulePath}/package.json")) {
            $this->comment('Step 2/5: Installing npm dependencies...');

            if (! $this->runNpmInstall($modulePath)) {
                $this->error('npm install failed.');

                return self::FAILURE;
            }
            $this->info('  ✓ npm dependencies installed');
            $this->newLine();
        } else {
            $this->comment('Step 2/5: Skipping npm install');
            $this->newLine();
        }

        // Step 3: Compile assets for distribution
        if (! $this->option('skip-compile') && $hasViteConfig) {
            $this->comment('Step 3/5: Compiling assets for distribution...');

            if (! $this->compileAssets($moduleName, $modulePath)) {
                $this->error('Asset compilation failed.');

                return self::FAILURE;
            }
            $this->info("  ✓ Assets compiled to modules/{$moduleName}/dist/build-{$moduleSlug}/");
            $this->newLine();
        } else {
            $this->comment('Step 3/5: Skipping asset compilation');
            if (! $hasViteConfig) {
                $this->line('  (No vite.config.js found)');
            }
            $this->newLine();
        }

        // Step 4: Verify pre-compiled assets exist
        $this->comment('Step 4/5: Verifying build...');
        $buildDir = "{$modulePath}/dist/build-{$moduleSlug}";
        $hasPrecompiledAssets = is_dir($buildDir) && count(glob("{$buildDir}/*")) > 0;

        if ($hasPrecompiledAssets) {
            $this->info("  ✓ Pre-compiled assets found in dist/build-{$moduleSlug}/");
        } else {
            $this->warn("  ⚠ No pre-compiled assets found");
            $this->line("    Users will need to run 'npm install && npm run build' after installation");
        }

        // Verify marketplace-assets
        $marketplaceAssetsDir = "{$modulePath}/marketplace-assets";
        if (is_dir($marketplaceAssetsDir)) {
            $this->info('  ✓ marketplace-assets/ folder found');
        } else {
            $this->warn('  ⚠ No marketplace-assets/ folder (logo/banner will be missing)');
        }
        $this->newLine();

        // Step 5: Create ZIP file
        $this->comment('Step 5/5: Creating ZIP package...');

        // Get the PascalCase name for the ZIP folder
        $moduleNameForZip = $this->getModuleNamespaceFolder($modulePath, $moduleName);

        $outputPath = $this->option('output')
            ?? base_path("modules/{$moduleSlug}-v{$version}.zip");

        // Remove existing ZIP if it exists
        if (file_exists($outputPath)) {
            unlink($outputPath);
        }

        $zip = new ZipArchive();
        if ($zip->open($outputPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->error("Failed to create ZIP file: {$outputPath}");

            return self::FAILURE;
        }

        // Add module files
        $this->addDirectoryToZip($zip, $modulePath, $moduleNameForZip);

        // Add manifest file
        $manifest = $this->generateManifest($moduleNameForZip, $version, $hasPrecompiledAssets);
        $zip->addFromString("{$moduleNameForZip}/.module-manifest.json", json_encode($manifest, JSON_PRETTY_PRINT));

        $zip->close();

        $this->info("  ✓ ZIP package created");
        $this->newLine();

        // Show results
        $this->info("╔═══════════════════════════════════════════════════════════╗");
        $this->info("║  Build Complete!");
        $this->info("╚═══════════════════════════════════════════════════════════╝");
        $this->newLine();

        $this->line("  <fg=cyan>Module:</> {$moduleName} v{$version}");
        $this->line("  <fg=cyan>Output:</> {$outputPath}");
        $this->line("  <fg=cyan>Size:</> " . $this->formatBytes(filesize($outputPath)));
        $this->line("  <fg=cyan>Pre-compiled assets:</> " . ($hasPrecompiledAssets ? '<fg=green>Yes</>' : '<fg=yellow>No</>'));
        $this->newLine();

        $this->comment('Ready to upload to marketplace or distribute!');
        $this->newLine();

        return self::SUCCESS;
    }

    /**
     * Run composer install --no-dev in the module directory.
     */
    private function runComposerInstall(string $modulePath): bool
    {
        $process = new Process([
            'composer',
            'install',
            '--no-dev',
            '--no-interaction',
            '--optimize-autoloader',
        ], $modulePath);
        $process->setTimeout(600);

        $process->run(function ($type, $buffer) {
            // Suppress output for cleaner display
        });

        return $process->isSuccessful();
    }

    /**
     * Run npm install in the module directory.
     */
    private function runNpmInstall(string $modulePath): bool
    {
        $process = new Process(['npm', 'install'], $modulePath);
        $process->setTimeout(300);

        $process->run(function ($type, $buffer) {
            // Suppress output for cleaner display
        });

        return $process->isSuccessful();
    }

    /**
     * Compile assets using Vite with dist mode.
     */
    private function compileAssets(string $moduleName, string $modulePath): bool
    {
        $configPath = "modules/{$moduleName}/vite.config.js";

        $command = ['npx', 'vite', 'build', '--config', $configPath];

        if (! $this->option('no-minify')) {
            $command[] = '--minify';
        }

        $process = new Process($command, base_path());
        $process->setTimeout(300);

        // Set environment variable for dist build
        $env = $process->getEnv();
        $env['MODULE_DIST_BUILD'] = 'true';
        $process->setEnv($env);

        $process->run(function ($type, $buffer) {
            // Suppress output for cleaner display
        });

        return $process->isSuccessful();
    }

    /**
     * Add a directory recursively to the ZIP archive.
     */
    private function addDirectoryToZip(ZipArchive $zip, string $sourcePath, string $zipBasePath): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourcePath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $filePath = $item->getPathname();
            $relativePath = substr($filePath, strlen($sourcePath) + 1);

            // Skip excluded files/directories
            if ($this->shouldExclude($relativePath)) {
                continue;
            }

            // Skip vendor if --no-vendor option is set
            if ($this->option('no-vendor') && str_starts_with($relativePath, 'vendor')) {
                continue;
            }

            $zipEntryPath = "{$zipBasePath}/{$relativePath}";

            if ($item->isDir()) {
                $zip->addEmptyDir($zipEntryPath);
            } else {
                $zip->addFile($filePath, $zipEntryPath);
            }
        }
    }

    /**
     * Check if a file/directory should be excluded.
     */
    private function shouldExclude(string $path): bool
    {
        foreach ($this->excludePatterns as $pattern) {
            // Exact match
            if ($path === $pattern || basename($path) === $pattern) {
                return true;
            }

            // Directory match
            if (str_starts_with($path, $pattern . '/') || str_starts_with($path, $pattern . DIRECTORY_SEPARATOR)) {
                return true;
            }

            // Wildcard match
            if (str_contains($pattern, '*') && fnmatch($pattern, basename($path))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get module info from module.json.
     */
    private function getModuleInfo(string $modulePath): array
    {
        $moduleJsonPath = "{$modulePath}/module.json";

        if (file_exists($moduleJsonPath)) {
            $content = file_get_contents($moduleJsonPath);

            return json_decode($content, true) ?? [];
        }

        return [];
    }

    /**
     * Generate a manifest file for the packaged module.
     */
    private function generateManifest(string $moduleName, string $version, bool $hasPrecompiledAssets): array
    {
        return [
            'name' => $moduleName,
            'version' => $version,
            'packaged_at' => now()->toIso8601String(),
            'packager_version' => '1.0.0',
            'has_precompiled_assets' => $hasPrecompiledAssets,
            'requires_npm_build' => ! $hasPrecompiledAssets,
            'laradashboard_version' => config('app.version', '2.0.0'),
            'php_version' => PHP_VERSION,
        ];
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
            $this->newLine();
            $this->line('Available modules:');
            foreach ($modules as $mod) {
                $this->line("  - {$mod}");
            }
        }
    }

    /**
     * Format bytes to human readable format.
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

    /**
     * Get the module folder name for the ZIP that matches the PSR-4 namespace.
     */
    private function getModuleNamespaceFolder(string $modulePath, string $fallbackName): string
    {
        $moduleInfo = $this->getModuleInfo($modulePath);

        $providers = $moduleInfo['providers'] ?? [];

        foreach ($providers as $provider) {
            if (preg_match('/^Modules\\\\([^\\\\]+)\\\\/', $provider, $matches)) {
                return $matches[1];
            }
        }

        return Str::studly($fallbackName);
    }
}
