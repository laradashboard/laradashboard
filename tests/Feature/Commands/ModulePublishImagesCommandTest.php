<?php

declare(strict_types=1);

use App\Services\Modules\ModuleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;

pest()->use(RefreshDatabase::class);

test('module publish images command publishes logo for enabled module', function () {
    $moduleName = 'publish-images-test';
    $modulePath = storage_path("app/testing-modules/{$moduleName}");
    File::ensureDirectoryExists($modulePath.'/marketplace-assets');

    File::put($modulePath.'/module.json', json_encode([
        'name' => $moduleName,
        'logo_image' => 'logo.svg',
    ], JSON_THROW_ON_ERROR));

    File::put($modulePath.'/marketplace-assets/logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"></svg>');

    $targetPublicPath = public_path("images/modules/{$moduleName}");
    if (File::isDirectory($targetPublicPath)) {
        File::deleteDirectory($targetPublicPath);
    }

    $moduleService = app(ModuleService::class);

    expect($moduleService->publishModuleImagesFromPath($modulePath, $moduleName))->toBeTrue();
    expect(File::exists($targetPublicPath.'/logo.svg'))->toBeTrue();

    File::deleteDirectory($modulePath);
    File::deleteDirectory($targetPublicPath);
});

test('module publish images artisan command succeeds', function () {
    $this->artisan('module:publish-images')
        ->assertSuccessful();
});
