<?php

declare(strict_types=1);

use App\Services\Modules\ModuleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\Support\Security\InteractsWithSecurityUsers;

pest()->use(RefreshDatabase::class);

uses(InteractsWithSecurityUsers::class);

beforeEach(function () {
    $this->setUpSecurityUsers();

    $this->moduleService = app(ModuleService::class);
    $this->studlyName = 'ReplaceEndpointTest';
    $this->slugName = 'replaceendpointtest';
    $this->statusFile = base_path('modules_statuses.json');
    $this->originalStatuses = File::exists($this->statusFile)
        ? File::get($this->statusFile)
        : null;

    foreach (['replaceendpointtest', 'ReplaceEndpointTest'] as $folder) {
        $path = base_path('modules/' . $folder);
        if (File::isDirectory($path)) {
            File::deleteDirectory($path);
        }
    }

    if (File::exists($this->statusFile)) {
        $statuses = json_decode(File::get($this->statusFile), true, 512, JSON_THROW_ON_ERROR) ?: [];
        unset($statuses['replaceendpointtest'], $statuses['ReplaceEndpointTest']);
        File::put($this->statusFile, json_encode($statuses, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    $this->artisan('module:make', ['name' => [$this->studlyName]])->assertSuccessful();

    $actualFolder = $this->moduleService->getActualModuleFolderName($this->studlyName);
    expect($actualFolder)->not->toBeNull();
    $this->modulePath = base_path('modules/' . $actualFolder);
});

afterEach(function () {
    try {
        $this->artisan('module:disable', ['module' => $this->slugName]);
    } catch (\Throwable) {
        // Module may already be deleted or disabled.
    }

    foreach (['replaceendpointtest', 'ReplaceEndpointTest'] as $folder) {
        $path = base_path('modules/' . $folder);
        if (File::isDirectory($path)) {
            File::deleteDirectory($path);
        }
    }

    foreach (File::glob(storage_path('app/modules_temp/replace_endpoint_*')) ?: [] as $tempPath) {
        if (File::isDirectory($tempPath)) {
            File::deleteDirectory($tempPath);
        }
    }

    if ($this->originalStatuses !== null) {
        File::put($this->statusFile, $this->originalStatuses);
    }
});

test('replace module endpoint reports module as already activated', function () {
    $this->moduleService->setModuleStatus($this->slugName, false);

    $tempPath = storage_path('app/modules_temp/replace_endpoint_' . uniqid('', true));
    File::ensureDirectoryExists($tempPath);

    $replacementPath = $tempPath . '/' . basename($this->modulePath);
    File::copyDirectory($this->modulePath, $replacementPath);

    $moduleJsonPath = $replacementPath . '/module.json';
    $moduleJson = json_decode(File::get($moduleJsonPath), true, 512, JSON_THROW_ON_ERROR);
    $moduleJson['version'] = '7.7.7';
    File::put($moduleJsonPath, json_encode($moduleJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    $response = $this->actingAs($this->superadminUser)->postJson(route('admin.modules.replace'), [
        'temp_path' => $tempPath,
        'existing_module_name' => $this->slugName,
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'already_activated' => true,
            'module_name' => $this->slugName,
        ]);

    expect($this->moduleService->getModuleStatuses()[$this->slugName])->toBeTrue();
});
