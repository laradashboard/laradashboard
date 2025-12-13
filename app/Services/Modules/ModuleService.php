<?php

declare(strict_types=1);

namespace App\Services\Modules;

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

    public function uploadModule(Request $request)
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

        $folderName = $zip->getNameIndex(0); // Retrieve the module folder name before closing.

        // Check valid module structure.
        $folderName = str_replace('/', '', $folderName);

        // Security: Prevent overwriting existing modules to avoid malicious code injection.
        // An attacker could upload a module with the same name as an already-enabled module
        // (e.g., Crm, TaskManager, Site) to immediately execute malicious code.
        if (File::exists($this->modulesPath . '/' . $folderName)) {
            $zip->close();
            throw new ModuleException(__('A module with this name already exists. Please delete the existing module first or use a different name.'));
        }

        // Extract the module.
        $zip->extractTo($this->modulesPath);
        $zip->close();

        $moduleJsonPath = $this->modulesPath . '/' . $folderName . '/module.json';
        if (! File::exists($moduleJsonPath)) {
            // Clean up the extracted files if module.json is missing
            File::deleteDirectory($this->modulesPath . '/' . $folderName);
            throw new ModuleException(__('Failed to find the module in the system. Please ensure the module has a valid module.json file.'));
        }

        // Get the actual module name from module.json (this is what nwidart/laravel-modules uses).
        $moduleJson = json_decode(File::get($moduleJsonPath), true);
        $moduleName = $moduleJson['name'] ?? $folderName;

        // Security: Check if a module with this name already exists (case-insensitive).
        $moduleStatuses = $this->getModuleStatuses();
        foreach (array_keys($moduleStatuses) as $existingModule) {
            if (strcasecmp($existingModule, $moduleName) === 0 && File::exists($this->modulesPath . '/' . $existingModule)) {
                // Clean up the extracted files
                File::deleteDirectory($this->modulesPath . '/' . $folderName);
                throw new ModuleException(__('A module with this name already exists. Please delete the existing module first or use a different name.'));
            }
        }

        // Save this module to the modules_statuses.json file as DISABLED.
        // New modules are disabled by default for security - admin must explicitly enable them.
        $moduleStatuses[$moduleName] = false;
        File::put($this->modulesStatusesPath, json_encode($moduleStatuses, JSON_PRETTY_PRINT));

        // Regenerate Composer autoloader so the new module classes can be found.
        // Without this, activating the module will fail with "Class not found" error.
        $this->regenerateAutoloader();

        // Clear the cache.
        Artisan::call('cache:clear');

        // Return the module name for use in responses
        return $moduleName;
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

        // Remove the module files.
        $modulePath = base_path('modules/' . $module->getName());

        if (! is_dir($modulePath)) {
            throw new ModuleException(__('Module directory does not exist. Please ensure the module is installed correctly.'));
        }

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
    public function moduleViteCompile(string $module, string $asset, ?string $hotFilePath = null, $manifestFile = '.vite/manifest.json'): ViteFoundation
    {
        return Vite::useHotFile($hotFilePath ?: storage_path('vite.hot'))
            ->useBuildDirectory($module)
            ->useManifestFilename($manifestFile)
            ->withEntryPoints([$asset]);
    }
}
