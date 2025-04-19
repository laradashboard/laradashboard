<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Nwidart\Modules\Facades\Module as ModuleFacade;

class ModulesController extends Controller
{
    protected $modulesPath;
    protected $modulesStatusesPath;

    public function __construct()
    {
        $this->modulesPath = base_path('Modules');
        $this->modulesStatusesPath = base_path('modules_statuses.json');
    }

    /**
     * Display a list of modules.
     */
    public function index()
    {
        $this->checkAuthorization(auth()->user(), ['module.view']);

        $modules = $this->getModules();
        return view('backend.pages.modules.index', compact('modules'));
    }

    public function upload(Request $request)
    {
        $this->checkAuthorization(auth()->user(), ['module.create']);

        $request->validate([
            'module' => 'required|file|mimes:zip',
        ]);

        $file = $request->file('module');
        $filePath = $file->storeAs('modules', $file->getClientOriginalName());

        // Extract and install the module.
        $modulePath = storage_path('app/' . $filePath);
        $zip = new \ZipArchive;
        if ($zip->open($modulePath) === true) {
            $moduleName = $zip->getNameIndex(0); // Retrieve the module folder name before closing
            $zip->extractTo($this->modulesPath);
            $zip->close();

            // Remove / from moduleName.
            $moduleName = str_replace('/', '', $moduleName);

            // Register the module in the database.
            $module = ModuleFacade::find(strtolower($moduleName));
            if ($module) {
                session()->flash('success', 'Module uploaded and registered successfully.');
            } else {
                session()->flash('error', 'Failed to find the module in the system.');
            }

            // Run the module migrations.
            Artisan::call('module:migrate', ['module' => $moduleName]);

            // Clear the cache
            Artisan::call('cache:clear');

            // Activate the module.
            Artisan::call('module:enable', ['module' => $moduleName]);

            session()->flash('success', 'Module uploaded and installed successfully.');
        } else {
            session()->flash('error', 'Failed to extract the module.');
        }

        return redirect()->route('admin.modules.index');
    }

    /**
     * Toggle the status of a module.
     */
    public function toggleStatus(string $moduleName)
    {
        $moduleStatuses = $this->getModuleStatuses();

        if (!isset($moduleStatuses[$moduleName]) && !File::exists($this->modulesPath . '/' . $moduleName)) {
            return response()->json(['success' => false, 'message' => 'Module not found.'], 404);
        }

        try {
            // Just enable it first so that it would be in the $this->getModuleStatuses()
            if (!isset($moduleStatuses[$moduleName])) {
                Artisan::call('module:enable', ['module' => $moduleName]);
                $moduleStatuses = $this->getModuleStatuses();
            }
        } catch (\Throwable $th) {
            // SKIP.
        }

        // Toggle the status
        $moduleStatuses[$moduleName] = !$moduleStatuses[$moduleName];

        // Save the updated statuses
        File::put($this->modulesStatusesPath, json_encode($moduleStatuses, JSON_PRETTY_PRINT));

        // Enable or disable the module
        if ($moduleStatuses[$moduleName]) {
            Artisan::call('module:enable', ['module' => $moduleName]);
        } else {
            Artisan::call('module:disable', ['module' => $moduleName]);
        }

        // Clear the cache
        Artisan::call('cache:clear');

        return response()->json(['success' => true, 'status' => $moduleStatuses[$moduleName]]);
    }

    /**
     * Get all modules from the Modules folder.
     */
    protected function getModules(): array
    {
        $modules = [];
        $moduleStatuses = $this->getModuleStatuses();

        if (!File::exists($this->modulesPath)) {
            return $modules;
        }

        $moduleDirectories = File::directories($this->modulesPath);

        foreach ($moduleDirectories as $moduleDirectory) {
            $moduleJsonPath = $moduleDirectory . '/module.json';

            if (File::exists($moduleJsonPath)) {
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
        }

        return $modules;
    }

    /**
     * Get the module statuses from the modules_statuses.json file.
     */
    protected function getModuleStatuses(): array
    {
        if (!File::exists($this->modulesStatusesPath)) {
            return [];
        }

        return json_decode(File::get($this->modulesStatusesPath), true) ?? [];
    }

    public function destroy(string $module)
    {
        $this->checkAuthorization(auth()->user(), ['module.delete']);

        // Find the module in the system.
        $moduleData = ModuleFacade::find(strtolower($module));

        if ($moduleData) {
            // Disable the module before deletion.
            Artisan::call('module:disable', ['module' => $moduleData->getName()]);

            // Remove the module files.
            $modulePath = base_path('Modules/' . $moduleData->getName());
            if (is_dir($modulePath)) {
                \File::deleteDirectory($modulePath);
            }
        }

        // Delete the module from the database.
        ModuleFacade::delete($moduleData->getName());

        // Clear the cache.
        Artisan::call('cache:clear');

        session()->flash('success', 'Module deleted successfully.');

        return redirect()->route('admin.modules.index');
    }
}