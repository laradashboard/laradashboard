<?php

declare(strict_types=1);

use App\Services\Modules\ModuleService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->moduleService = app(ModuleService::class);
    $this->studlyName = 'ReplaceActivateTest';
    $this->slugName = 'replaceactivatetest';
    $this->statusFile = base_path('modules_statuses.json');
    $this->originalStatuses = File::exists($this->statusFile)
        ? File::get($this->statusFile)
        : null;

    foreach ([base_path('modules/' . $this->slugName), base_path('modules/' . $this->studlyName)] as $path) {
        if (File::isDirectory($path)) {
            File::deleteDirectory($path);
        }
    }

    $this->artisan('module:make', ['name' => [$this->studlyName]])->assertSuccessful();

    $actualFolder = $this->moduleService->getActualModuleFolderName($this->studlyName);
    expect($actualFolder)->not->toBeNull();
    $this->modulePath = base_path('modules/' . $actualFolder);
});

afterEach(function () {
    foreach ([base_path('modules/' . $this->slugName), base_path('modules/' . $this->studlyName)] as $path) {
        if (File::isDirectory($path)) {
            File::deleteDirectory($path);
        }
    }

    if ($this->originalStatuses !== null) {
        File::put($this->statusFile, $this->originalStatuses);
    }
});

test('replace module activates the module even when it was previously disabled', function () {
    $this->moduleService->setModuleStatus($this->slugName, false);

    $tempPath = storage_path('app/modules_temp/replace_test_' . uniqid('', true));
    File::ensureDirectoryExists($tempPath);

    $replacementPath = $tempPath . '/' . $this->studlyName;
    File::copyDirectory($this->modulePath, $replacementPath);

    $moduleJsonPath = $replacementPath . '/module.json';
    $moduleJson = json_decode(File::get($moduleJsonPath), true, 512, JSON_THROW_ON_ERROR);
    $moduleJson['version'] = '9.9.9';
    File::put($moduleJsonPath, json_encode($moduleJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    $this->moduleService->replaceModule($tempPath, $this->slugName);

    $statuses = $this->moduleService->getModuleStatuses();

    expect($statuses[$this->slugName])->toBeTrue();
    expect(File::exists($this->modulePath . '/module.json'))->toBeTrue();

    $installedJson = json_decode(File::get($this->modulePath . '/module.json'), true, 512, JSON_THROW_ON_ERROR);
    expect($installedJson['version'])->toBe('9.9.9');
});

test('replace module refreshes nwidart registry before activation', function () {
    $this->moduleService->setModuleStatus($this->slugName, true);

    $tempPath = storage_path('app/modules_temp/replace_test_' . uniqid('', true));
    File::ensureDirectoryExists($tempPath);

    $replacementPath = $tempPath . '/' . $this->studlyName;
    File::copyDirectory($this->modulePath, $replacementPath);

    $moduleJsonPath = $replacementPath . '/module.json';
    $moduleJson = json_decode(File::get($moduleJsonPath), true, 512, JSON_THROW_ON_ERROR);
    $moduleJson['version'] = '8.8.8';
    File::put($moduleJsonPath, json_encode($moduleJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    app('modules')->resetModules();

    $this->moduleService->replaceModule($tempPath, Str::studly($this->slugName));

    expect($this->moduleService->getModuleStatuses()[$this->slugName])->toBeTrue();
});
