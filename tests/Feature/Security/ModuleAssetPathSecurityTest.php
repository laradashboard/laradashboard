<?php

declare(strict_types=1);

use App\Services\Modules\ModuleService;
use Illuminate\Support\Facades\File;

test('module asset publishing rejects unsafe paths', function (string $assetPath) {
    $moduleName = 'path-security-test';
    $modulePath = storage_path("app/testing-modules/{$moduleName}");
    File::ensureDirectoryExists($modulePath);

    File::put($modulePath . '/module.json', json_encode([
        'name' => $moduleName,
        'logo_image' => $assetPath,
    ], JSON_THROW_ON_ERROR));

    $targetPublicPath = public_path("images/modules/{$moduleName}");
    if (File::isDirectory($targetPublicPath)) {
        File::deleteDirectory($targetPublicPath);
    }

    $moduleService = app(ModuleService::class);
    $published = $moduleService->publishModuleImagesFromPath($modulePath, $moduleName);

    expect($published)->toBeFalse();
    expect(File::isDirectory($targetPublicPath))->toBeFalse();

    File::deleteDirectory($modulePath);
})->with([
    '../../.env',
    '../../../etc/passwd',
    'marketplace-assets/../../.env',
    '/etc/passwd',
    'https://evil.example/logo.png',
    'http://evil.example/logo.png',
]);
