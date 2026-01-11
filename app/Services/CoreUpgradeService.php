<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class CoreUpgradeService
{
    protected string $versionFile;

    protected string $tempPath;

    public function __construct(
        protected BackupService $backupService
    ) {
        $this->versionFile = base_path('version.json');
        $this->tempPath = storage_path('app/core-upgrades-temp');
    }

    /**
     * Get the current core version.
     */
    public function getCurrentVersion(): array
    {
        if (! File::exists($this->versionFile)) {
            return [
                'version' => '0.0.0',
                'release_date' => null,
                'name' => 'LaraDashboard',
            ];
        }

        $content = File::get($this->versionFile);

        return json_decode($content, true) ?? [
            'version' => '0.0.0',
            'release_date' => null,
            'name' => 'LaraDashboard',
        ];
    }

    /**
     * Get the marketplace API URL.
     */
    protected function getMarketplaceUrl(): string
    {
        return rtrim(config('laradashboard.marketplace_url', 'http://localhost:8000'), '/');
    }

    /**
     * Check for available updates from the marketplace.
     */
    public function checkForUpdates(): ?array
    {
        try {
            $currentVersion = $this->getCurrentVersion();
            $response = Http::timeout(30)->post($this->getMarketplaceUrl().'/api/core/check-updates', [
                'current_version' => $currentVersion['version'],
            ]);

            if (! $response->successful()) {
                Log::warning('Core upgrade check failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $data = $response->json();

            if ($data['success'] && $data['has_update']) {
                // Store the update info in settings
                $this->storeUpdateInfo($data);

                return $data;
            }

            // No update available, clear stored update info
            $this->clearUpdateInfo();

            return $data;
        } catch (\Exception $e) {
            Log::error('Core upgrade check error', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Store update information in settings.
     */
    protected function storeUpdateInfo(array $data): void
    {
        Setting::updateOrCreate(
            ['option_name' => 'ld_core_upgrade_available'],
            ['option_value' => json_encode([
                'has_update' => true,
                'latest_version' => $data['latest_version'],
                'latest_update' => $data['latest_update'],
                'has_critical' => $data['has_critical'] ?? false,
                'checked_at' => now()->toIso8601String(),
            ])]
        );
    }

    /**
     * Clear stored update information.
     */
    public function clearUpdateInfo(): void
    {
        Setting::where('option_name', 'ld_core_upgrade_available')->delete();
    }

    /**
     * Get stored update information.
     */
    public function getStoredUpdateInfo(): ?array
    {
        $setting = Setting::where('option_name', 'ld_core_upgrade_available')->first();

        if (! $setting) {
            return null;
        }

        return json_decode($setting->option_value, true);
    }

    /**
     * Download the upgrade package.
     */
    public function downloadUpgrade(string $version): ?string
    {
        try {
            // Create temp directory
            if (! File::exists($this->tempPath)) {
                File::makeDirectory($this->tempPath, 0755, true);
            }

            $downloadUrl = $this->getMarketplaceUrl()."/api/core/download/{$version}";
            $zipPath = $this->tempPath."/laradashboard-{$version}.zip";

            // Download the file
            $response = Http::timeout(600)->withOptions([
                'sink' => $zipPath,
            ])->get($downloadUrl);

            if (! $response->successful()) {
                Log::error('Core upgrade download failed', [
                    'version' => $version,
                    'status' => $response->status(),
                ]);

                return null;
            }

            // Verify the download
            if (! File::exists($zipPath) || File::size($zipPath) === 0) {
                Log::error('Downloaded file is empty or missing', ['path' => $zipPath]);

                return null;
            }

            return $zipPath;
        } catch (\Exception $e) {
            Log::error('Core upgrade download error', [
                'version' => $version,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Create a backup of the current installation.
     * Delegates to BackupService.
     */
    public function createBackup(): ?string
    {
        return $this->backupService->createBackup();
    }

    /**
     * Create a backup with specific options.
     * Delegates to BackupService.
     */
    public function createBackupWithOptions(string $backupType, bool $includeDatabase = false, bool $includeVendor = false): ?string
    {
        return $this->backupService->createBackupWithOptions($backupType, $includeDatabase, $includeVendor);
    }

    /**
     * Get list of available backups.
     * Delegates to BackupService.
     */
    public function getBackups(): array
    {
        return $this->backupService->getBackups();
    }

    /**
     * Delete a backup file.
     * Delegates to BackupService.
     */
    public function deleteBackup(string $filename): bool
    {
        return $this->backupService->deleteBackup($filename);
    }

    /**
     * Restore from backup.
     * Delegates to BackupService.
     */
    public function restoreFromBackup(?string $backupFile): bool
    {
        return $this->backupService->restoreFromBackup($backupFile);
    }

    /**
     * Perform the upgrade.
     */
    public function performUpgrade(string $version, ?string $backupFile = null): array
    {
        $result = [
            'success' => false,
            'message' => '',
            'backup_file' => $backupFile,
        ];

        try {
            // Put application in maintenance mode
            Artisan::call('down', ['--secret' => 'upgrade-in-progress']);

            // Download the upgrade package
            $zipPath = $this->downloadUpgrade($version);
            if (! $zipPath) {
                $result['message'] = 'Failed to download upgrade package.';
                $this->restoreFromBackup($backupFile);
                Artisan::call('up');

                return $result;
            }

            // Extract the upgrade package
            $extractPath = $this->tempPath.'/extracted';
            if (! $this->extractZip($zipPath, $extractPath)) {
                $result['message'] = 'Failed to extract upgrade package.';
                $this->restoreFromBackup($backupFile);
                Artisan::call('up');

                return $result;
            }

            // Copy files to the application
            if (! $this->copyUpgradeFiles($extractPath)) {
                $result['message'] = 'Failed to copy upgrade files.';
                $this->restoreFromBackup($backupFile);
                Artisan::call('up');

                return $result;
            }

            // Ensure storage directory structure exists
            $this->ensureStorageDirectoriesExist();

            // Run migrations
            Artisan::call('migrate', ['--force' => true]);

            // Clear caches
            Artisan::call('optimize:clear');

            // Clean up temp files
            File::deleteDirectory($this->tempPath);

            // Clear update info from settings
            $this->clearUpdateInfo();

            // Bring application back online
            Artisan::call('up');

            $result['success'] = true;
            $result['message'] = "Successfully upgraded to version {$version}";

            Log::info('Core upgrade completed successfully', ['version' => $version]);

            return $result;
        } catch (\Exception $e) {
            Log::error('Core upgrade error', [
                'version' => $version,
                'message' => $e->getMessage(),
            ]);

            // Try to restore from backup
            $this->restoreFromBackup($backupFile);

            // Bring application back online
            Artisan::call('up');

            $result['message'] = 'Upgrade failed: '.$e->getMessage();

            return $result;
        }
    }

    /**
     * Perform upgrade from an uploaded zip file.
     */
    public function performUpgradeFromUpload(UploadedFile $file, ?string $backupFile = null): array
    {
        $result = [
            'success' => false,
            'message' => '',
            'backup_file' => $backupFile,
        ];

        try {
            // Create temp directory
            if (! File::exists($this->tempPath)) {
                File::makeDirectory($this->tempPath, 0755, true);
            }

            // Store the uploaded file
            $zipPath = $this->tempPath.'/'.time().'_'.$file->getClientOriginalName();
            $file->move($this->tempPath, basename($zipPath));

            // Verify the file exists
            if (! File::exists($zipPath) || File::size($zipPath) === 0) {
                $result['message'] = __('Uploaded file is empty or invalid.');

                return $result;
            }

            // Put application in maintenance mode
            Artisan::call('down', ['--secret' => 'upgrade-in-progress']);

            // Extract the upgrade package
            $extractPath = $this->tempPath.'/extracted';
            if (! $this->extractZip($zipPath, $extractPath)) {
                $result['message'] = __('Failed to extract upgrade package.');
                $this->restoreFromBackup($backupFile);
                Artisan::call('up');

                return $result;
            }

            // Copy files to the application
            if (! $this->copyUpgradeFiles($extractPath)) {
                $result['message'] = __('Failed to copy upgrade files.');
                $this->restoreFromBackup($backupFile);
                Artisan::call('up');

                return $result;
            }

            // Ensure storage directory structure exists
            $this->ensureStorageDirectoriesExist();

            // Run migrations
            Artisan::call('migrate', ['--force' => true]);

            // Clear caches
            Artisan::call('optimize:clear');

            // Clean up temp files
            File::deleteDirectory($this->tempPath);

            // Clear update info from settings
            $this->clearUpdateInfo();

            // Get the new version
            $newVersion = $this->getCurrentVersion();

            // Bring application back online
            Artisan::call('up');

            $result['success'] = true;
            $result['message'] = __('Successfully upgraded to version :version', ['version' => $newVersion['version']]);

            Log::info('Core upgrade from upload completed successfully', ['version' => $newVersion['version']]);

            return $result;
        } catch (\Exception $e) {
            Log::error('Core upgrade from upload error', [
                'message' => $e->getMessage(),
            ]);

            // Try to restore from backup
            $this->restoreFromBackup($backupFile);

            // Bring application back online
            Artisan::call('up');

            $result['message'] = __('Upgrade failed: :error', ['error' => $e->getMessage()]);

            return $result;
        }
    }

    /**
     * Extract a zip file.
     */
    protected function extractZip(string $zipPath, string $extractPath): bool
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
     * Copy upgrade files to the application.
     */
    protected function copyUpgradeFiles(string $sourcePath): bool
    {
        try {
            // Find the actual source directory (might be nested)
            $directories = File::directories($sourcePath);
            if (count($directories) === 1) {
                $sourcePath = $directories[0];
            }

            // Directories to update (including vendor for production deploys)
            $directoriesToUpdate = [
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
                'vendor',
            ];

            // Also copy module build directories if they exist
            $moduleBuildDirs = $this->getModuleBuildDirectories($sourcePath);
            $directoriesToUpdate = array_merge($directoriesToUpdate, $moduleBuildDirs);

            foreach ($directoriesToUpdate as $dir) {
                $source = $sourcePath.'/'.$dir;
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
            $filesToUpdate = [
                'version.json',
                'composer.json',
                'composer.lock',
                'package.json',
                'package-lock.json',
                'vite.config.js',
                'tailwind.config.js',
                'postcss.config.js',
                'artisan',
                '.htaccess',
                'index.php',
                // Public directory files
                'public/.htaccess',
                'public/index.php',
                'public/favicon.ico',
                'public/robots.txt',
                'public/mix-manifest.json',
            ];

            foreach ($filesToUpdate as $file) {
                $source = $sourcePath.'/'.$file;
                $dest = base_path($file);

                if (File::exists($source)) {
                    File::copy($source, $dest);
                }
            }

            // Copy .env.example if it exists (for fresh setups)
            $envExampleSource = $sourcePath.'/.env.example';
            $envExampleDest = base_path('.env.example');
            if (File::exists($envExampleSource)) {
                File::copy($envExampleSource, $envExampleDest);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to copy upgrade files', [
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
     * Get module build directories from source path.
     *
     * @return array<int, string>
     */
    protected function getModuleBuildDirectories(string $sourcePath): array
    {
        $buildDirs = [];
        $publicPath = $sourcePath.'/public';

        if (File::isDirectory($publicPath)) {
            $directories = File::directories($publicPath);
            foreach ($directories as $dir) {
                $dirName = basename($dir);
                // Match build-* directories
                if (str_starts_with($dirName, 'build-')) {
                    $buildDirs[] = 'public/'.$dirName;
                }
            }
        }

        return $buildDirs;
    }
}
