<?php

declare(strict_types=1);

namespace App\Support\Modules;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Nwidart\Modules\Laravel\Module as LaravelModule;

class SafeModule extends LaravelModule
{
    /**
     * Register the module's service providers with error protection.
     *
     * If a module's service provider throws during registration (e.g., due to
     * conflicting vendor packages, missing classes, or incompatible code),
     * the module is auto-disabled instead of crashing the entire application.
     */
    public function registerProviders(): void
    {
        try {
            parent::registerProviders();
        } catch (\Throwable $e) {
            $this->handleModuleBootFailure($e, 'register');
        }
    }

    /**
     * Boot the module with error protection.
     */
    public function boot(): void
    {
        try {
            parent::boot();
        } catch (\Throwable $e) {
            $this->handleModuleBootFailure($e, 'boot');
        }
    }

    /**
     * Handle a module that failed to register or boot.
     * Auto-disables the module and logs the error.
     */
    protected function handleModuleBootFailure(\Throwable $e, string $phase): void
    {
        $moduleName = $this->getName();
        $errorMessage = $e->getMessage();

        Log::error("Module '{$moduleName}' failed during {$phase}: {$errorMessage}", [
            'module' => $moduleName,
            'phase' => $phase,
            'exception' => $e::class,
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);

        // Auto-disable the module to prevent further crashes
        $statusesPath = base_path('modules_statuses.json');

        if (File::exists($statusesPath)) {
            $statuses = json_decode(File::get($statusesPath), true) ?? [];

            // Disable all case variants of this module name
            foreach ($statuses as $key => $status) {
                if (strtolower($key) === strtolower($moduleName)) {
                    $statuses[$key] = false;
                }
            }

            File::put($statusesPath, json_encode($statuses, JSON_PRETTY_PRINT));
        }

        // Store the failure reason for admin notification
        $disabledPath = storage_path('framework/modules_auto_disabled.json');
        $disabledModules = [];

        if (File::exists($disabledPath)) {
            $disabledModules = json_decode(File::get($disabledPath), true) ?? [];
        }

        $disabledModules[$moduleName] = "Failed during {$phase}: {$errorMessage}";
        File::put($disabledPath, json_encode($disabledModules, JSON_PRETTY_PRINT));
    }
}
