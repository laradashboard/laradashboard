<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class CoreUpgradeService
{
    protected string $versionFile;

    protected string $backupPath;

    protected string $tempPath;

    public function __construct()
    {
        $this->versionFile = base_path('version.json');
        $this->backupPath = storage_path('app/core-backups');
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
     */
    public function createBackup(): ?string
    {
        try {
            // Create backup directory
            if (! File::exists($this->backupPath)) {
                File::makeDirectory($this->backupPath, 0755, true);
            }

            $currentVersion = $this->getCurrentVersion()['version'];
            $timestamp = now()->format('Y-m-d_His');
            $backupFile = $this->backupPath."/backup-{$currentVersion}-{$timestamp}.zip";

            $zip = new ZipArchive();
            if ($zip->open($backupFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                Log::error('Could not create backup zip file');

                return null;
            }

            // List of directories and files to backup
            $itemsToBackup = [
                'app',
                'config',
                'database/migrations',
                'public/css',
                'public/js',
                'resources/views',
                'routes',
                'version.json',
                'composer.json',
            ];

            foreach ($itemsToBackup as $item) {
                $path = base_path($item);
                if (File::isDirectory($path)) {
                    $this->addDirectoryToZip($zip, $path, $item);
                } elseif (File::exists($path)) {
                    $zip->addFile($path, $item);
                }
            }

            $zip->close();

            Log::info('Backup created successfully', ['path' => $backupFile]);

            return $backupFile;
        } catch (\Exception $e) {
            Log::error('Backup creation error', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Add a directory to zip recursively.
     */
    protected function addDirectoryToZip(ZipArchive $zip, string $path, string $relativePath): void
    {
        $files = File::allFiles($path);

        foreach ($files as $file) {
            $filePath = $file->getRealPath();
            $zipPath = $relativePath.'/'.str_replace($path.'/', '', $filePath);
            $zip->addFile($filePath, $zipPath);
        }
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

            // Directories to update
            $directoriesToUpdate = [
                'app',
                'config',
                'database/migrations',
                'public/css',
                'public/js',
                'resources/views',
                'routes',
            ];

            foreach ($directoriesToUpdate as $dir) {
                $source = $sourcePath.'/'.$dir;
                $dest = base_path($dir);

                if (File::isDirectory($source)) {
                    File::copyDirectory($source, $dest);
                }
            }

            // Copy individual files
            $filesToUpdate = [
                'version.json',
                'composer.json',
            ];

            foreach ($filesToUpdate as $file) {
                $source = $sourcePath.'/'.$file;
                $dest = base_path($file);

                if (File::exists($source)) {
                    File::copy($source, $dest);
                }
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
     * Restore from backup.
     */
    public function restoreFromBackup(?string $backupFile): bool
    {
        if (! $backupFile || ! File::exists($backupFile)) {
            Log::warning('No backup file to restore from');

            return false;
        }

        try {
            $extractPath = $this->tempPath.'/restore';
            if (! $this->extractZip($backupFile, $extractPath)) {
                return false;
            }

            // Restore files
            $this->copyUpgradeFiles($extractPath);

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
    protected function formatFileSize(int $size): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return round($size, 2).' '.$units[$unitIndex];
    }

    /**
     * Delete a backup file.
     */
    public function deleteBackup(string $filename): bool
    {
        $path = $this->backupPath.'/'.$filename;

        if (File::exists($path)) {
            return File::delete($path);
        }

        return false;
    }
}
