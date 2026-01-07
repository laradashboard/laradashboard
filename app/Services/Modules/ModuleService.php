<?php

declare(strict_types=1);

namespace App\Services\Modules;

use App\Enums\Hooks\ModuleActionHook;
use App\Exceptions\ModuleConflictException;
use App\Exceptions\ModuleException;
use App\Support\Facades\Hook;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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

    /**
     * Normalize module name to lowercase for comparison purposes.
     */
    public function normalizeModuleName(string $moduleName): string
    {
        return strtolower(trim($moduleName));
    }

    /**
     * Get the actual module folder name from disk (case-sensitive).
     * Returns null if module folder doesn't exist.
     */
    public function getActualModuleFolderName(string $moduleName): ?string
    {
        $normalizedSearch = $this->normalizeModuleName($moduleName);

        if (! File::exists($this->modulesPath)) {
            return null;
        }

        foreach (File::directories($this->modulesPath) as $directory) {
            $folderName = basename($directory);
            if ($this->normalizeModuleName($folderName) === $normalizedSearch) {
                return $folderName;
            }
        }

        return null;
    }

    /**
     * Get the module name as defined in module.json.
     * This is the canonical lowercase name used for status tracking.
     */
    public function getModuleJsonName(string $moduleName): ?string
    {
        $folderName = $this->getActualModuleFolderName($moduleName);
        if (! $folderName) {
            return null;
        }

        $moduleJsonPath = $this->modulesPath . '/' . $folderName . '/module.json';
        if (! File::exists($moduleJsonPath)) {
            return $this->normalizeModuleName($folderName); // Fallback to lowercase folder name
        }

        $moduleData = json_decode(File::get($moduleJsonPath), true);

        // name should be lowercase, but normalize just in case
        return $this->normalizeModuleName($moduleData['name'] ?? $folderName);
    }

    /**
     * Get the module title for display purposes.
     */
    public function getModuleTitle(string $moduleName): ?string
    {
        $folderName = $this->getActualModuleFolderName($moduleName);
        if (! $folderName) {
            return null;
        }

        $moduleJsonPath = $this->modulesPath . '/' . $folderName . '/module.json';
        if (! File::exists($moduleJsonPath)) {
            return ucfirst($folderName);
        }

        $moduleData = json_decode(File::get($moduleJsonPath), true);

        return $moduleData['title'] ?? $moduleData['name'] ?? ucfirst($folderName);
    }

    public function findModuleByName(string $moduleName): ?Module
    {
        // Use actual folder name for lookup
        $actualName = $this->getActualModuleFolderName($moduleName);
        if (! $actualName) {
            return null;
        }

        return ModuleFacade::find($actualName);
    }

    public function getModuleByName(string $moduleName): ?ModuleModel
    {
        $module = $this->findModuleByName($moduleName);
        if (! $module) {
            return null;
        }

        $moduleData = json_decode(File::get($module->getPath() . '/module.json'), true);
        $moduleStatuses = $this->getModuleStatuses();

        // Use lowercase name from module.json for status lookup
        $jsonName = $this->normalizeModuleName($moduleData['name'] ?? $module->getName());

        // Read description from description.md file if it exists
        $description = $this->getModuleDescriptionFromFile($module->getPath());

        return new ModuleModel([
            'id' => $jsonName,
            'name' => $jsonName,
            'title' => $moduleData['title'] ?? $moduleData['name'] ?? $module->getName(),
            'description' => $description,
            'icon' => $moduleData['icon'] ?? 'lucide:box',
            'logo_image' => $moduleData['logo_image'] ?? null,
            'banner_image' => $moduleData['banner_image'] ?? null,
            'status' => $moduleStatuses[$jsonName] ?? false,
            'version' => $moduleData['version'] ?? '1.0.0',
            'author' => $moduleData['author'] ?? null,
            'author_url' => $moduleData['author_url'] ?? null,
            'documentation_url' => $moduleData['documentation_url'] ?? null,
            'tags' => $moduleData['keywords'] ?? [],
            'category' => $moduleData['category'] ?? null,
            'priority' => $moduleData['priority'] ?? 0,
        ]);
    }

    /**
     * Get module description from description.md file.
     */
    protected function getModuleDescriptionFromFile(string $modulePath): string
    {
        $descriptionFile = $modulePath . '/description.md';

        if (! File::exists($descriptionFile)) {
            return '';
        }

        $markdown = File::get($descriptionFile);

        return Str::markdown($markdown);
    }

    /**
     * Get the module statuses from the modules_statuses.json file.
     * Keys are mapped to module.json names (what nwidart uses).
     */
    public function getModuleStatuses(): array
    {
        if (! File::exists(path: $this->modulesStatusesPath)) {
            return [];
        }

        $statuses = json_decode(File::get($this->modulesStatusesPath), true) ?? [];

        // Map to module.json names and merge duplicates
        $normalized = [];
        foreach ($statuses as $name => $status) {
            $jsonName = $this->getModuleJsonName($name);
            if ($jsonName) {
                // If duplicate exists, prefer the enabled status
                if (isset($normalized[$jsonName])) {
                    $normalized[$jsonName] = $normalized[$jsonName] || $status;
                } else {
                    $normalized[$jsonName] = $status;
                }
            }
        }

        return $normalized;
    }

    /**
     * Save module statuses to the modules_statuses.json file.
     * Uses module.json names (what nwidart/laravel-modules uses).
     */
    protected function saveModuleStatuses(array $statuses): void
    {
        // Ensure all keys are module.json names
        $normalized = [];
        foreach ($statuses as $name => $status) {
            $jsonName = $this->getModuleJsonName($name);
            if ($jsonName) {
                $normalized[$jsonName] = $status;
            }
        }

        File::put($this->modulesStatusesPath, json_encode($normalized, JSON_PRETTY_PRINT));
    }

    /**
     * Clean up orphaned entries from module_statuses.json.
     * Removes entries for modules whose folders have been manually deleted.
     * Normalizes module names to match module.json names (what nwidart uses).
     */
    public function cleanupOrphanedModuleStatuses(): void
    {
        if (! File::exists($this->modulesStatusesPath)) {
            return;
        }

        // Read raw file to detect if cleanup is needed
        $rawStatuses = json_decode(File::get($this->modulesStatusesPath), true) ?? [];
        $cleanedStatuses = [];
        $needsSave = false;

        foreach ($rawStatuses as $moduleName => $status) {
            $jsonName = $this->getModuleJsonName($moduleName);

            if ($jsonName) {
                // Check if key needs to be updated to module.json name
                if ($moduleName !== $jsonName) {
                    $needsSave = true;
                    Log::info("Normalizing module name: {$moduleName} -> {$jsonName}");
                }

                // If duplicate exists, prefer enabled status
                if (isset($cleanedStatuses[$jsonName])) {
                    $cleanedStatuses[$jsonName] = $cleanedStatuses[$jsonName] || $status;
                } else {
                    $cleanedStatuses[$jsonName] = $status;
                }
            } else {
                $needsSave = true;
                Log::info("Cleaned up orphaned module status entry: {$moduleName}");
            }
        }

        // Save if any changes were made
        if ($needsSave || \count($rawStatuses) !== \count($cleanedStatuses)) {
            $this->saveModuleStatuses($cleanedStatuses);
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
     * @param string $existingModuleName The name of the existing module to replace (from module.json)
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

        // Check if module was enabled (use normalized name for status lookup)
        $normalizedExisting = $this->normalizeModuleName($existingModuleName);
        $moduleStatuses = $this->getModuleStatuses();
        $wasEnabled = $moduleStatuses[$normalizedExisting] ?? false;

        // Get the actual folder name (may be different case than status key)
        $actualFolderName = $this->getActualModuleFolderName($existingModuleName);
        if ($actualFolderName) {
            $existingModulePath = $this->modulesPath . '/' . $actualFolderName;

            // Clean up old assets first
            $this->cleanupModuleAssets($actualFolderName);

            // Disable the module before deletion
            if ($wasEnabled) {
                try {
                    Artisan::call('module:disable', ['module' => $normalizedExisting]);
                } catch (\Throwable $e) {
                    Log::warning("Could not disable module before replacement: " . $e->getMessage());
                }
            }

            // Delete the old module files
            File::deleteDirectory($existingModulePath);
        }

        // Remove old status entry if module name changed
        $normalizedNew = $this->normalizeModuleName($moduleName);
        if ($normalizedExisting !== $normalizedNew && isset($moduleStatuses[$normalizedExisting])) {
            unset($moduleStatuses[$normalizedExisting]);
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
     *
     * @param string $tempPath The temporary extraction path
     * @param string $folderName The PascalCase folder name for PSR-4 autoloading
     * @param string $moduleName The lowercase name from module.json for status tracking
     */
    protected function installModuleFromTemp(string $tempPath, string $folderName, string $moduleName): string
    {
        // Fire action before module installation
        Hook::doAction(ModuleActionHook::MODULE_INSTALLING_BEFORE, $moduleName, ['folder' => $folderName, 'path' => $tempPath]);

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
        // Use lowercase $moduleName (from module.json) as the key since that's what nwidart/laravel-modules expects.
        $moduleStatuses = $this->getModuleStatuses();
        $normalizedName = $this->normalizeModuleName($moduleName);
        $moduleStatuses[$normalizedName] = false;
        File::put($this->modulesStatusesPath, json_encode($moduleStatuses, JSON_PRETTY_PRINT));

        Log::info("Module installed: folder={$folderName}, status_key={$normalizedName}, target={$targetPath}");

        // Publish pre-built assets if the module contains them.
        // Use path-based method since module isn't registered in Module facade yet.
        // Use slug for asset paths (lowercase) since that's what the build system uses.
        $moduleSlug = Str::slug($folderName);
        if ($this->hasPrebuiltAssetsAtPath($targetPath, $moduleSlug)) {
            $this->publishModuleAssetsFromPath($targetPath, $moduleSlug, force: true);
            Log::info("Published pre-built assets for module {$folderName}");
        }

        // Regenerate Composer autoloader so the new module classes can be found.
        // Without this, activating the module will fail with "Class not found" error.
        $this->regenerateAutoloader();

        // Clear the cache.
        Artisan::call('cache:clear');

        // Fire action after module installation
        Hook::doAction(ModuleActionHook::MODULE_INSTALLED_AFTER, $normalizedName, $targetPath);

        // Return the lowercase module name (from module.json) as the module identifier
        // This is what the UI and other parts of the system use to identify the module
        return $normalizedName;
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

        // Read description from description.md file
        $description = $this->getModuleDescriptionFromFile($modulePath);

        return [
            'name' => $moduleJson['name'] ?? basename($modulePath),
            'version' => $moduleJson['version'] ?? '1.0.0',
            'description' => $description,
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
            // Extract the PascalCase folder name from providers or composer.json
            // This is critical for case-sensitive filesystems (Linux)
            $folderName = $this->extractNamespaceFolderFromPath($tempPath);

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

    /**
     * Extract the PascalCase folder name from a module's providers or composer.json.
     *
     * This is critical for case-sensitive filesystems (Linux). The folder name must
     * match the PSR-4 namespace (e.g., "Crm" not "crm") for autoloading to work.
     *
     * Priority:
     * 1. Extract from module.json providers: "Modules\\Crm\\..." -> "Crm"
     * 2. Extract from composer.json PSR-4: "Modules\\Crm\\": "app/" -> "Crm"
     * 3. Fallback to module.json name converted to StudlyCase
     */
    protected function extractNamespaceFolderFromPath(string $modulePath): string
    {
        // Try to extract from module.json providers first
        $moduleJsonPath = $modulePath . '/module.json';
        if (File::exists($moduleJsonPath)) {
            $moduleJson = json_decode(File::get($moduleJsonPath), true);

            // Check providers array for namespace
            $providers = $moduleJson['providers'] ?? [];
            foreach ($providers as $provider) {
                // Match pattern: Modules\{ModuleName}\...
                if (preg_match('/^Modules\\\\([^\\\\]+)\\\\/', $provider, $matches)) {
                    return $matches[1]; // Returns "Crm" from "Modules\Crm\..."
                }
            }
        }

        // Try to extract from composer.json PSR-4 namespaces
        $composerJsonPath = $modulePath . '/composer.json';
        if (File::exists($composerJsonPath)) {
            $composerJson = json_decode(File::get($composerJsonPath), true);
            $psr4 = $composerJson['autoload']['psr-4'] ?? [];

            foreach (array_keys($psr4) as $namespace) {
                // Match pattern: Modules\{ModuleName}\
                if (preg_match('/^Modules\\\\([^\\\\]+)\\\\/', $namespace, $matches)) {
                    return $matches[1]; // Returns "Crm" from "Modules\Crm\"
                }
            }
        }

        // Fallback: convert module.json name to StudlyCase
        if (File::exists($moduleJsonPath)) {
            $moduleJson = json_decode(File::get($moduleJsonPath), true);
            $name = $moduleJson['name'] ?? basename($modulePath);

            return Str::studly($name);
        }

        return Str::studly(basename($modulePath));
    }

    public function toggleModule($moduleName, $enable = true): bool
    {
        $action = $enable ? 'enable' : 'disable';
        Log::info("Attempting to {$action} module: {$moduleName}");

        // Fire action hooks before enabling/disabling
        if ($enable) {
            Hook::doAction(ModuleActionHook::MODULE_ENABLING_BEFORE, $moduleName);
        } else {
            Hook::doAction(ModuleActionHook::MODULE_DISABLING_BEFORE, $moduleName);
        }

        try {
            // Reload Composer autoloader to ensure newly uploaded module classes are available.
            // This is critical when activating a module that was just uploaded in a previous request.
            $this->reloadAutoloader();
            Log::info("Autoloader reloaded for module {$moduleName}");

            // Clear the cache.
            Artisan::call('cache:clear');

            // Activate/Deactivate the module.
            $callbackName = $enable ? 'module:enable' : 'module:disable';
            Log::info("Calling artisan {$callbackName} for module {$moduleName}");

            $exitCode = Artisan::call($callbackName, ['module' => $moduleName]);
            $output = Artisan::output();

            Log::info("Artisan {$callbackName} result for {$moduleName}: exit={$exitCode}, output={$output}");

            if ($exitCode !== 0) {
                throw new \RuntimeException("Artisan command failed with exit code {$exitCode}: {$output}");
            }

            // When enabling a module, run migrations and publish assets
            if ($enable) {
                Hook::doAction(ModuleActionHook::MODULE_MIGRATING_BEFORE, $moduleName);
                $this->runModuleMigrations($moduleName);
                Hook::doAction(ModuleActionHook::MODULE_MIGRATED_AFTER, $moduleName);

                Hook::doAction(ModuleActionHook::MODULE_ASSETS_PUBLISHING_BEFORE, $moduleName);
                $this->publishModuleAssets($moduleName);
                Hook::doAction(ModuleActionHook::MODULE_ASSETS_PUBLISHED_AFTER, $moduleName);
            }

            Log::info("Successfully {$action}d module: {$moduleName}");

            // Fire action hooks after enabling/disabling
            if ($enable) {
                Hook::doAction(ModuleActionHook::MODULE_ENABLED_AFTER, $moduleName);
            } else {
                Hook::doAction(ModuleActionHook::MODULE_DISABLED_AFTER, $moduleName);
            }
        } catch (\Throwable $th) {
            Log::error("Failed to {$action} module {$moduleName}: " . $th->getMessage(), [
                'exception' => $th::class,
                'file' => $th->getFile(),
                'line' => $th->getLine(),
                'trace' => $th->getTraceAsString(),
            ]);
            throw new ModuleException(__('Failed to :action module. Error: :error', [
                'action' => $action,
                'error' => $th->getMessage(),
            ]));
        }

        return true;
    }

    /**
     * Run database migrations for a specific module.
     *
     * This ensures that when a module is enabled or updated,
     * its database schema is properly set up.
     */
    protected function runModuleMigrations(string $moduleName): void
    {
        Log::info("Running migrations for module: {$moduleName}");

        try {
            $exitCode = Artisan::call('migrate', [
                '--force' => true,
            ]);
            $output = Artisan::output();

            Log::info("Migration result for {$moduleName}: exit={$exitCode}, output={$output}");

            if ($exitCode !== 0) {
                Log::warning("Migration for module {$moduleName} returned non-zero exit code: {$exitCode}");
            }
        } catch (\Throwable $th) {
            Log::error("Failed to run migrations for module {$moduleName}: " . $th->getMessage());
            // Don't throw - migrations might fail if tables already exist, which is fine
        }
    }

    /**
     * Reload Composer autoloader to pick up newly added module classes.
     *
     * This reads each module's composer.json to get the correct PSR-4 mappings,
     * since modules may have their classes in subdirectories (e.g., app/).
     */
    protected function reloadAutoloader(): void
    {
        $autoloadFile = base_path('vendor/autoload.php');
        if (! File::exists($autoloadFile)) {
            return;
        }

        // Get the Composer ClassLoader instance
        $loader = require $autoloadFile;

        // Re-register the PSR-4 autoload mappings for each module
        $modulesPath = $this->modulesPath;
        if (! File::isDirectory($modulesPath)) {
            return;
        }

        foreach (File::directories($modulesPath) as $moduleDir) {
            $moduleName = basename($moduleDir);
            $composerJsonPath = $moduleDir . '/composer.json';

            // Read module's composer.json for PSR-4 mappings
            if (File::exists($composerJsonPath)) {
                try {
                    $composerJson = json_decode(File::get($composerJsonPath), true);
                    $psr4 = $composerJson['autoload']['psr-4'] ?? [];

                    foreach ($psr4 as $namespace => $path) {
                        // Handle both string and array paths
                        $paths = is_array($path) ? $path : [$path];
                        foreach ($paths as $p) {
                            $fullPath = $moduleDir . '/' . trim($p, '/');
                            if (File::isDirectory($fullPath)) {
                                $loader->addPsr4($namespace, $fullPath . '/');
                            }
                        }
                    }

                    // Also register files autoload if present
                    $files = $composerJson['autoload']['files'] ?? [];
                    foreach ($files as $file) {
                        $filePath = $moduleDir . '/' . $file;
                        if (File::exists($filePath)) {
                            require_once $filePath;
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning("Failed to parse composer.json for module {$moduleName}: " . $e->getMessage());
                }
            } else {
                // Fallback: register module root as PSR-4 path
                $namespace = "Modules\\{$moduleName}\\";
                $loader->addPsr4($namespace, $moduleDir . '/');
            }
        }
    }

    public function toggleModuleStatus(string $moduleName): bool
    {
        $jsonName = $this->getModuleJsonName($moduleName);

        if (! $jsonName) {
            throw new ModuleException(__('Module not found.'));
        }

        $moduleStatuses = $this->getModuleStatuses();

        // If module is not in statuses file, add it as disabled first
        // then the toggle will enable it (fixing the double-click issue)
        if (! isset($moduleStatuses[$jsonName])) {
            $moduleStatuses[$jsonName] = false;
        }

        // Toggle the status.
        $moduleStatuses[$jsonName] = ! $moduleStatuses[$jsonName];
        $newStatus = $moduleStatuses[$jsonName];

        // Run the module enable/disable artisan command (uses module.json name)
        $this->toggleModule($jsonName, $newStatus);

        return $newStatus;
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

        foreach ($moduleNames as $moduleName) {
            $jsonName = $this->getModuleJsonName($moduleName);
            try {
                if (! $jsonName) {
                    $results[$moduleName] = false;
                    continue;
                }

                $this->toggleModule($jsonName, true);
                $results[$jsonName] = true;
            } catch (\Throwable $e) {
                Log::error("Failed to activate module " . $jsonName . ": " . $e->getMessage());
                $results[$jsonName] = false;
            }
        }

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

        foreach ($moduleNames as $moduleName) {
            $jsonName = $this->getModuleJsonName($moduleName);
            try {
                if (! $jsonName) {
                    $results[$moduleName] = false;
                    continue;
                }

                $this->toggleModule($jsonName, false);
                $results[$jsonName] = true;
            } catch (\Throwable $e) {
                Log::error("Failed to deactivate module " . $jsonName . ": " . $e->getMessage());
                $results[$jsonName] = false;
            }
        }

        Artisan::call('cache:clear');

        return $results;
    }

    public function deleteModule(string $moduleName): void
    {
        $module = $this->findModuleByName($moduleName);

        if (! $module) {
            throw new ModuleException(__('Module not found.'), Response::HTTP_NOT_FOUND);
        }

        // Fire action before module deletion
        Hook::doAction(ModuleActionHook::MODULE_DELETING_BEFORE, $moduleName);

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

        // Fire action after module deletion
        Hook::doAction(ModuleActionHook::MODULE_DELETED_AFTER, $moduleName);
    }

    /**
     * Regenerate Composer autoloader and Laravel bootstrap caches.
     * This is necessary after uploading a module via zip file.
     */
    protected function regenerateAutoloader(): void
    {
        // Set HOME/COMPOSER_HOME for shared hosting environments
        $env = array_merge($_ENV, $_SERVER, [
            'HOME' => getenv('HOME') ?: base_path(),
            'COMPOSER_HOME' => getenv('COMPOSER_HOME') ?: base_path('.composer'),
        ]);

        // Run composer dump-autoload
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
            } else {
                Log::info('Composer autoloader regenerated successfully');
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to regenerate autoloader: ' . $e->getMessage());
        }

        // Regenerate Laravel bootstrap caches (packages.php, services.php)
        try {
            Artisan::call('package:discover', ['--ansi' => true]);
            Log::info('Package discovery completed');
        } catch (\Throwable $e) {
            Log::warning('Failed to run package:discover: ' . $e->getMessage());
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
        $module = $this->findModuleByName($moduleName);
        if (! $module) {
            Log::info("Module {$moduleName} not found for asset publishing");
            return false;
        }

        $moduleSlug = Str::slug($moduleName);
        // Use actual module path to handle case sensitivity (folder might be lowercase)
        $sourcePath = $module->getPath() . '/dist/build-' . $moduleSlug;
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
        $module = $this->findModuleByName($moduleName);
        if (! $module) {
            return false;
        }

        $moduleSlug = Str::slug($moduleName);
        // Use actual module path to handle case sensitivity
        $distPath = $module->getPath() . '/dist/build-' . $moduleSlug;

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
        $moduleSlug = Str::slug($moduleName);
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

    /**
     * Check if a module has pre-built assets using a direct filesystem path.
     * Use this during upload when the module isn't registered in the Module facade yet.
     *
     * @param string $modulePath The absolute path to the module directory
     * @param string $moduleName The module name (for slug generation)
     * @return bool Whether the module has pre-built assets
     */
    public function hasPrebuiltAssetsAtPath(string $modulePath, string $moduleName): bool
    {
        $moduleSlug = Str::slug($moduleName);
        $distPath = $modulePath . '/dist/build-' . $moduleSlug;

        return File::isDirectory($distPath) && File::exists($distPath . '/manifest.json');
    }

    /**
     * Publish pre-built assets from a module's dist directory using a direct filesystem path.
     * Use this during upload when the module isn't registered in the Module facade yet.
     *
     * @param string $modulePath The absolute path to the module directory
     * @param string $moduleName The module name (for slug generation)
     * @param bool $force Whether to overwrite existing assets
     * @return bool Whether assets were published
     */
    public function publishModuleAssetsFromPath(string $modulePath, string $moduleName, bool $force = false): bool
    {
        $moduleSlug = Str::slug($moduleName);
        $sourcePath = $modulePath . '/dist/build-' . $moduleSlug;
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
}
