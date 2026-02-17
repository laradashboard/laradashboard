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
        return rtrim(config('laradashboard.marketplace.url', 'https://laradashboard.com'), '/');
    }

    /**
     * Check for available updates from the marketplace.
     */
    public function checkForUpdates(): ?array
    {
        try {
            $currentVersion = $this->getCurrentVersion();
            $marketplaceUrl = $this->getMarketplaceUrl();
            $endpoint = $marketplaceUrl.'/api/core/check-updates';

            Log::info('Checking for core updates', [
                'current_version' => $currentVersion['version'],
                'marketplace_url' => $marketplaceUrl,
                'endpoint' => $endpoint,
            ]);

            $response = Http::timeout(30)->post($endpoint, [
                'current_version' => $currentVersion['version'],
            ]);

            if (! $response->successful()) {
                Log::warning('Core upgrade check failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'endpoint' => $endpoint,
                ]);

                return null;
            }

            $data = $response->json();

            Log::info('Core upgrade check response', [
                'success' => $data['success'] ?? false,
                'has_update' => $data['has_update'] ?? false,
                'latest_version' => $data['latest_version'] ?? null,
                'current_version' => $currentVersion['version'],
            ]);

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
                'trace' => $e->getTraceAsString(),
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

            $zipPath = $this->tempPath."/laradashboard-{$version}.zip";

            // Check if we're on the marketplace itself (laradashboard module is installed)
            // In that case, try to get the file directly from local storage
            if ($this->tryLocalDownload($version, $zipPath)) {
                return $zipPath;
            }

            // Fall back to HTTP download
            $downloadUrl = $this->getMarketplaceUrl()."/api/core/download/{$version}";

            Log::info('Downloading core upgrade via HTTP', [
                'version' => $version,
                'url' => $downloadUrl,
            ]);

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
     * Try to get the upgrade file from local storage (when on the marketplace itself).
     */
    protected function tryLocalDownload(string $version, string $destinationPath): bool
    {
        try {
            // Check if the LaraDashboard module is installed (we're on the marketplace)
            if (! class_exists(\Modules\LaraDashboard\Models\CoreUpgrade::class)) {
                return false;
            }

            // Try to find the upgrade in the local database
            $upgrade = \Modules\LaraDashboard\Models\CoreUpgrade::where('version', $version)
                ->where('status', 'published')
                ->first();

            if (! $upgrade || ! $upgrade->zip_file) {
                return false;
            }

            // Check if the file exists in public storage
            if (! \Illuminate\Support\Facades\Storage::disk('public')->exists($upgrade->zip_file)) {
                Log::warning('Local core upgrade file not found in storage', [
                    'version' => $version,
                    'zip_file' => $upgrade->zip_file,
                ]);

                return false;
            }

            // Copy the file to the destination
            $sourcePath = \Illuminate\Support\Facades\Storage::disk('public')->path($upgrade->zip_file);
            File::copy($sourcePath, $destinationPath);

            Log::info('Core upgrade file copied from local storage', [
                'version' => $version,
                'source' => $sourcePath,
                'destination' => $destinationPath,
            ]);

            return File::exists($destinationPath) && File::size($destinationPath) > 0;
        } catch (\Exception $e) {
            Log::warning('Failed to get local core upgrade file', [
                'version' => $version,
                'error' => $e->getMessage(),
            ]);

            return false;
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

            // Run ONLY core migrations (not module migrations)
            // Module migrations should be handled by module installation/upgrade
            Artisan::call('migrate', [
                '--force' => true,
                '--path' => 'database/migrations',
            ]);

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

            // Run ONLY core migrations (not module migrations)
            // Module migrations should be handled by module installation/upgrade
            Artisan::call('migrate', [
                '--force' => true,
                '--path' => 'database/migrations',
            ]);

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
