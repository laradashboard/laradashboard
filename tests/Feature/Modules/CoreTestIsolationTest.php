<?php

declare(strict_types=1);

use App\Services\Modules\ModuleService;

/**
 * The core test suite must never boot installed modules or rewrite the real
 * modules_statuses.json (MODULES_STATUSES_FILE in phpunit.xml).
 */
test('tests use their own module statuses file', function () {
    $path = ModuleService::statusesFilePath();

    expect($path)->not->toBe(base_path('modules_statuses.json'))
        ->and($path)->toBe(config('modules.activators.file.statuses-file'))
        ->and(app(ModuleService::class)->modulesStatusesPath)->toBe($path);
});

test('no module service providers are registered during core tests', function () {
    $moduleProviders = array_filter(
        array_keys(app()->getLoadedProviders()),
        fn (string $provider) => str_starts_with($provider, 'Modules\\'),
    );

    expect($moduleProviders)->toBe([]);
});

test('no module vendor autoloader is registered during core tests', function () {
    $moduleVendors = array_filter(
        array_keys(Composer\Autoload\ClassLoader::getRegisteredLoaders()),
        fn (string $vendorDir) => str_starts_with(realpath($vendorDir) ?: $vendorDir, realpath(base_path('modules')) ?: base_path('modules')),
    );

    expect($moduleVendors)->toBe([]);
});

test('writing a module status in a test leaves the real file untouched', function () {
    $real = base_path('modules_statuses.json');
    $before = file_exists($real) ? md5_file($real) : null;

    app(ModuleService::class)->setModuleStatus('isolation-probe', false);

    expect(file_exists($real) ? md5_file($real) : null)->toBe($before);
});
