<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class ModulePackageCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:package
                            {module : The module name (e.g., Crm, crm)}
                            {--compile : Compile CSS before packaging}
                            {--minify : Minify the CSS during compilation}
                            {--output= : Custom output path for the ZIP file}
                            {--no-vendor : Exclude vendor directory from package}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Package a module into a distributable ZIP file with pre-compiled assets';

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

        // Get the PascalCase name for the ZIP folder (matches PSR-4 namespace)
        // This ensures compatibility with case-sensitive filesystems (Linux)
        $moduleNameForZip = $this->getModuleNamespaceFolder($modulePath, $moduleName);
        $moduleSlug = Str::slug($moduleName);

        // Get module version from module.json
        $moduleInfo = $this->getModuleInfo($modulePath);
        $version = $moduleInfo['version'] ?? '1.0.0';

        $this->info("Packaging module: {$moduleName} v{$version}");
        $this->line('');

        // Step 1: Compile CSS if requested (using --dist mode for self-contained package)
        if ($this->option('compile')) {
            $this->comment('Step 1: Compiling assets for distribution...');

            $compileOptions = [
                'module' => $moduleName,
                '--minify' => $this->option('minify'),
                '--dist' => true, // Build inside module directory
            ];
            $exitCode = $this->call('module:compile-css', $compileOptions);

            if ($exitCode !== 0) {
                $this->error('Asset compilation failed. Aborting packaging.');

                return self::FAILURE;
            }
            $this->line('');
        }

        // Step 2: Check for pre-compiled assets inside module's dist directory
        $buildDir = base_path("modules/{$moduleName}/dist/build-{$moduleSlug}");
        $hasPrecompiledAssets = is_dir($buildDir) && count(glob("{$buildDir}/*")) > 0;

        if (! $hasPrecompiledAssets) {
            $this->warn("No pre-compiled assets found in modules/{$moduleName}/dist/");
            $this->line('');

            if ($this->confirm('Would you like to compile assets now?', true)) {
                $exitCode = $this->call('module:compile-css', [
                    'module' => $moduleName,
                    '--minify' => true,
                    '--dist' => true, // Build inside module directory
                ]);

                if ($exitCode !== 0) {
                    $this->error('Asset compilation failed.');

                    return self::FAILURE;
                }
                $hasPrecompiledAssets = true;
            }
        }

        // Step 3: Create ZIP file
        $this->comment('Creating ZIP package...');
        $this->info("Using folder name '{$moduleNameForZip}' in ZIP (matches PSR-4 namespace)");

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

        // Add module files (including dist/ with pre-compiled assets)
        // Use $moduleNameForZip (PascalCase) for the folder in the ZIP to match PSR-4 namespace
        $this->line("  Adding module files...");
        $this->addDirectoryToZip($zip, $modulePath, $moduleNameForZip, $moduleNameForZip);

        if ($hasPrecompiledAssets) {
            $this->line("  Pre-compiled assets included in: {$moduleNameForZip}/dist/build-{$moduleSlug}/");
        }

        // Add manifest file for the module
        $manifest = $this->generateManifest($moduleNameForZip, $version, $hasPrecompiledAssets);
        $zip->addFromString("{$moduleNameForZip}/.module-manifest.json", json_encode($manifest, JSON_PRETTY_PRINT));

        $zip->close();

        // Show results
        $this->newLine();
        $this->info('Module packaged successfully!');
        $this->line('');
        $this->line("Output: {$outputPath}");
        $this->line("Size: " . $this->formatBytes(filesize($outputPath)));
        $this->line("Pre-compiled assets: " . ($hasPrecompiledAssets ? 'Yes (self-contained)' : 'No'));
        $this->line('');

        $this->comment('Installation instructions:');
        $this->line('  1. Upload ZIP via module manager or extract to modules/ directory');
        if ($hasPrecompiledAssets) {
            $this->line("  2. Run: php artisan module:publish-assets {$moduleNameForZip}");
            $this->line('     (This copies pre-built CSS/JS to public/ directory)');
        } else {
            $this->line('  2. Run: php artisan module:compile-css ' . $moduleNameForZip);
        }
        $this->line("  3. Run: php artisan module:enable {$moduleNameForZip}");

        return self::SUCCESS;
    }

    /**
     * Add a directory recursively to the ZIP archive.
     */
    private function addDirectoryToZip(ZipArchive $zip, string $sourcePath, string $zipPath, string $moduleName): void
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

            $zipEntryPath = "{$moduleName}/{$zipPath}/{$relativePath}";

            // Simplify the path for the module itself
            if (str_starts_with($zipPath, $moduleName)) {
                $zipEntryPath = "{$zipPath}/{$relativePath}";
            }

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
            $this->line('');
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
     *
     * This ensures the ZIP folder is PascalCase (e.g., "Crm" not "crm"),
     * which is required for case-sensitive filesystems (Linux).
     *
     * The folder name is extracted from the service provider namespace in module.json.
     */
    private function getModuleNamespaceFolder(string $modulePath, string $fallbackName): string
    {
        $moduleInfo = $this->getModuleInfo($modulePath);

        // Try to extract the module name from the providers array
        // e.g., "Modules\\Crm\\Providers\\CrmServiceProvider" -> "Crm"
        $providers = $moduleInfo['providers'] ?? [];

        foreach ($providers as $provider) {
            // Match pattern: Modules\{ModuleName}\...
            if (preg_match('/^Modules\\\\([^\\\\]+)\\\\/', $provider, $matches)) {
                return $matches[1]; // Returns "Crm" from "Modules\Crm\..."
            }
        }

        // Fallback: convert to PascalCase (studly case)
        return Str::studly($fallbackName);
    }
}
