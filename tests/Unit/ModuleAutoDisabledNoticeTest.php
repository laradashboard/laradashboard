<?php

declare(strict_types=1);

use App\Providers\ModuleServiceProvider;
use Illuminate\Support\Facades\File;

test('module boot flashes an auto-disabled notice and removes the file', function () {
    $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'modules-auto-disabled-'.uniqid('', true).'.json';
    File::put($path, json_encode(['Crm' => 'Provider file not found'], JSON_THROW_ON_ERROR));

    try {
        app()->getProvider(ModuleServiceProvider::class)->flashAutoDisabledModuleNotices($path);

        expect(session('warning'))->toBe('Module "Crm" was auto-disabled: Provider file not found')
            ->and(File::exists($path))->toBeFalse();
    } finally {
        File::delete($path);
    }
});

test('module boot ignores an auto-disabled notice file removed by another process', function () {
    $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'modules-auto-disabled-'.uniqid('', true).'.json';

    expect(fn () => app()->getProvider(ModuleServiceProvider::class)->flashAutoDisabledModuleNotices($path))
        ->not->toThrow(Throwable::class);
});
