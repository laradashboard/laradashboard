<?php

declare(strict_types=1);

namespace App\Services\Modules;

use App\Exceptions\ModuleConflictException;
use App\Exceptions\ModuleException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class MarketplaceService
{
    protected ModuleService $moduleService;

    protected string $baseUrl;

    protected string $modulesEndpoint;

    public function __construct(ModuleService $moduleService)
    {
        $this->moduleService = $moduleService;
        $this->baseUrl = rtrim(config('laradashboard.marketplace.url', 'https://laradashboard.com'), '/');
        $this->modulesEndpoint = config('laradashboard.marketplace.modules_endpoint', '/api/modules');
    }

    /**
     * Fetch modules from the marketplace API.
     *
     * @return array{success: bool, data: array, meta: array, error: string|null}
     */
    public function fetchModules(string $search = '', string $type = '', int $page = 1, int $perPage = 12): array
    {
        $cacheKey = 'marketplace:modules:' . md5("{$search}|{$type}|{$page}|{$perPage}");
        $cacheDuration = (int) config('laradashboard.marketplace.modules_cache_duration', 5);

        return Cache::remember($cacheKey, now()->addMinutes($cacheDuration), function () use ($search, $type, $page, $perPage) {
            return $this->callModulesApi($search, $type, $page, $perPage);
        });
    }

    /**
     * Call the marketplace modules API.
     *
     * @return array{success: bool, data: array, meta: array, error: string|null}
     */
    protected function callModulesApi(string $search, string $type, int $page, int $perPage): array
    {
        $url = $this->baseUrl . $this->modulesEndpoint;

        // If marketplace URL points to self, query database directly
        $appUrl = rtrim(config('app.url'), '/');
        if ($this->baseUrl === $appUrl) {
            return $this->fetchModulesFromDatabase($search, $type, $page, $perPage);
        }

        try {
            $query = array_filter([
                'search' => $search,
                'type' => $type,
                'page' => $page,
                'per_page' => $perPage,
            ]);

            $response = Http::timeout(15)
                ->retry(1, 500)
                ->get($url, $query);

            if (! $response->successful()) {
                Log::warning('Marketplace API returned non-success status', [
                    'status' => $response->status(),
                ]);

                return [
                    'success' => false,
                    'data' => [],
                    'meta' => [],
                    'error' => __('Marketplace returned status: :status', ['status' => $response->status()]),
                ];
            }

            $json = $response->json();

            return [
                'success' => $json['success'] ?? true,
                'data' => $json['data'] ?? [],
                'meta' => $json['meta'] ?? [],
                'error' => null,
            ];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::warning('Could not connect to marketplace', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => [],
                'meta' => [],
                'error' => __('Could not connect to the marketplace. Please try again later.'),
            ];
        } catch (\Throwable $e) {
            Log::error('Marketplace API error: ' . $e->getMessage());

            return [
                'success' => false,
                'data' => [],
                'meta' => [],
                'error' => __('An error occurred while fetching marketplace modules.'),
            ];
        }
    }

    /**
     * Fetch modules from the local database (development mode).
     *
     * @return array{success: bool, data: array, meta: array, error: string|null}
     */
    protected function fetchModulesFromDatabase(string $search, string $type, int $page, int $perPage): array
    {
        try {
            if (! \Illuminate\Support\Facades\Schema::hasTable('ld_modules')) {
                return [
                    'success' => true,
                    'data' => [],
                    'meta' => ['current_page' => 1, 'last_page' => 1, 'per_page' => $perPage, 'total' => 0],
                    'error' => null,
                ];
            }

            $query = \Illuminate\Support\Facades\DB::table('ld_modules')
                ->where('status', 'approved');

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            if ($type) {
                $query->where('module_type', $type);
            }

            $total = $query->count();
            $modules = $query->orderByDesc('created_at')
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get()
                ->toArray();

            return [
                'success' => true,
                'data' => array_map(fn ($m) => (array) $m, $modules),
                'meta' => [
                    'current_page' => $page,
                    'last_page' => max(1, (int) ceil($total / $perPage)),
                    'per_page' => $perPage,
                    'total' => $total,
                ],
                'error' => null,
            ];
        } catch (\Throwable $e) {
            Log::warning('Database marketplace query failed: ' . $e->getMessage());

            return [
                'success' => false,
                'data' => [],
                'meta' => [],
                'error' => __('Failed to fetch modules from database.'),
            ];
        }
    }

    /**
     * Download and install a module from the marketplace.
     *
     * @return array{success: bool, message: string, module_name: string|null}
     */
    public function downloadAndInstall(string $slug, string $version): array
    {
        try {
            $downloadUrl = $this->baseUrl . "/api/modules/{$slug}/download/{$version}";

            // Download the ZIP to a temp path
            $tempPath = storage_path('app/modules_temp/' . uniqid('marketplace_', true));
            File::ensureDirectoryExists($tempPath);
            $zipPath = $tempPath . '/module.zip';

            $downloadResult = $this->downloadZip($downloadUrl, $zipPath);

            if (! $downloadResult['success']) {
                File::deleteDirectory($tempPath);

                return [
                    'success' => false,
                    'message' => $downloadResult['message'],
                    'module_name' => null,
                ];
            }

            // Extract the ZIP
            $zip = new \ZipArchive();

            if (! $zip->open($zipPath)) {
                File::deleteDirectory($tempPath);

                return [
                    'success' => false,
                    'message' => __('Failed to open the downloaded module package.'),
                    'module_name' => null,
                ];
            }

            $extractPath = $tempPath . '/extracted';
            File::ensureDirectoryExists($extractPath);
            $zip->extractTo($extractPath);
            $zip->close();

            // Check if the module already exists locally
            $moduleInfo = $this->findModuleInExtracted($extractPath);

            if (! $moduleInfo) {
                File::deleteDirectory($tempPath);

                return [
                    'success' => false,
                    'message' => __('Invalid module package. No module.json found.'),
                    'module_name' => null,
                ];
            }

            $moduleName = $moduleInfo['name'];
            $existingFolder = $this->moduleService->getActualModuleFolderName($moduleName);

            if ($existingFolder) {
                // Module already exists - replace it
                $newModuleName = $this->moduleService->replaceModule($extractPath, $existingFolder);
            } else {
                // New module - install fresh
                $newModuleName = $this->installFromExtracted($extractPath, $moduleInfo);
            }

            // Clean up
            if (File::isDirectory($tempPath)) {
                File::deleteDirectory($tempPath);
            }

            return [
                'success' => true,
                'message' => __('Module ":name" installed successfully.', ['name' => $newModuleName]),
                'module_name' => $newModuleName,
                'original_name' => $moduleName,
            ];
        } catch (ModuleConflictException $e) {
            return [
                'success' => false,
                'message' => __('A module with this name already exists.'),
                'module_name' => null,
            ];
        } catch (ModuleException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'module_name' => null,
            ];
        } catch (\Throwable $e) {
            Log::error('Marketplace module installation failed: ' . $e->getMessage(), [
                'slug' => $slug,
                'version' => $version,
                'exception' => $e,
            ]);

            return [
                'success' => false,
                'message' => __('Installation failed: :error', ['error' => $e->getMessage()]),
                'module_name' => null,
            ];
        }
    }

    /**
     * Download a ZIP file from the marketplace.
     *
     * @return array{success: bool, message: string}
     */
    protected function downloadZip(string $url, string $destinationPath): array
    {
        $appUrl = rtrim(config('app.url'), '/');

        // If marketplace URL points to self, copy directly from storage
        if ($this->baseUrl === $appUrl) {
            return $this->copyLocalZip($url, $destinationPath);
        }

        try {
            $response = Http::timeout(120)->sink($destinationPath)->get($url);

            if ($response->status() === 403) {
                return [
                    'success' => false,
                    'message' => __('This module requires a license. Please purchase it on the marketplace first.'),
                ];
            }

            if ($response->status() === 404) {
                return [
                    'success' => false,
                    'message' => __('Module version not found on the marketplace.'),
                ];
            }

            if (! $response->successful()) {
                return [
                    'success' => false,
                    'message' => __('Failed to download module: HTTP :status', ['status' => $response->status()]),
                ];
            }

            return [
                'success' => true,
                'message' => __('Downloaded successfully.'),
            ];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return [
                'success' => false,
                'message' => __('Could not connect to the marketplace server.'),
            ];
        }
    }

    /**
     * Copy ZIP from local storage (development mode).
     *
     * @return array{success: bool, message: string}
     */
    protected function copyLocalZip(string $downloadUrl, string $destinationPath): array
    {
        try {
            $parsed = parse_url($downloadUrl);
            $path = $parsed['path'] ?? '';

            if (preg_match('#^/api/modules/([^/]+)/download/([^/]+)$#', $path, $matches)) {
                $slug = $matches[1];
                $version = $matches[2];

                if (\Illuminate\Support\Facades\Schema::hasTable('ld_module_versions')) {
                    $moduleVersion = \Illuminate\Support\Facades\DB::table('ld_module_versions as v')
                        ->join('ld_modules as m', 'm.id', '=', 'v.module_id')
                        ->where('m.slug', $slug)
                        ->where('v.version', $version)
                        ->whereNotNull('v.zip_file')
                        ->select('v.zip_file')
                        ->first();

                    if ($moduleVersion && \Illuminate\Support\Facades\Storage::exists($moduleVersion->zip_file)) {
                        $sourcePath = \Illuminate\Support\Facades\Storage::path($moduleVersion->zip_file);
                        File::copy($sourcePath, $destinationPath);

                        return [
                            'success' => true,
                            'message' => __('Copied from local storage.'),
                        ];
                    }
                }
            }

            return [
                'success' => false,
                'message' => __('Module ZIP file not found in local storage.'),
            ];
        } catch (\Throwable $e) {
            Log::error('Failed to copy local ZIP: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => __('Failed to copy module file.'),
            ];
        }
    }

    /**
     * Find module info in extracted ZIP path.
     *
     * @return array{name: string, folder: string, path: string}|null
     */
    protected function findModuleInExtracted(string $extractPath): ?array
    {
        // Check if module.json is directly in extract path
        if (File::exists($extractPath . '/module.json')) {
            $data = json_decode(File::get($extractPath . '/module.json'), true);

            return [
                'name' => $data['name'] ?? basename($extractPath),
                'folder' => $this->extractFolderName($extractPath, $data),
                'path' => $extractPath,
            ];
        }

        // Check subdirectories
        foreach (File::directories($extractPath) as $directory) {
            if (File::exists($directory . '/module.json')) {
                $data = json_decode(File::get($directory . '/module.json'), true);

                return [
                    'name' => $data['name'] ?? basename($directory),
                    'folder' => basename($directory),
                    'path' => $directory,
                ];
            }
        }

        return null;
    }

    /**
     * Extract the PascalCase folder name from module data.
     */
    protected function extractFolderName(string $modulePath, array $moduleJson): string
    {
        // Try to extract from providers
        $providers = $moduleJson['providers'] ?? [];
        foreach ($providers as $provider) {
            if (preg_match('/^Modules\\\\([^\\\\]+)\\\\/', $provider, $matches)) {
                return $matches[1];
            }
        }

        // Fallback to StudlyCase of name
        return \Illuminate\Support\Str::studly($moduleJson['name'] ?? basename($modulePath));
    }

    /**
     * Install a module from extracted path (new module, not replacing).
     */
    protected function installFromExtracted(string $extractPath, array $moduleInfo): string
    {
        $folderName = $moduleInfo['folder'];
        $moduleName = $moduleInfo['name'];
        $targetPath = $this->moduleService->modulesPath . '/' . $folderName;

        if ($moduleInfo['path'] === $extractPath) {
            // Module is at root of extract path
            File::moveDirectory($extractPath, $targetPath);
        } else {
            // Module is in a subdirectory
            File::moveDirectory($moduleInfo['path'], $targetPath);
            File::deleteDirectory($extractPath);
        }

        // Set module as disabled by default
        $this->moduleService->setModuleStatus($moduleName, false);

        $normalizedName = $this->moduleService->normalizeModuleName($moduleName);
        $moduleSlug = \Illuminate\Support\Str::slug($folderName);

        // Publish assets if pre-built
        $buildPath = $targetPath . '/public/build-' . $moduleSlug;
        if (File::isDirectory($buildPath)) {
            $this->moduleService->publishModuleAssetsFromPath($targetPath, $moduleSlug, force: true);
        }

        // Publish module images
        $this->moduleService->publishModuleImagesFromPath($targetPath, $normalizedName);

        // Regenerate Composer autoloader so the new module classes can be found
        $this->regenerateAutoloader();

        // Clear cache
        \Illuminate\Support\Facades\Artisan::call('cache:clear');

        Log::info("Marketplace module installed: {$moduleName} (folder: {$folderName})");

        return $normalizedName;
    }

    /**
     * Regenerate Composer autoloader so new module classes can be found.
     */
    protected function regenerateAutoloader(): void
    {
        $env = array_merge($_ENV, $_SERVER, [
            'HOME' => getenv('HOME') ?: base_path(),
            'COMPOSER_HOME' => getenv('COMPOSER_HOME') ?: base_path('.composer'),
        ]);

        try {
            $composerPath = base_path('composer.phar');
            $command = file_exists($composerPath)
                ? ['php', $composerPath, 'dump-autoload', '--no-interaction']
                : ['composer', 'dump-autoload', '--no-interaction'];

            $process = new Process($command, base_path());
            $process->setTimeout(120);
            $process->setEnv($env);
            $process->run();

            if (! $process->isSuccessful()) {
                Log::warning('Failed to regenerate autoloader: ' . $process->getErrorOutput());
            }
        } catch (\Throwable $e) {
            Log::warning('Autoloader regeneration failed: ' . $e->getMessage());
        }
    }

    /**
     * Get the list of locally installed module slugs (normalized/lowercase).
     *
     * @return array<string>
     */
    public function getInstalledModuleSlugs(): array
    {
        $modulesPath = config('modules.paths.modules');

        if (! File::exists($modulesPath)) {
            return [];
        }

        $slugs = [];

        foreach (File::directories($modulesPath) as $dir) {
            $moduleJsonPath = $dir . '/module.json';

            if (File::exists($moduleJsonPath)) {
                $data = json_decode(File::get($moduleJsonPath), true);
                $slugs[] = strtolower($data['name'] ?? basename($dir));
            } else {
                $slugs[] = strtolower(basename($dir));
            }
        }

        return $slugs;
    }
}
