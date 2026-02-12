<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Process\Process;
use ZipArchive;

class CoreZipCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'core:zip
                            {--output= : Custom output path for the ZIP file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build and package the core application into a distributable ZIP file';

    /**
     * Directories and files to exclude from the package.
     */
    private array $excludePatterns = [
        // Git
        '.git',
        '.gitignore',
        '.gitattributes',

        // Dependencies
        'node_modules',
        'vendor/bin',

        // Dev dependencies (backup exclusion - composer --no-dev handles this)
        'vendor/phpstan/phpstan',
        'vendor/phpstan/extension-installer',
        'vendor/rector',
        'vendor/fakerphp',
        'vendor/phpunit',
        'vendor/pestphp',
        'vendor/mockery',
        'vendor/filp',
        'vendor/php-debugbar',
        'vendor/barryvdh',
        'vendor/beyondcode',
        'vendor/brianium',
        'vendor/larastan',
        'vendor/nunomaduro/collision',
        'vendor/nunomaduro/larastan',
        'vendor/laravel/pint',
        'vendor/laravel/sail',
        'vendor/laravel/boost',
        'vendor/spatie/ignition',
        'vendor/spatie/laravel-ignition',
        'vendor/spatie/flare-client-php',
        'vendor/spatie/backtrace',
        'vendor/spatie/error-solutions',

        // PHPUnit/Pest related test dependencies
        'vendor/sebastian',
        'vendor/hamcrest',
        'vendor/phar-io',
        'vendor/theseer',
        'vendor/staabm',
        'vendor/amphp',
        'vendor/revolt',
        'vendor/jean85',
        'vendor/kelunik',
        'vendor/fidry',
        'vendor/ta-tikoma',
        'vendor/daverandom',

        // Module-specific dependencies (modules have their own vendor)
        'vendor/laravel/scout',

        // IDE / Editor
        '.DS_Store',
        'Thumbs.db',
        '.idea',
        '.vscode',
        '.editorconfig',

        // Environment (keep .env.example, exclude others)
        '.env',
        '.env.testing',
        '.env.production',

        // Testing
        'tests',
        'phpunit.xml',
        'phpunit.xml.dist',
        '.phpunit.result.cache',
        'coverage',

        // Code Quality / Linting
        'phpstan.neon',
        'phpstan-baseline.neon',
        'pint.json',
        'pint-no-modules.json',
        'rector.php',
        '.php-cs-fixer.cache',
        '.styleci.yml',
        '.commitlintrc.cjs',

        // CI/CD
        '.github',
        '.husky',

        // AI / Tools
        '.claude',
        '.junie',
        '.mcp.json',
        '.factory',
        'CLAUDE.md',

        // Documentation (dev)
        'docs',
        'demo-screenshots',
        'README.md',
        'CONTRIBUTING.md',
        'COMMIT_CONVENTION.md',
        'Coding-Standard.md',
        'SECURITY.md',

        // Storage & bootstrap/cache - exclude, we create empty structure separately
        'storage',
        'bootstrap/cache',

        // Modules - exclude content, modules are installed separately
        'modules/',

        // Public uploads
        'public/hot',
        'public/images/uploads',
        'public/images/modules',
        'public/uploads',

        // Module build assets (modules are installed separately)
        'public/build-*',

        // Build artifacts
        'laradashboard-v*.zip',
        '*.log',

        // Legacy / Unused
        'webpack.mix.js',
        'releases',
        'api.json',
        'Users',

        // Package manager locks (optional - keep composer.lock for reproducible builds)
        'package-lock.json',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $basePath = base_path();
        $version = $this->getVersion();

        $this->newLine();
        $this->info('╔═══════════════════════════════════════════════════════════╗');
        $this->info("║  Building LaraDashboard Core v{$version}");
        $this->info('╚═══════════════════════════════════════════════════════════╝');
        $this->newLine();

        // Step 1: Install composer dependencies (production only)
        $this->comment('Step 1/4: Installing composer dependencies (production)...');
        if (! $this->runComposerInstall()) {
            $this->error('Composer install failed.');

            return self::FAILURE;
        }
        $this->info('  ✓ Composer dependencies installed (--no-dev)');
        $this->newLine();

        // Step 2: Install npm dependencies
        $this->comment('Step 2/4: Installing npm dependencies...');
        if (! $this->runNpmInstall()) {
            $this->error('npm install failed.');

            return self::FAILURE;
        }
        $this->info('  ✓ npm dependencies installed');
        $this->newLine();

        // Step 3: Build assets
        $this->comment('Step 3/4: Compiling assets...');
        if (! $this->runNpmBuild()) {
            $this->error('Asset compilation failed.');

            return self::FAILURE;
        }
        $this->info('  ✓ Assets compiled');
        $this->newLine();

        // Step 4: Create ZIP file
        $this->comment('Step 4/4: Creating ZIP package...');

        $outputPath = $this->option('output')
            ?? $basePath . "/laradashboard-v{$version}.zip";

        // Remove existing ZIP if it exists
        if (file_exists($outputPath)) {
            unlink($outputPath);
        }

        $zip = new ZipArchive();
        if ($zip->open($outputPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->error("Failed to create ZIP file: {$outputPath}");

            return self::FAILURE;
        }

        // Add core files
        $this->addDirectoryToZip($zip, $basePath, 'laradashboard');

        // Add required empty directories with .gitkeep
        $this->addEmptyDirectories($zip);

        // Add .env file from .env.example
        $envExample = $basePath . '/.env.example';
        if (file_exists($envExample)) {
            $zip->addFile($envExample, 'laradashboard/.env');
        }

        $zip->close();

        $this->info('  ✓ ZIP package created');
        $this->newLine();

        // Reinstall dev dependencies after build
        $this->reinstallDevDependencies();

        // Show results
        $this->info('╔═══════════════════════════════════════════════════════════╗');
        $this->info('║  Build Complete!');
        $this->info('╚═══════════════════════════════════════════════════════════╝');
        $this->newLine();

        $this->line("  <fg=cyan>Version:</> v{$version}");
        $this->line("  <fg=cyan>Output:</> {$outputPath}");
        $this->line('  <fg=cyan>Size:</> ' . $this->formatBytes(filesize($outputPath)));
        $this->newLine();

        $this->comment('Ready to distribute!');
        $this->newLine();

        return self::SUCCESS;
    }

    /**
     * Get version from version.json.
     */
    private function getVersion(): string
    {
        $versionFile = base_path('version.json');

        if (file_exists($versionFile)) {
            $content = json_decode(file_get_contents($versionFile), true);

            return $content['version'] ?? '1.0.0';
        }

        return '1.0.0';
    }

    /**
     * Run composer install --no-dev.
     */
    private function runComposerInstall(): bool
    {
        $process = new Process([
            'composer',
            'install',
            '--no-dev',
            '--no-interaction',
            '--optimize-autoloader',
        ], base_path());
        $process->setTimeout(600);

        $process->run(function ($type, $buffer) {
            // Suppress output for cleaner display
        });

        return $process->isSuccessful();
    }

    /**
     * Run npm install.
     */
    private function runNpmInstall(): bool
    {
        $process = new Process(['npm', 'install'], base_path());
        $process->setTimeout(300);

        $process->run(function ($type, $buffer) {
            // Suppress output for cleaner display
        });

        return $process->isSuccessful();
    }

    /**
     * Run npm run build.
     */
    private function runNpmBuild(): bool
    {
        $process = new Process(['npm', 'run', 'build'], base_path());
        $process->setTimeout(300);

        $process->run(function ($type, $buffer) {
            // Suppress output for cleaner display
        });

        return $process->isSuccessful();
    }

    /**
     * Reinstall dev dependencies after build.
     */
    private function reinstallDevDependencies(): void
    {
        $this->line('  <fg=gray>Reinstalling dev dependencies...</>');

        $process = new Process([
            'composer',
            'install',
            '--no-interaction',
        ], base_path());
        $process->setTimeout(600);
        $process->run();
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

            $zipEntryPath = "{$zipBasePath}/{$relativePath}";

            if ($item->isDir()) {
                $zip->addEmptyDir($zipEntryPath);
            } else {
                $zip->addFile($filePath, $zipEntryPath);
            }
        }
    }

    /**
     * Add empty directories with .gitignore files for Laravel structure.
     */
    private function addEmptyDirectories(ZipArchive $zip): void
    {
        $dirsWithGitignore = [
            'storage/app' => "*\n!public/\n!.gitignore",
            'storage/app/public' => "*\n!.gitignore",
            'storage/framework' => "*\n!.gitignore",
            'storage/framework/cache' => "*\n!data/\n!.gitignore",
            'storage/framework/cache/data' => "*\n!.gitignore",
            'storage/framework/sessions' => "*\n!.gitignore",
            'storage/framework/testing' => "*\n!.gitignore",
            'storage/framework/views' => "*\n!.gitignore",
            'storage/logs' => "*\n!.gitignore",
            'bootstrap/cache' => "*\n!.gitignore",
        ];

        foreach ($dirsWithGitignore as $dir => $gitignoreContent) {
            $zip->addEmptyDir("laradashboard/{$dir}");
            $zip->addFromString("laradashboard/{$dir}/.gitignore", $gitignoreContent);
        }

        // Add empty modules directory (modules are installed separately)
        $zip->addEmptyDir('laradashboard/modules');
        $zip->addFromString('laradashboard/modules/.gitkeep', '');
    }

    /**
     * Check if a file/directory should be excluded.
     */
    private function shouldExclude(string $path): bool
    {
        // Always exclude nested node_modules and vendor directories
        if (str_contains($path, '/node_modules/') || str_contains($path, '/node_modules')) {
            return true;
        }

        foreach ($this->excludePatterns as $pattern) {
            // Directory pattern (ends with /) - only match directories
            if (str_ends_with($pattern, '/')) {
                $dirPattern = rtrim($pattern, '/');
                if ($path === $dirPattern || str_starts_with($path, $dirPattern . '/')) {
                    return true;
                }

                continue;
            }

            // Exact match
            if ($path === $pattern || basename($path) === $pattern) {
                return true;
            }

            // Directory match (starts with pattern)
            if (str_starts_with($path, $pattern . '/') || str_starts_with($path, $pattern . DIRECTORY_SEPARATOR)) {
                return true;
            }

            // Wildcard match (check both full path and basename)
            if (str_contains($pattern, '*')) {
                if (fnmatch($pattern, $path) || fnmatch($pattern, basename($path))) {
                    return true;
                }
            }
        }

        return false;
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
}
