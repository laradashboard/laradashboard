<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class BackupService
{
    protected string $backupPath;

    protected string $tempPath;

    public function __construct()
    {
        $this->backupPath = storage_path('app/core-backups');
        $this->tempPath = storage_path('app/core-upgrades-temp');
    }

    /**
     * Get the backup storage path.
     */
    public function getBackupPath(): string
    {
        return $this->backupPath;
    }

    /**
     * Get the temp storage path.
     */
    public function getTempPath(): string
    {
        return $this->tempPath;
    }

    /**
     * Create a default backup (core with modules, no database).
     * Used during upgrades to ensure modules are also backed up.
     */
    public function createBackup(): ?string
    {
        return $this->createBackupWithOptions('core_with_modules', false);
    }

    /**
     * Create a backup with specific options.
     *
     * @param  string  $backupType  One of: core, core_with_modules, core_with_uploads, full
     * @param  bool  $includeDatabase  Whether to include a database dump
     * @param  bool  $includeVendor  Whether to include the vendor folder (for production deployable backups)
     */
    public function createBackupWithOptions(string $backupType, bool $includeDatabase = false, bool $includeVendor = false): ?string
    {
        try {
            // Create backup directory
            if (! File::exists($this->backupPath)) {
                File::makeDirectory($this->backupPath, 0755, true);
            }

            $currentVersion = $this->getCurrentVersion()['version'];
            $timestamp = now()->format('Y-m-d_His');
            $vendorSuffix = $includeVendor ? '-with-vendor' : '';
            $backupFile = "{$this->backupPath}/backup-{$backupType}{$vendorSuffix}-{$currentVersion}-{$timestamp}.zip";

            $zip = new ZipArchive();
            if ($zip->open($backupFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                Log::error('Could not create backup zip file');

                return null;
            }

            // Get items to backup based on type
            $itemsToBackup = $this->getBackupItems($backupType, $includeVendor);

            foreach ($itemsToBackup as $item) {
                $path = base_path($item);
                if (File::isDirectory($path)) {
                    $this->addDirectoryToZip($zip, $path, $item);
                } elseif (File::exists($path)) {
                    $zip->addFile($path, $item);
                }
            }

            // Include database dump if requested
            if ($includeDatabase) {
                $sqlDump = $this->createDatabaseDump();
                if ($sqlDump) {
                    $zip->addFile($sqlDump, 'database/backup.sql');
                }
            }

            $zip->close();

            // Clean up temp SQL file
            if ($includeDatabase && isset($sqlDump) && File::exists($sqlDump)) {
                File::delete($sqlDump);
            }

            Log::info('Backup created successfully', [
                'path' => $backupFile,
                'type' => $backupType,
                'include_database' => $includeDatabase,
                'include_vendor' => $includeVendor,
            ]);

            return $backupFile;
        } catch (\Exception $e) {
            Log::error('Backup creation error', [
                'message' => $e->getMessage(),
                'type' => $backupType,
            ]);

            return null;
        }
    }

    /**
     * Get the items to backup based on backup type.
     *
     * @return array<int, string>
     */
    public function getBackupItems(string $backupType, bool $includeVendor = false): array
    {
        // Core items (always included)
        $coreItems = [
            'app',
            'bootstrap',
            'config',
            'database/factories',
            'database/migrations',
            'database/seeders',
            // Public directory files
            'public/.htaccess',
            'public/index.php',
            'public/favicon.ico',
            'public/robots.txt',
            'public/mix-manifest.json',
            // Public directory folders (excluding uploads and module-specific)
            'public/asset',
            'public/backend',
            'public/build',
            'public/css',
            'public/js',
            'public/images/logo',
            // Resources
            'resources/css',
            'resources/js',
            'resources/lang',
            'resources/views',
            'routes',
            // Storage directory structure (required for Laravel to work)
            'storage/app/.gitignore',
            'storage/framework/.gitignore',
            'storage/framework/cache/.gitignore',
            'storage/framework/sessions/.gitignore',
            'storage/framework/views/.gitignore',
            'storage/logs/.gitignore',
            // Root config files
            'version.json',
            'composer.json',
            'composer.lock',
            'package.json',
            'package-lock.json',
            'vite.config.js',
            'tailwind.config.js',
            'postcss.config.js',
            'artisan',
            '.env.example',
            '.htaccess',
            'index.php',
        ];

        // Vendor items (optional - for production deployable backups)
        $vendorItems = [
            'vendor',
        ];

        // Module items
        $moduleItems = [
            'Modules',
        ];

        // Module build files (separate from core build)
        $moduleBuildItems = $this->getModuleBuildDirectories();

        // Upload items
        $uploadItems = [
            'public/images/uploads',
            'public/uploads',
            'storage/app/public',
        ];

        $baseItems = match ($backupType) {
            'core' => $coreItems,
            'core_with_modules' => [...$coreItems, ...$moduleItems, ...$moduleBuildItems],
            'core_with_uploads' => [...$coreItems, ...$uploadItems],
            'full' => [...$coreItems, ...$moduleItems, ...$moduleBuildItems, ...$uploadItems],
            default => $coreItems,
        };

        // Add vendor if requested
        if ($includeVendor) {
            $baseItems = [...$baseItems, ...$vendorItems];
        }

        return $baseItems;
    }

    /**
     * Get module build directories dynamically.
     *
     * @return array<int, string>
     */
    public function getModuleBuildDirectories(): array
    {
        $buildDirs = [];
        $publicPath = public_path();

        if (File::isDirectory($publicPath)) {
            $directories = File::directories($publicPath);
            foreach ($directories as $dir) {
                $dirName = basename($dir);
                // Match build-* directories but not the main 'build' directory
                if (str_starts_with($dirName, 'build-') && $dirName !== 'build') {
                    $buildDirs[] = 'public/' . $dirName;
                }
            }
        }

        return $buildDirs;
    }

    /**
     * Create a database dump.
     */
    public function createDatabaseDump(): ?string
    {
        try {
            $connection = config('database.default');
            $driver = config("database.connections.{$connection}.driver");

            if ($driver !== 'mysql') {
                Log::warning('Database dump only supported for MySQL');

                return null;
            }

            $database = config("database.connections.{$connection}.database");
            $username = config("database.connections.{$connection}.username");
            $password = config("database.connections.{$connection}.password");
            $host = config("database.connections.{$connection}.host");
            $port = config("database.connections.{$connection}.port", 3306);

            $dumpFile = $this->tempPath . '/database_dump_' . time() . '.sql';

            // Create temp directory if not exists
            if (! File::exists($this->tempPath)) {
                File::makeDirectory($this->tempPath, 0755, true);
            }

            // Build mysqldump command
            $command = sprintf(
                'mysqldump -h%s -P%s -u%s -p%s %s > %s 2>/dev/null',
                escapeshellarg($host),
                escapeshellarg((string) $port),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($database),
                escapeshellarg($dumpFile)
            );

            exec($command, $output, $returnCode);

            if ($returnCode !== 0 || ! File::exists($dumpFile)) {
                Log::error('Database dump failed', ['returnCode' => $returnCode]);

                return null;
            }

            return $dumpFile;
        } catch (\Exception $e) {
            Log::error('Database dump error', ['message' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Add a directory to zip recursively.
     */
    public function addDirectoryToZip(ZipArchive $zip, string $path, string $relativePath): void
    {
        $files = File::allFiles($path);

        foreach ($files as $file) {
            $filePath = $file->getRealPath();
            $zipPath = $relativePath . '/' . str_replace($path . '/', '', $filePath);
            $zip->addFile($filePath, $zipPath);
        }
    }

    /**
     * Get list of available backups.
     */
    public function getBackups(): array
    {
        if (! File::exists($this->backupPath)) {
            return [];
        }

        $files = File::files($this->backupPath);
        $backups = [];

        foreach ($files as $file) {
            if ($file->getExtension() === 'zip') {
                $backups[] = [
                    'name' => $file->getFilename(),
                    'path' => $file->getRealPath(),
                    'size' => $this->formatFileSize($file->getSize()),
                    'created_at' => date('Y-m-d H:i:s', $file->getMTime()),
                ];
            }
        }

        // Sort by date descending
        usort($backups, fn ($a, $b) => strtotime($b['created_at']) - strtotime($a['created_at']));

        return $backups;
    }

    /**
     * Format file size.
     */
    public function formatFileSize(int $size): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return round($size, 2) . ' ' . $units[$unitIndex];
    }

    /**
     * Delete a backup file.
     */
    public function deleteBackup(string $filename): bool
    {
        $path = $this->backupPath . '/' . $filename;

        if (File::exists($path)) {
            return File::delete($path);
        }

        return false;
    }

    /**
     * Restore from backup.
     */
    public function restoreFromBackup(?string $backupFile): bool
    {
        if (! $backupFile || ! File::exists($backupFile)) {
            Log::warning('No backup file to restore from');

            return false;
        }

        try {
            $extractPath = $this->tempPath . '/restore';
            if (! $this->extractZip($backupFile, $extractPath)) {
                return false;
            }

            // Restore files
            $this->copyRestoreFiles($extractPath);

            // Ensure storage directory structure exists
            $this->ensureStorageDirectoriesExist();

            // Clean up
            File::deleteDirectory($extractPath);

            Log::info('Restored from backup successfully');

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to restore from backup', [
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Extract a zip file.
     */
    public function extractZip(string $zipPath, string $extractPath): bool
    {
        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            return false;
        }

        // Create extract directory
        if (! File::exists($extractPath)) {
            File::makeDirectory($extractPath, 0755, true);
        }

        $zip->extractTo($extractPath);
        $zip->close();

        return true;
    }

    /**
     * Copy restore files to the application.
     */
    protected function copyRestoreFiles(string $sourcePath): bool
    {
        try {
            // Find the actual source directory (might be nested)
            $directories = File::directories($sourcePath);
            if (count($directories) === 1) {
                $sourcePath = $directories[0];
            }

            // Directories to restore
            $directoriesToRestore = [
                'app',
                'bootstrap',
                'config',
                'database/factories',
                'database/migrations',
                'database/seeders',
                'public/asset',
                'public/backend',
                'public/build',
                'public/css',
                'public/js',
                'public/images/logo',
                'resources/css',
                'resources/js',
                'resources/lang',
                'resources/views',
                'routes',
                'Modules',
                'vendor',
            ];

            // Also restore module build directories if they exist
            $moduleBuildDirs = $this->getModuleBuildDirectoriesFromSource($sourcePath);
            $directoriesToRestore = [...$directoriesToRestore, ...$moduleBuildDirs];

            foreach ($directoriesToRestore as $dir) {
                $source = "{$sourcePath}/{$dir}";
                $dest = base_path($dir);

                if (File::isDirectory($source)) {
                    // For vendor folder, delete existing first to avoid conflicts
                    if ($dir === 'vendor' && File::isDirectory($dest)) {
                        File::deleteDirectory($dest);
                    }
                    File::copyDirectory($source, $dest);
                }
            }

            // Copy individual files
            $filesToRestore = [
                'version.json',
                'composer.json',
                'composer.lock',
                'package.json',
                'package-lock.json',
                'vite.config.js',
                'tailwind.config.js',
                'postcss.config.js',
                'artisan',
                '.env.example',
                '.htaccess',
                'index.php',
                // Public directory files
                'public/.htaccess',
                'public/index.php',
                'public/favicon.ico',
                'public/robots.txt',
                'public/mix-manifest.json',
            ];

            foreach ($filesToRestore as $file) {
                $source = "{$sourcePath}/{$file}";
                $dest = base_path($file);

                if (File::exists($source)) {
                    File::copy($source, $dest);
                }
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to copy restore files', [
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Ensure the storage directory structure exists.
     */
    protected function ensureStorageDirectoriesExist(): void
    {
        $directories = [
            storage_path('app'),
            storage_path('app/public'),
            storage_path('framework'),
            storage_path('framework/cache'),
            storage_path('framework/cache/data'),
            storage_path('framework/sessions'),
            storage_path('framework/testing'),
            storage_path('framework/views'),
            storage_path('logs'),
        ];

        foreach ($directories as $directory) {
            if (! File::isDirectory($directory)) {
                File::makeDirectory($directory, 0755, true);
            }
        }

        // Create .gitignore files if they don't exist
        $gitignoreContent = "*\n!.gitignore\n";
        $gitignoreFiles = [
            storage_path('app/.gitignore'),
            storage_path('app/public/.gitignore'),
            storage_path('framework/cache/.gitignore'),
            storage_path('framework/sessions/.gitignore'),
            storage_path('framework/testing/.gitignore'),
            storage_path('framework/views/.gitignore'),
            storage_path('logs/.gitignore'),
        ];

        foreach ($gitignoreFiles as $gitignoreFile) {
            if (! File::exists($gitignoreFile)) {
                File::put($gitignoreFile, $gitignoreContent);
            }
        }
    }

    /**
     * Get module build directories from a source path.
     *
     * @return array<int, string>
     */
    protected function getModuleBuildDirectoriesFromSource(string $sourcePath): array
    {
        $buildDirs = [];
        $publicPath = "{$sourcePath}/public";

        if (File::isDirectory($publicPath)) {
            $directories = File::directories($publicPath);
            foreach ($directories as $dir) {
                $dirName = basename($dir);
                // Match build-* directories
                if (str_starts_with($dirName, 'build-')) {
                    $buildDirs[] = "public/{$dirName}";
                }
            }
        }

        return $buildDirs;
    }

    /**
     * Get the current core version.
     */
    protected function getCurrentVersion(): array
    {
        $versionFile = base_path('version.json');

        if (! File::exists($versionFile)) {
            return [
                'version' => '0.0.0',
                'release_date' => null,
                'name' => 'LaraDashboard',
            ];
        }

        $content = File::get($versionFile);

        return json_decode($content, true) ?? [
            'version' => '0.0.0',
            'release_date' => null,
            'name' => 'LaraDashboard',
        ];
    }
}
