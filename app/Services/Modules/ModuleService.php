<?php

declare(strict_types=1);

namespace App\Services\Modules;

use App\Exceptions\ModuleException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Nwidart\Modules\Facades\Module as ModuleFacade;
use Nwidart\Modules\Module;

class ModuleService
{
    public string $modulesPath;

    public string $modulesStatusesPath;

    public function __construct()
    {
        $this->modulesPath = base_path('Modules');
        $this->modulesStatusesPath = base_path('modules_statuses.json');
    }

    public function findModuleByName(string $moduleName): ?Module
    {
        return ModuleFacade::find(strtolower($moduleName));
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
     * Get all modules from the Modules folder.
     */
    public function getModules(): array
    {
        $modules = [];
        $moduleStatuses = $this->getModuleStatuses();

        if (! File::exists($this->modulesPath)) {
            throw new ModuleException(__('Modules directory does not exist. Please ensure the "Modules" directory is present in the application root.'));
        }

        $moduleDirectories = File::directories($this->modulesPath);

        foreach ($moduleDirectories as $moduleDirectory) {
            $moduleJsonPath = $moduleDirectory.'/module.json';
            if (! File::exists($moduleJsonPath)) {
                throw new ModuleException(__('Invalid module structure. Each module must have a module.json file.'));
            }

            $moduleData = json_decode(File::get($moduleJsonPath), true);
            $moduleName = basename($moduleDirectory);
            $modules[] = [
                'name' => $moduleName,
                'title' => $moduleData['name'] ?? $moduleName,
                'description' => $moduleData['description'] ?? '',
                'icon' => $moduleData['icon'] ?? 'bi-box',
                'status' => $moduleStatuses[$moduleName] ?? false,
                'version' => $moduleData['version'] ?? '1.0.0',
                'tags' => $moduleData['keywords'] ?? [],
            ];
        }

        return $modules;
    }

    public function uploadModule(Request $request)
    {
        $file = $request->file('module');
        $filePath = $file->storeAs('modules', $file->getClientOriginalName());

        // Extract and install the module.
        $modulePath = storage_path('app/'.$filePath);
        $zip = new \ZipArchive();

        if (! $zip->open($modulePath)) {
            throw new ModuleException(__('Module upload failed. The file may not be a valid zip archive.'));
        }

        $moduleName = $zip->getNameIndex(0); // Retrieve the module folder name before closing
        $zip->extractTo($this->modulesPath);
        $zip->close();

        // Check valid module structure.
        $moduleName = str_replace('/', '', $moduleName);
        if (! File::exists($this->modulesPath.'/'.$moduleName.'/module.json')) {
            throw new ModuleException(__('Failed to find the module in the system. Please ensure the module has a valid module.json file.'));
        }

        // Save this module to the modules_statuses.json file.
        $moduleStatuses = $this->getModuleStatuses();
        $moduleStatuses[$moduleName] = true;
        File::put($this->modulesStatusesPath, json_encode($moduleStatuses, JSON_PRETTY_PRINT));

        // Clear the cache.
        Artisan::call('cache:clear');

        return true;
    }

    public function toggleModule($moduleName, $enable = true): bool
    {
        try {
            // Clear the cache.
            Artisan::call('cache:clear');

            // Activate/Deactivate the module.
            $callbackName = $enable ? 'module:enable' : 'module:disable';
            Artisan::call($callbackName, ['module' => $moduleName]);
        } catch (\Throwable $th) {
            Log::error("Failed to toggle module {$moduleName}: ".$th->getMessage());
            throw new ModuleException(__('Failed to toggle module status. Please check the logs for more details.'));
        }

        return true;
    }

    public function toggleModuleStatus(string $moduleName): bool
    {
        $moduleStatuses = $this->getModuleStatuses();

        if (! isset($moduleStatuses[$moduleName]) && ! File::exists($this->modulesPath.'/'.$moduleName)) {
            throw new ModuleException(__('Module not found.'));
        }

        // Just enable it first so that it would be in the getModuleStatuses()
        if (! isset($moduleStatuses[$moduleName])) {
            Artisan::call('module:enable', ['module' => $moduleName]);
            $moduleStatuses = $this->getModuleStatuses();
        }

        // Toggle the status.
        $moduleStatuses[$moduleName] = ! $moduleStatuses[$moduleName];

        // Save the updated statuses.
        File::put($this->modulesStatusesPath, json_encode($moduleStatuses, JSON_PRETTY_PRINT));

        $this->toggleModule($moduleName, ! empty($moduleStatuses[$moduleName]));

        return $moduleStatuses[$moduleName];
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
        $modulePath = base_path('Modules/'.$module->getName());

        if (! is_dir($modulePath)) {
            throw new ModuleException(__('Module directory does not exist. Please ensure the module is installed correctly.'));
        }

        // Delete the module from the database.
        ModuleFacade::delete($module->getName());

        // Clear the cache.
        Artisan::call('cache:clear');
    }
}
