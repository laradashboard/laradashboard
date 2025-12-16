<?php

declare(strict_types=1);

namespace App\Services\Modules;

use App\Exceptions\ModuleConflictException;
use App\Exceptions\ModuleException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Nwidart\Modules\Facades\Module as ModuleFacade;
use Nwidart\Modules\Module;
use App\Models\Module as ModuleModel;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Vite;
use Illuminate\Foundation\Vite as ViteFoundation;

class ModuleService
{
    public string $modulesPath;

    public string $modulesStatusesPath;

    public function __construct()
    {
        $this->modulesPath = config('modules.paths.modules');
        $this->modulesStatusesPath = base_path('modules_statuses.json');
    }

    public function findModuleByName(string $moduleName): ?Module
    {
        return ModuleFacade::find(strtolower($moduleName));
    }

    public function getModuleByName(string $moduleName): ?ModuleModel
    {
        $module = $this->findModuleByName($moduleName);
        if (! $module) {
            return null;
        }

        $moduleData = json_decode(File::get($module->getPath() . '/module.json'), true);
        $moduleStatuses = $this->getModuleStatuses();

        return new ModuleModel([
            'id' => $module->getName(),
            'name' => $module->getName(),
            'title' => $moduleData['name'] ?? $module->getName(),
            'description' => $moduleData['description'] ?? '',
            'icon' => $moduleData['icon'] ?? 'bi-box',
            'status' => $moduleStatuses[$module->getName()] ?? false,
            'version' => $moduleData['version'] ?? '1.0.0',
            'tags' => $moduleData['keywords'] ?? [],
        ]);
    }

    /**
     * Get the module statuses from the modules_statuses.json file.
     */
    public function getModuleStatuses(): array
    {
        if (! File::exists(path: $this->modulesStatusesPath)) {
            return [];
        }

        return json_decode(File::get($this->modulesStatusesPath), true) ?? [];
    }

    /**
     * Clean up orphaned entries from module_statuses.json.
     * Removes entries for modules whose folders have been manually deleted.
     */
    public function cleanupOrphanedModuleStatuses(): void
    {
        $moduleStatuses = $this->getModuleStatuses();
        $statusesModified = false;

        foreach (array_keys($moduleStatuses) as $moduleName) {
            $modulePath = $this->modulesPath . '/' . $moduleName;
            if (! File::exists($modulePath)) {
                unset($moduleStatuses[$moduleName]);
                $statusesModified = true;
                Log::info("Cleaned up orphaned module status entry: {$moduleName}");
            }
        }

        if ($statusesModified) {
            File::put($this->modulesStatusesPath, json_encode($moduleStatuses, JSON_PRETTY_PRINT));
        }
    }

    /**
     * Get all modules from the Modules folder.
     */
    public function getPaginatedModules(int $perPage = 15): LengthAwarePaginator
    {
        $modules = [];
        if (! File::exists($this->modulesPath)) {
            throw new ModuleException(message: __('Modules directory does not exist. Please ensure the "modules" directory is present in the application root.'));
        }

        $moduleDirectories = File::directories($this->modulesPath);

        foreach ($moduleDirectories as $moduleDirectory) {
            $module = $this->getModuleByName(basename($moduleDirectory));
            if ($module) {
                $modules[] = $module;
            }
        }

        // Manually paginate the array.
        $page = request('page', 1);
        $collection = collect($modules);
        $paged = new LengthAwarePaginator(
            $collection->forPage($page, $perPage),
            $collection->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return $paged;
    }

    /**
     * Upload a new module from a zip file.
     *
     * @throws ModuleException If the upload fails
     * @throws ModuleConflictException If a module with the same name already exists
     */
    public function uploadModule(Request $request): string
    {
        // First, clean up orphaned entries from module_statuses.json
        // This handles cases where module folders were manually deleted
        $this->cleanupOrphanedModuleStatuses();

        $file = $request->file('module');
        $filePath = $file->storeAs('modules', $file->getClientOriginalName());

        // Extract and install the module.
        $modulePath = storage_path('app/' . $filePath);
        $zip = new \ZipArchive();

        if (! $zip->open($modulePath)) {
            throw new ModuleException(__('Module upload failed. The file may not be a valid zip archive.'));
        }

        // Extract to a temporary location first to read module.json
        $tempPath = storage_path('app/modules_temp/' . uniqid('module_', true));
        File::ensureDirectoryExists($tempPath);
        $zip->extractTo($tempPath);
        $zip->close();

        // Find the module folder and module.json (handles various zip structures)
        $moduleInfo = $this->findModuleInTempPath($tempPath);

        if (! $moduleInfo) {
            // Clean up the temp files if module.json is missing
            File::deleteDirectory($tempPath);
            throw new ModuleException(__('Failed to find the module in the system. Please ensure the module has a valid module.json file.'));
        }

        $extractedPath = $moduleInfo['path'];
        $folderName = $moduleInfo['folder'];
        $moduleJsonPath = $extractedPath . '/module.json';

        // Get the uploaded module info from module.json
        $uploadedModuleJson = json_decode(File::get($moduleJsonPath), true);
        $moduleName = $uploadedModuleJson['name'] ?? $folderName;

        // Check if a module with this name already exists
        $existingModulePath = $this->modulesPath . '/' . $folderName;
        $moduleStatuses = $this->getModuleStatuses();
        $conflictingModule = null;

        // First check by folder name
        if (File::exists($existingModulePath)) {
            $conflictingModule = $folderName;
        }

        // Also check case-insensitive by module name in statuses
        if (! $conflictingModule) {
            foreach (array_keys($moduleStatuses) as $existingModule) {
                if (strcasecmp($existingModule, $moduleName) === 0 && File::exists($this->modulesPath . '/' . $existingModule)) {
                    $conflictingModule = $existingModule;
                    break;
                }
            }
        }

        // If there's a conflict, throw ModuleConflictException with comparison data
        if ($conflictingModule) {
            $currentModuleInfo = $this->getModuleInfoFromPath($this->modulesPath . '/' . $conflictingModule);
            $uploadedModuleInfo = $this->getModuleInfoFromPath($extractedPath);

            throw new ModuleConflictException(
                __('A module with this name already exists.'),
                $currentModuleInfo,
                $uploadedModuleInfo,
                $tempPath
            );
        }

        // No conflict - proceed with installation
        return $this->installModuleFromTemp($tempPath, $folderName, $moduleName);
    }

    /**
     * Replace an existing module with the uploaded one.
     *
     * @param string $tempPath The temporary path where the uploaded module was extracted
     * @param string $existingModuleName The name of the existing module to replace
     */
    public function replaceModule(string $tempPath, string $existingModuleName): string
    {
        // Find the module folder and module.json
        $moduleInfo = $this->findModuleInTempPath($tempPath);

        if (! $moduleInfo) {
            File::deleteDirectory($tempPath);
            throw new ModuleException(__('Failed to find the module in the system. Please ensure the module has a valid module.json file.'));
        }

        $extractedPath = $moduleInfo['path'];
        $folderName = $moduleInfo['folder'];
        $moduleJsonPath = $extractedPath . '/module.json';

        $uploadedModuleJson = json_decode(File::get($moduleJsonPath), true);
        $moduleName = $uploadedModuleJson['name'] ?? $folderName;

        // Check if module was enabled
        $moduleStatuses = $this->getModuleStatuses();
        $wasEnabled = $moduleStatuses[$existingModuleName] ?? false;

        // Delete the existing module (but preserve the status for re-enabling)
        $existingModulePath = $this->modulesPath . '/' . $existingModuleName;
        if (File::exists($existingModulePath)) {
            // Clean up old assets first
            $this->cleanupModuleAssets($existingModuleName);

            // Disable the module before deletion
            if ($wasEnabled) {
                try {
                    Artisan::call('module:disable', ['module' => $existingModuleName]);
                } catch (\Throwable $e) {
                    Log::warning("Could not disable module before replacement: " . $e->getMessage());
                }
            }

            // Delete the old module files
            File::deleteDirectory($existingModulePath);
        }

        // Remove old status entry if module name changed
        if ($existingModuleName !== $moduleName && isset($moduleStatuses[$existingModuleName])) {
            unset($moduleStatuses[$existingModuleName]);
            File::put($this->modulesStatusesPath, json_encode($moduleStatuses, JSON_PRETTY_PRINT));
        }

        // Install the new module
        $installedModuleName = $this->installModuleFromTemp($tempPath, $folderName, $moduleName);

        // Re-enable if was previously enabled
        if ($wasEnabled) {
            try {
                $this->toggleModule($installedModuleName, true);
            } catch (\Throwable $e) {
                Log::warning("Could not re-enable module after replacement: " . $e->getMessage());
            }
        }

        return $installedModuleName;
    }

    /**
     * Install a module from a temporary extraction path.
     */
    protected function installModuleFromTemp(string $tempPath, string $folderName, string $moduleName): string
    {
        $targetPath = $this->modulesPath . '/' . $folderName;

        // Check if the module is in a subdirectory or at the root of temp path
        $extractedPath = $tempPath . '/' . $folderName;

        if (File::isDirectory($extractedPath) && File::exists($extractedPath . '/module.json')) {
            // Module is in a subdirectory (standard structure)
            File::moveDirectory($extractedPath, $targetPath);
            // Clean up temp directory
            File::deleteDirectory($tempPath);
        } elseif (File::exists($tempPath . '/module.json')) {
            // Module is at root of temp path (zipped from inside module folder)
            // We need to move the entire temp directory content to target
            File::moveDirectory($tempPath, $targetPath);
        } else {
            // Fallback: try the subdirectory approach
            File::moveDirectory($extractedPath, $targetPath);
            File::deleteDirectory($tempPath);
        }

        // Save this module to the modules_statuses.json file as DISABLED.
        // New modules are disabled by default for security - admin must explicitly enable them.
        $moduleStatuses = $this->getModuleStatuses();
        $moduleStatuses[$moduleName] = false;
        File::put($this->modulesStatusesPath, json_encode($moduleStatuses, JSON_PRETTY_PRINT));

        // Publish pre-built assets if the module contains them.
        // This allows modules with pre-compiled CSS/JS to work without npm build.
        if ($this->hasPrebuiltAssets($moduleName)) {
            $this->publishModuleAssets($moduleName, force: true);
            Log::info("Published pre-built assets for module {$moduleName}");
        }

        // Regenerate Composer autoloader so the new module classes can be found.
        // Without this, activating the module will fail with "Class not found" error.
        $this->regenerateAutoloader();

        // Clear the cache.
        Artisan::call('cache:clear');

        return $moduleName;
    }

    /**
     * Get module information from a module path.
     *
     * @return array<string, mixed>
     */
    protected function getModuleInfoFromPath(string $modulePath): array
    {
        $moduleJsonPath = $modulePath . '/module.json';

        if (! File::exists($moduleJsonPath)) {
            return [
                'name' => basename($modulePath),
                'version' => 'Unknown',
                'description' => '',
                'author' => '',
            ];
        }

        $moduleJson = json_decode(File::get($moduleJsonPath), true);

        return [
            'name' => $moduleJson['name'] ?? basename($modulePath),
            'version' => $moduleJson['version'] ?? '1.0.0',
            'description' => $moduleJson['description'] ?? '',
            'author' => $this->extractAuthor($moduleJson),
            'keywords' => $moduleJson['keywords'] ?? [],
            'icon' => $moduleJson['icon'] ?? 'bi-box',
        ];
    }

    /**
     * Extract author name from module.json.
     */
    protected function extractAuthor(array $moduleJson): string
    {
        if (isset($moduleJson['author'])) {
            if (is_string($moduleJson['author'])) {
                return $moduleJson['author'];
            }
            if (is_array($moduleJson['author']) && isset($moduleJson['author']['name'])) {
                return $moduleJson['author']['name'];
            }
        }

        if (isset($moduleJson['authors']) && is_array($moduleJson['authors'])) {
            $firstAuthor = $moduleJson['authors'][0] ?? null;
            if ($firstAuthor && isset($firstAuthor['name'])) {
                return $firstAuthor['name'];
            }
        }

        return '';
    }

    /**
     * Cancel a pending module replacement by cleaning up temp files.
     */
    public function cancelModuleReplacement(string $tempPath): void
    {
        if (File::exists($tempPath) && str_starts_with($tempPath, storage_path('app/modules_temp/'))) {
            File::deleteDirectory($tempPath);
        }
    }

    /**
     * Find module.json in the temp extraction path.
     * Handles various zip structures:
     * - ModuleName/module.json (standard)
     * - module.json at root (zipped from inside module folder)
     * - Nested structures
     *
     * @return array{path: string, folder: string}|null
     */
    protected function findModuleInTempPath(string $tempPath): ?array
    {
        // First, check if module.json is directly in temp path (zipped from inside module)
        if (File::exists($tempPath . '/module.json')) {
            // Read the module name from module.json to determine folder name
            $moduleJson = json_decode(File::get($tempPath . '/module.json'), true);
            $folderName = $moduleJson['name'] ?? basename($tempPath);

            return [
                'path' => $tempPath,
                'folder' => $folderName,
            ];
        }

        // Check subdirectories for module.json
        $directories = File::directories($tempPath);
        foreach ($directories as $directory) {
            if (File::exists($directory . '/module.json')) {
                return [
                    'path' => $directory,
                    'folder' => basename($directory),
                ];
            }
        }

        // Not found
        return null;
    }

    public function toggleModule($moduleName, $enable = true): bool
    {
        try {
            // Reload Composer autoloader to ensure newly uploaded module classes are available.
            // This is critical when activating a module that was just uploaded in a previous request.
            $this->reloadAutoloader();

            // Clear the cache.
            Artisan::call('cache:clear');

            // Activate/Deactivate the module.
            $callbackName = $enable ? 'module:enable' : 'module:disable';
            Artisan::call($callbackName, ['module' => $moduleName]);
        } catch (\Throwable $th) {
            Log::error("Failed to toggle module {$moduleName}: " . $th->getMessage());
            throw new ModuleException(__('Failed to toggle module status. Please check the logs for more details.'));
        }

        return true;
    }

    /**
     * Reload Composer autoloader to pick up newly added module classes.
     */
    protected function reloadAutoloader(): void
    {
        $autoloadFile = base_path('vendor/autoload.php');
        if (File::exists($autoloadFile)) {
            // Get the Composer ClassLoader instance and reload it
            $loader = require $autoloadFile;

            // Re-register the PSR-4 autoload mappings for modules
            $modulesPath = $this->modulesPath;
            if (File::isDirectory($modulesPath)) {
                foreach (File::directories($modulesPath) as $moduleDir) {
                    $moduleName = basename($moduleDir);
                    $namespace = "Modules\\{$moduleName}\\";
                    $loader->addPsr4($namespace, $moduleDir . '/');
                }
            }
        }
    }

    public function toggleModuleStatus(string $moduleName): bool
    {
        $moduleStatuses = $this->getModuleStatuses();

        if (! isset($moduleStatuses[$moduleName]) && ! File::exists($this->modulesPath . '/' . $moduleName)) {
            throw new ModuleException(__('Module not found.'));
        }

        // If module is not in statuses file, add it as disabled first
        // then the toggle will enable it (fixing the double-click issue)
        if (! isset($moduleStatuses[$moduleName])) {
            $moduleStatuses[$moduleName] = false;
        }

        // Toggle the status.
        $moduleStatuses[$moduleName] = ! $moduleStatuses[$moduleName];

        // Save the updated statuses.
        File::put($this->modulesStatusesPath, json_encode($moduleStatuses, JSON_PRETTY_PRINT));

        $this->toggleModule($moduleName, ! empty($moduleStatuses[$moduleName]));

        return $moduleStatuses[$moduleName];
    }

    /**
     * Bulk activate multiple modules.
     *
     * @param  array<string>  $moduleNames
     * @return array<string, bool> Results for each module
     */
    public function bulkActivate(array $moduleNames): array
    {
        $results = [];
        $moduleStatuses = $this->getModuleStatuses();

        foreach ($moduleNames as $moduleName) {
            try {
                if (! File::exists($this->modulesPath . '/' . $moduleName)) {
                    $results[$moduleName] = false;
                    continue;
                }

                $moduleStatuses[$moduleName] = true;
                $this->toggleModule($moduleName, true);
                $results[$moduleName] = true;
            } catch (\Throwable $e) {
                Log::error("Failed to activate module {$moduleName}: " . $e->getMessage());
                $results[$moduleName] = false;
            }
        }

        File::put($this->modulesStatusesPath, json_encode($moduleStatuses, JSON_PRETTY_PRINT));
        Artisan::call('cache:clear');

        return $results;
    }

    /**
     * Bulk deactivate multiple modules.
     *
     * @param  array<string>  $moduleNames
     * @return array<string, bool> Results for each module
     */
    public function bulkDeactivate(array $moduleNames): array
    {
        $results = [];
        $moduleStatuses = $this->getModuleStatuses();

        foreach ($moduleNames as $moduleName) {
            try {
                if (! File::exists($this->modulesPath . '/' . $moduleName)) {
                    $results[$moduleName] = false;
                    continue;
                }

                $moduleStatuses[$moduleName] = false;
                $this->toggleModule($moduleName, false);
                $results[$moduleName] = true;
            } catch (\Throwable $e) {
                Log::error("Failed to deactivate module {$moduleName}: " . $e->getMessage());
                $results[$moduleName] = false;
            }
        }

        File::put($this->modulesStatusesPath, json_encode($moduleStatuses, JSON_PRETTY_PRINT));
        Artisan::call('cache:clear');

        return $results;
    }

    public function deleteModule(string $moduleName): void
    {
        $module = $this->findModuleByName($moduleName);

        if (! $module) {
            throw new ModuleException(__('Module not found.'), Response::HTTP_NOT_FOUND);
        }

        // Disable the module before deletion.
        Artisan::call('module:disable', ['module' => $module->getName()]);

        // Remove the module files using the actual module path.
        $modulePath = $module->getPath();

        if (! is_dir($modulePath)) {
            throw new ModuleException(__('Module directory does not exist. Please ensure the module is installed correctly.'));
        }

        // Clean up published assets from public directory.
        $this->cleanupModuleAssets($module->getName());

        // Delete the module from the database.
        ModuleFacade::delete($module->getName());

        // Clear the cache.
        Artisan::call('cache:clear');
    }

    /**
     * Regenerate Composer autoloader to recognize new module classes.
     * This is necessary after uploading a module via zip file.
     */
    protected function regenerateAutoloader(): void
    {
        try {
            $composerPath = base_path('composer.phar');
            $command = file_exists($composerPath)
                ? ['php', $composerPath, 'dump-autoload', '--no-interaction']
                : ['composer', 'dump-autoload', '--no-interaction'];

            $process = new Process($command, base_path());
            $process->setTimeout(120);
            $process->run();

            if (! $process->isSuccessful()) {
                Log::warning('Failed to regenerate autoloader: ' . $process->getErrorOutput());
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to regenerate autoloader: ' . $e->getMessage());
        }
    }

    public function getModuleAssetPath(): array
    {
        $paths = [];
        if (file_exists('build/manifest.json')) {
            $files = json_decode(file_get_contents('build/manifest.json'), true);
            foreach ($files as $file) {
                $paths[] = $file['src'];
            }
        }

        return $paths;
    }

    /**
     * Support for Vite hot reload overriding manifest file.
     */
    public function moduleViteCompile(string $module, string $asset, ?string $hotFilePath = null, $manifestFile = 'manifest.json'): ViteFoundation
    {
        return Vite::useHotFile($hotFilePath ?: storage_path('vite.hot'))
            ->useBuildDirectory($module)
            ->useManifestFilename($manifestFile)
            ->withEntryPoints([$asset]);
    }

    /**
     * Publish pre-built assets from module's dist directory to public directory.
     * This allows modules with pre-compiled CSS/JS to work without npm build.
     *
     * @param string $moduleName The module name
     * @param bool $force Whether to overwrite existing assets
     * @return bool Whether assets were published
     */
    public function publishModuleAssets(string $moduleName, bool $force = false): bool
    {
        $moduleSlug = \Illuminate\Support\Str::slug($moduleName);
        $sourcePath = $this->modulesPath . '/' . $moduleName . '/dist/build-' . $moduleSlug;
        $targetPath = public_path('build-' . $moduleSlug);

        // Check if module has pre-built assets
        if (! File::isDirectory($sourcePath)) {
            Log::info("Module {$moduleName} has no pre-built assets at {$sourcePath}");
            return false;
        }

        // Check if target already exists
        if (File::isDirectory($targetPath)) {
            if (! $force) {
                Log::info("Assets for module {$moduleName} already exist at {$targetPath}, skipping");
                return true;
            }
            // Remove existing assets
            File::deleteDirectory($targetPath);
        }

        // Copy assets from module dist to public
        try {
            File::copyDirectory($sourcePath, $targetPath);
            Log::info("Published assets for module {$moduleName} from {$sourcePath} to {$targetPath}");
            return true;
        } catch (\Throwable $e) {
            Log::error("Failed to publish assets for module {$moduleName}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if a module has pre-built assets in its dist directory.
     *
     * @param string $moduleName The module name
     * @return bool Whether the module has pre-built assets
     */
    public function hasPrebuiltAssets(string $moduleName): bool
    {
        $moduleSlug = \Illuminate\Support\Str::slug($moduleName);
        $distPath = $this->modulesPath . '/' . $moduleName . '/dist/build-' . $moduleSlug;

        return File::isDirectory($distPath) && File::exists($distPath . '/manifest.json');
    }

    /**
     * Clean up published assets for a module from the public directory.
     *
     * @param string $moduleName The module name
     * @return bool Whether cleanup was successful
     */
    public function cleanupModuleAssets(string $moduleName): bool
    {
        $moduleSlug = \Illuminate\Support\Str::slug($moduleName);
        $targetPath = public_path('build-' . $moduleSlug);

        if (! File::isDirectory($targetPath)) {
            return true; // Nothing to clean up
        }

        try {
            File::deleteDirectory($targetPath);
            Log::info("Cleaned up assets for module {$moduleName} from {$targetPath}");
            return true;
        } catch (\Throwable $e) {
            Log::error("Failed to clean up assets for module {$moduleName}: " . $e->getMessage());
            return false;
        }
    }
}
