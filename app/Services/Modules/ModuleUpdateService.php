<?php

declare(strict_types=1);

namespace App\Services\Modules;

use App\Services\LicenseVerificationService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ModuleUpdateService
{
    protected ModuleService $moduleService;

    protected LicenseVerificationService $licenseService;

    protected string $modulesPath;

    public function __construct(ModuleService $moduleService, LicenseVerificationService $licenseService)
    {
        $this->moduleService = $moduleService;
        $this->licenseService = $licenseService;
        $this->modulesPath = config('modules.paths.modules');
    }

    /**
     * Check for available module updates.
     * This method calls the marketplace API and caches the results.
     *
     * @param bool $forceRefresh Force a fresh check, bypassing cache
     * @return array{success: bool, updates: array<string, array>, checked_at: string|null, error: string|null}
     */
    public function checkForUpdates(bool $forceRefresh = false): array
    {
        if (! config('laradashboard.updates.enabled', true)) {
            return [
                'success' => true,
                'updates' => [],
                'checked_at' => null,
                'error' => 'Update checking is disabled.',
            ];
        }

        $cacheKey = config('laradashboard.updates.cache_key');
        $cacheDuration = config('laradashboard.updates.cache_duration', 12);

        // Return cached results if available and not forcing refresh
        if (! $forceRefresh && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $payload = $this->buildUpdateCheckPayload();

            if (empty($payload['modules'])) {
                $result = [
                    'success' => true,
                    'updates' => [],
                    'checked_at' => now()->toIso8601String(),
                    'error' => null,
                ];

                $this->cacheResult($result);

                return $result;
            }

            $response = $this->callUpdateApi($payload);

            if (! $response['success']) {
                return [
                    'success' => false,
                    'updates' => [],
                    'checked_at' => now()->toIso8601String(),
                    'error' => $response['error'] ?? 'Failed to check for updates.',
                ];
            }

            $result = [
                'success' => true,
                'updates' => $response['updates'] ?? [],
                'checked_at' => now()->toIso8601String(),
                'error' => null,
            ];

            $this->cacheResult($result);

            // Update last check timestamp
            Cache::put(
                config('laradashboard.updates.last_check_key'),
                now()->toIso8601String(),
                now()->addHours($cacheDuration)
            );

            return $result;
        } catch (\Throwable $e) {
            Log::error('Module update check failed: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return [
                'success' => false,
                'updates' => [],
                'checked_at' => now()->toIso8601String(),
                'error' => 'Failed to connect to update server.',
            ];
        }
    }

    /**
     * Get cached update information without making an API call.
     *
     * @return array{success: bool, updates: array<string, array>, checked_at: string|null, error: string|null}|null
     */
    public function getCachedUpdates(): ?array
    {
        $cacheKey = config('laradashboard.updates.cache_key');

        return Cache::get($cacheKey);
    }

    /**
     * Get update information for a specific module.
     *
     * @param string $moduleName The module name/slug
     * @return array{has_update: bool, current_version: string|null, latest_version: string|null, download_url: string|null, changelog: string|null}|null
     */
    public function getModuleUpdate(string $moduleName): ?array
    {
        $cached = $this->getCachedUpdates();

        if (! $cached || ! $cached['success']) {
            return null;
        }

        $normalizedName = $this->moduleService->normalizeModuleName($moduleName);
        $updates = $cached['updates'] ?? [];

        if (! isset($updates[$normalizedName])) {
            return [
                'has_update' => false,
                'current_version' => $this->getInstalledModuleVersion($moduleName),
                'latest_version' => null,
                'download_url' => null,
                'changelog' => null,
            ];
        }

        $update = $updates[$normalizedName];

        return [
            'has_update' => true,
            'current_version' => $update['current'] ?? null,
            'latest_version' => $update['latest'] ?? null,
            'download_url' => $update['download_url'] ?? null,
            'changelog' => $update['changelog'] ?? null,
            'required_core' => $update['required_core'] ?? null,
            'required_php' => $update['required_php'] ?? null,
            'module_type' => $update['module_type'] ?? 'free',
            'requires_license' => $update['requires_license'] ?? false,
        ];
    }

    /**
     * Check if any updates are available.
     */
    public function hasAvailableUpdates(): bool
    {
        $cached = $this->getCachedUpdates();

        if (! $cached || ! $cached['success']) {
            return false;
        }

        return ! empty($cached['updates']);
    }

    /**
     * Get count of available updates.
     */
    public function getUpdateCount(): int
    {
        $cached = $this->getCachedUpdates();

        if (! $cached || ! $cached['success']) {
            return 0;
        }

        return count($cached['updates'] ?? []);
    }

    /**
     * Get the last update check timestamp.
     */
    public function getLastCheckTime(): ?string
    {
        return Cache::get(config('laradashboard.updates.last_check_key'));
    }

    /**
     * Check if we should trigger a fallback update check.
     * This is used when cron is not configured.
     */
    public function shouldTriggerFallbackCheck(): bool
    {
        if (! config('laradashboard.updates.enabled', true)) {
            return false;
        }

        $lastCheck = $this->getLastCheckTime();

        if (! $lastCheck) {
            return true;
        }

        $lastCheckTime = \Carbon\Carbon::parse($lastCheck);
        $throttleMinutes = config('laradashboard.updates.fallback_throttle_minutes', 60);

        return $lastCheckTime->addMinutes($throttleMinutes)->isPast();
    }

    /**
     * Clear update cache.
     */
    public function clearCache(): void
    {
        Cache::forget(config('laradashboard.updates.cache_key'));
        Cache::forget(config('laradashboard.updates.last_check_key'));
    }

    /**
     * Build the payload for the update check API.
     *
     * @return array{core: array, modules: array<string, string>, php: string, domain: string}
     */
    protected function buildUpdateCheckPayload(): array
    {
        $modules = [];

        if (File::exists($this->modulesPath)) {
            foreach (File::directories($this->modulesPath) as $moduleDirectory) {
                $moduleJsonPath = $moduleDirectory . '/module.json';

                if (File::exists($moduleJsonPath)) {
                    $moduleData = json_decode(File::get($moduleJsonPath), true);
                    $name = $this->moduleService->normalizeModuleName($moduleData['name'] ?? basename($moduleDirectory));
                    $version = $moduleData['version'] ?? '1.0.0';
                    $modules[$name] = $version;
                }
            }
        }

        return [
            'core' => [
                'version' => $this->getLaraDashboardVersion(),
                'laravel' => app()->version(),
            ],
            'php' => PHP_VERSION,
            'modules' => $modules,
            'domain' => request()->getHost(),
        ];
    }

    /**
     * Get the LaraDashboard core version.
     */
    protected function getLaraDashboardVersion(): string
    {
        // Try to get version from the laradashboard module
        $laradashboardModulePath = $this->modulesPath . '/laradashboard/module.json';

        if (File::exists($laradashboardModulePath)) {
            $data = json_decode(File::get($laradashboardModulePath), true);

            return $data['version'] ?? '1.0.0';
        }

        return config('app.version', '1.0.0');
    }

    /**
     * Get the installed version of a specific module.
     */
    protected function getInstalledModuleVersion(string $moduleName): ?string
    {
        $folderName = $this->moduleService->getActualModuleFolderName($moduleName);

        if (! $folderName) {
            return null;
        }

        $moduleJsonPath = $this->modulesPath . '/' . $folderName . '/module.json';

        if (! File::exists($moduleJsonPath)) {
            return null;
        }

        $data = json_decode(File::get($moduleJsonPath), true);

        return $data['version'] ?? '1.0.0';
    }

    /**
     * Call the update check API.
     *
     * @param array $payload The request payload
     * @return array{success: bool, updates: array|null, error: string|null}
     */
    protected function callUpdateApi(array $payload): array
    {
        $baseUrl = rtrim(config('laradashboard.marketplace.url'), '/');
        $endpoint = config('laradashboard.marketplace.update_check_endpoint');
        $url = $baseUrl . $endpoint;

        // If marketplace URL points to self, query database directly
        // This avoids single-threaded PHP dev server blocking issues
        $appUrl = rtrim(config('app.url'), '/');
        if ($baseUrl === $appUrl) {
            return $this->checkUpdatesFromDatabase($payload);
        }

        try {
            $response = Http::timeout(10)
                ->retry(1, 500)
                ->post($url, $payload);

            if (! $response->successful()) {
                Log::warning('Module update API returned non-success status', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [
                    'success' => false,
                    'updates' => null,
                    'error' => 'Update server returned status: ' . $response->status(),
                ];
            }

            $data = $response->json();

            return [
                'success' => $data['success'] ?? true,
                'updates' => $data['updates'] ?? [],
                'error' => null,
            ];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::warning('Could not connect to module update server', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'updates' => null,
                'error' => 'Could not connect to update server.',
            ];
        }
    }

    /**
     * Check for updates directly from database (development mode).
     * Uses raw queries to avoid module dependencies.
     *
     * @param array $payload The request payload
     * @return array{success: bool, updates: array|null, error: string|null}
     */
    protected function checkUpdatesFromDatabase(array $payload): array
    {
        try {
            // Check if marketplace tables exist
            if (! Schema::hasTable('ld_modules')) {
                return [
                    'success' => true,
                    'updates' => [],
                    'error' => null,
                ];
            }

            $installedModules = $payload['modules'] ?? [];
            $coreVersion = $payload['core']['version'] ?? '1.0.0';
            $phpVersion = $payload['php'] ?? PHP_VERSION;

            if (empty($installedModules)) {
                return [
                    'success' => true,
                    'updates' => [],
                    'error' => null,
                ];
            }

            $moduleSlugs = array_keys($installedModules);

            // Query marketplace modules with their latest approved+released versions
            $modules = DB::table('ld_modules')
                ->whereIn('slug', $moduleSlugs)
                ->whereIn('status', ['published', 'approved'])
                ->get();

            $updates = [];

            foreach ($modules as $module) {
                $installedVersion = $installedModules[$module->slug] ?? null;

                if (! $installedVersion) {
                    continue;
                }

                // Get latest approved and released version
                $latestVersion = DB::table('ld_module_versions')
                    ->where('module_id', $module->id)
                    ->where('status', 'approved')
                    ->whereNotNull('released_at')
                    ->orderByDesc('released_at')
                    ->first();

                if (! $latestVersion) {
                    continue;
                }

                // Check if update is available
                if (version_compare($installedVersion, $latestVersion->version, '<')) {
                    $isPro = $module->module_type !== 'free';

                    // For pro modules, use licensed download endpoint; for free, use direct storage URL
                    $downloadUrl = null;
                    if ($latestVersion->zip_file) {
                        if ($isPro) {
                            // Pro modules require license verification - use API endpoint
                            $downloadUrl = rtrim(config('laradashboard.marketplace.url'), '/') .
                                '/api/modules/' . $module->slug . '/download/' . $latestVersion->version;
                        } else {
                            // Free modules can download directly
                            $downloadUrl = asset('storage/' . $latestVersion->zip_file);
                        }
                    }

                    $updates[$module->slug] = [
                        'current' => $installedVersion,
                        'latest' => $latestVersion->version,
                        'download_url' => $downloadUrl,
                        'changelog' => $latestVersion->changelog ?? $latestVersion->description ?? '',
                        'required_core' => $module->min_laradashboard_required ?? '1.0.0',
                        'required_php' => '8.1',
                        'released_at' => $latestVersion->released_at,
                        'module_type' => $module->module_type,
                        'requires_license' => $isPro,
                    ];
                }
            }

            return [
                'success' => true,
                'updates' => $updates,
                'error' => null,
            ];
        } catch (\Throwable $e) {
            Log::warning('Database update check failed: ' . $e->getMessage());

            return [
                'success' => false,
                'updates' => [],
                'error' => 'Failed to check updates from database.',
            ];
        }
    }

    /**
     * Download ZIP from remote URL or copy from local storage.
     * Handles development mode where marketplace URL points to same server.
     *
     * @param string $downloadUrl The download URL
     * @param string $destinationPath Where to save the file
     * @param string|null $licenseKey License key for pro modules
     * @param string|null $moduleSlug Module slug for license verification
     * @return array{success: bool, message: string}
     */
    protected function downloadOrCopyZip(
        string $downloadUrl,
        string $destinationPath,
        ?string $licenseKey = null,
        ?string $moduleSlug = null
    ): array {
        $baseUrl = rtrim(config('laradashboard.marketplace.url'), '/');
        $appUrl = rtrim(config('app.url'), '/');

        // If marketplace URL points to self, copy file directly from storage
        if ($baseUrl === $appUrl) {
            return $this->copyLocalZip($downloadUrl, $destinationPath, $licenseKey, $moduleSlug);
        }

        // Remote download - include license key in headers for pro modules
        try {
            $request = Http::timeout(120)->sink($destinationPath);

            if ($licenseKey && $moduleSlug) {
                $request = $request->withHeaders([
                    'X-License-Key' => $licenseKey,
                    'X-Module-Slug' => $moduleSlug,
                    'X-Domain' => request()->getHost(),
                ]);
            }

            $response = $request->get($downloadUrl);

            if ($response->status() === 403) {
                return [
                    'success' => false,
                    'message' => 'License verification failed. Please check your license is valid and activated for this domain.',
                ];
            }

            if (! $response->successful()) {
                return [
                    'success' => false,
                    'message' => 'Failed to download update: ' . $response->status(),
                ];
            }

            return [
                'success' => true,
                'message' => 'Downloaded successfully.',
            ];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return [
                'success' => false,
                'message' => 'Could not connect to update server.',
            ];
        }
    }

    /**
     * Copy ZIP file from local storage (development mode).
     *
     * @param string $downloadUrl The asset URL or API URL
     * @param string $destinationPath Where to copy the file
     * @param string|null $licenseKey License key for pro modules
     * @param string|null $moduleSlug Module slug for license verification
     * @return array{success: bool, message: string}
     */
    protected function copyLocalZip(
        string $downloadUrl,
        string $destinationPath,
        ?string $licenseKey = null,
        ?string $moduleSlug = null
    ): array {
        try {
            $parsed = parse_url($downloadUrl);
            $path = $parsed['path'] ?? '';

            // Check if this is an API download URL (pro module) or direct storage URL (free module)
            // API URL format: /api/modules/{slug}/download/{version}
            if (preg_match('#^/api/modules/([^/]+)/download/([^/]+)$#', $path, $matches)) {
                // Pro module - verify license and get ZIP path from database
                $slug = $matches[1];
                $version = $matches[2];

                if (! $licenseKey) {
                    return [
                        'success' => false,
                        'message' => 'License key required for pro module download.',
                    ];
                }

                // Verify license in database
                $verification = $this->verifyLicenseLocally($licenseKey, $slug);
                if (! $verification['valid']) {
                    return [
                        'success' => false,
                        'message' => $verification['message'],
                    ];
                }

                // Get ZIP file path from module version
                $storagePath = $this->getModuleVersionZipPath($slug, $version);
                if (! $storagePath) {
                    return [
                        'success' => false,
                        'message' => 'Module version ZIP file not found.',
                    ];
                }
            } elseif (str_starts_with($path, '/storage/')) {
                // Free module - direct storage URL
                $relativePath = substr($path, 9); // Remove '/storage/'
                $storagePath = storage_path('app/public/' . $relativePath);
            } else {
                return [
                    'success' => false,
                    'message' => 'Invalid download URL format.',
                ];
            }

            if (! File::exists($storagePath)) {
                return [
                    'success' => false,
                    'message' => 'Module ZIP file not found in storage.',
                ];
            }

            File::copy($storagePath, $destinationPath);

            return [
                'success' => true,
                'message' => 'Copied from local storage.',
            ];
        } catch (\Throwable $e) {
            Log::error('Failed to copy local ZIP: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to copy module file: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Verify license locally against the database (development mode).
     *
     * @param string $licenseKey The license key to verify
     * @param string $moduleSlug The module slug
     * @return array{valid: bool, message: string}
     */
    protected function verifyLicenseLocally(string $licenseKey, string $moduleSlug): array
    {
        try {
            if (! Schema::hasTable('ld_module_purchases')) {
                return [
                    'valid' => true,
                    'message' => 'License tables not available, skipping verification.',
                ];
            }

            $domain = request()->getHost();

            // Find the purchase with matching license key and module
            $purchase = DB::table('ld_module_purchases as p')
                ->join('ld_modules as m', 'm.id', '=', 'p.module_id')
                ->where('p.license_key', $licenseKey)
                ->where('m.slug', $moduleSlug)
                ->whereIn('p.status', ['active', 'completed'])
                ->select('p.id', 'p.status', 'p.expires_at')
                ->first();

            if (! $purchase) {
                return [
                    'valid' => false,
                    'message' => 'Invalid license key for this module.',
                ];
            }

            // Check if expired
            if ($purchase->expires_at && now()->isAfter($purchase->expires_at)) {
                return [
                    'valid' => false,
                    'message' => 'License has expired. Please renew to download updates.',
                ];
            }

            // Check if activated on this domain
            $activation = DB::table('ld_license_activations')
                ->where('purchase_id', $purchase->id)
                ->where('domain', $domain)
                ->where('is_active', true)
                ->first();

            if (! $activation) {
                return [
                    'valid' => false,
                    'message' => 'License is not activated on this domain.',
                ];
            }

            return [
                'valid' => true,
                'message' => 'License verified successfully.',
            ];
        } catch (\Throwable $e) {
            Log::warning('Local license verification failed: ' . $e->getMessage());

            return [
                'valid' => true,
                'message' => 'Could not verify license, allowing download.',
            ];
        }
    }

    /**
     * Get the storage path for a module version ZIP file.
     *
     * @param string $moduleSlug The module slug
     * @param string $version The version number
     * @return string|null The full storage path or null if not found
     */
    protected function getModuleVersionZipPath(string $moduleSlug, string $version): ?string
    {
        try {
            $moduleVersion = DB::table('ld_module_versions as v')
                ->join('ld_modules as m', 'm.id', '=', 'v.module_id')
                ->where('m.slug', $moduleSlug)
                ->where('v.version', $version)
                ->where('v.status', 'approved')
                ->select('v.zip_file')
                ->first();

            if (! $moduleVersion || ! $moduleVersion->zip_file) {
                return null;
            }

            return storage_path('app/public/' . $moduleVersion->zip_file);
        } catch (\Throwable $e) {
            Log::error('Failed to get module version ZIP path: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Cache the update check result.
     *
     * @param array $result The result to cache
     */
    protected function cacheResult(array $result): void
    {
        $cacheKey = config('laradashboard.updates.cache_key');
        $cacheDuration = config('laradashboard.updates.cache_duration', 12);

        Cache::put($cacheKey, $result, now()->addHours($cacheDuration));
    }

    /**
     * Download and install a module update.
     *
     * @param string $moduleName The module name to update
     * @return array{success: bool, message: string, requires_license?: bool}
     */
    public function downloadAndInstallUpdate(string $moduleName): array
    {
        $updateInfo = $this->getModuleUpdate($moduleName);

        if (! $updateInfo || ! $updateInfo['has_update']) {
            return [
                'success' => false,
                'message' => 'No update available for this module.',
            ];
        }

        $downloadUrl = $updateInfo['download_url'] ?? null;

        if (! $downloadUrl) {
            return [
                'success' => false,
                'message' => 'Download URL not available.',
            ];
        }

        // Check if module requires license
        $requiresLicense = $updateInfo['requires_license'] ?? false;
        $licenseKey = null;

        if ($requiresLicense) {
            $normalizedSlug = $this->moduleService->normalizeModuleName($moduleName);
            $storedLicense = $this->licenseService->getStoredLicense($normalizedSlug);

            if (! $storedLicense || empty($storedLicense['license_key'])) {
                return [
                    'success' => false,
                    'message' => 'This is a pro module. Please activate your license first to download updates.',
                    'requires_license' => true,
                ];
            }

            $licenseKey = $storedLicense['license_key'];
        }

        try {
            // Download the update ZIP
            $tempPath = storage_path('app/modules_temp/' . uniqid('update_', true));
            File::ensureDirectoryExists($tempPath);

            $zipPath = $tempPath . '/module.zip';

            // Check if download URL is local (same server) - copy directly to avoid blocking
            $downloadResult = $this->downloadOrCopyZip($downloadUrl, $zipPath, $licenseKey, $moduleName);

            if (! $downloadResult['success']) {
                File::deleteDirectory($tempPath);

                return [
                    'success' => false,
                    'message' => $downloadResult['message'],
                ];
            }

            // Replace the existing module
            $folderName = $this->moduleService->getActualModuleFolderName($moduleName);

            if (! $folderName) {
                File::deleteDirectory($tempPath);

                return [
                    'success' => false,
                    'message' => 'Module not found.',
                ];
            }

            // Extract the ZIP
            $zip = new \ZipArchive();

            if (! $zip->open($zipPath)) {
                File::deleteDirectory($tempPath);

                return [
                    'success' => false,
                    'message' => 'Failed to open update package.',
                ];
            }

            $extractPath = $tempPath . '/extracted';
            File::ensureDirectoryExists($extractPath);
            $zip->extractTo($extractPath);
            $zip->close();

            // Use the module service to replace the module
            $newModuleName = $this->moduleService->replaceModule($extractPath, $folderName);

            // Clean up
            File::deleteDirectory($tempPath);

            // Clear update cache so the UI reflects the change
            $this->clearCache();

            return [
                'success' => true,
                'message' => "Module '{$newModuleName}' updated successfully.",
            ];
        } catch (\Throwable $e) {
            Log::error('Module update installation failed: ' . $e->getMessage(), [
                'module' => $moduleName,
                'exception' => $e,
            ]);

            return [
                'success' => false,
                'message' => 'Update installation failed: ' . $e->getMessage(),
            ];
        }
    }
}
