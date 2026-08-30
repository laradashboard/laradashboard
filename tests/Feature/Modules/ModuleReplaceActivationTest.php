<?php

declare(strict_types=1);

use App\Services\Modules\ModuleService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

function removeReplaceActivateTestModuleFromStatuses(): void
{
    $statusFile = base_path('modules_statuses.json');

    if (! File::exists($statusFile)) {
        return;
    }

    $statuses = json_decode(File::get($statusFile), true, 512, JSON_THROW_ON_ERROR) ?: [];
    unset(
        $statuses['replaceactivatetest'],
        $statuses['ReplaceActivateTest'],
    );
    File::put($statusFile, json_encode($statuses, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

function deleteReplaceActivateTestModuleDirectories(): void
{
    foreach (['replaceactivatetest', 'ReplaceActivateTest'] as $folder) {
        $path = base_path('modules/' . $folder);

        if (File::isDirectory($path)) {
            File::deleteDirectory($path);
        }
    }
}

beforeEach(function () {
    $this->moduleService = app(ModuleService::class);
    $this->studlyName = 'ReplaceActivateTest';
    $this->slugName = 'replaceactivatetest';
    $this->statusFile = base_path('modules_statuses.json');
    $this->originalStatuses = File::exists($this->statusFile)
        ? File::get($this->statusFile)
        : null;

    deleteReplaceActivateTestModuleDirectories();
    removeReplaceActivateTestModuleFromStatuses();

    $this->artisan('module:make', ['name' => [$this->studlyName]])->assertSuccessful();

    $actualFolder = $this->moduleService->getActualModuleFolderName($this->studlyName);
    expect($actualFolder)->not->toBeNull();
    $this->modulePath = base_path('modules/' . $actualFolder);
});

afterEach(function () {
    try {
        Artisan::call('module:disable', ['module' => $this->slugName]);
    } catch (\Throwable) {
        // Module may already be deleted or disabled.
    }

    deleteReplaceActivateTestModuleDirectories();

    foreach (File::glob(storage_path('app/modules_temp/replace_test_*')) ?: [] as $tempPath) {
        if (File::isDirectory($tempPath)) {
            File::deleteDirectory($tempPath);
        }
    }

    if ($this->originalStatuses !== null) {
        File::put($this->statusFile, $this->originalStatuses);
    } else {
        removeReplaceActivateTestModuleFromStatuses();
    }
});

test('replace module activates the module even when it was previously disabled', function () {
    $this->moduleService->setModuleStatus($this->slugName, false);

    $tempPath = storage_path('app/modules_temp/replace_test_' . uniqid('', true));
    File::ensureDirectoryExists($tempPath);

    $replacementPath = $tempPath . '/' . basename($this->modulePath);
    File::copyDirectory($this->modulePath, $replacementPath);

    $moduleJsonPath = $replacementPath . '/module.json';
    $moduleJson = json_decode(File::get($moduleJsonPath), true, 512, JSON_THROW_ON_ERROR);
    $moduleJson['version'] = '9.9.9';
    File::put($moduleJsonPath, json_encode($moduleJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    $this->moduleService->replaceModule($tempPath, $this->slugName);

    $statuses = $this->moduleService->getModuleStatuses();

    expect($statuses[$this->slugName])->toBeTrue();

    $installedFolder = $this->moduleService->getActualModuleFolderName($this->slugName);
    expect($installedFolder)->not->toBeNull();

    $installedModulePath = base_path('modules/' . $installedFolder);
    expect(File::exists($installedModulePath . '/module.json'))->toBeTrue();

    $installedJson = json_decode(File::get($installedModulePath . '/module.json'), true, 512, JSON_THROW_ON_ERROR);
    expect($installedJson['version'])->toBe('9.9.9');
});

test('replace module refreshes nwidart registry before activation', function () {
    $this->moduleService->setModuleStatus($this->slugName, true);

    $tempPath = storage_path('app/modules_temp/replace_test_' . uniqid('', true));
    File::ensureDirectoryExists($tempPath);

    $replacementPath = $tempPath . '/' . basename($this->modulePath);
    File::copyDirectory($this->modulePath, $replacementPath);

    $moduleJsonPath = $replacementPath . '/module.json';
    $moduleJson = json_decode(File::get($moduleJsonPath), true, 512, JSON_THROW_ON_ERROR);
    $moduleJson['version'] = '8.8.8';
    File::put($moduleJsonPath, json_encode($moduleJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    app('modules')->resetModules();

    $this->moduleService->replaceModule($tempPath, Str::studly($this->slugName));

    expect($this->moduleService->getModuleStatuses()[$this->slugName])->toBeTrue();
});
