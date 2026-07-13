<?php

declare(strict_types=1);

use App\Services\InstallationService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

test('installer only accepts trusted module download urls', function (string $url, bool $isTrusted) {
    $installationService = app(InstallationService::class);

    expect($installationService->isTrustedModuleDownloadUrl($url))->toBe($isTrusted);
})->with([
    ['https://evil.example/module.zip', false],
    ['https://laradashboard.com/api/modules/crm/download/1.0.0', true],
    ['https://laradashboard.com/evil/path', false],
    ['https://evil.example/api/modules/crm/download/1.0.0', false],
]);

test('installer only resolves modules from allowed status slugs', function () {
    $statusesPath = base_path('modules_statuses.json');
    $originalStatuses = File::exists($statusesPath) ? File::get($statusesPath) : null;

    File::put($statusesPath, json_encode([
        'crm' => true,
        'blog' => false,
    ], JSON_THROW_ON_ERROR));

    Http::fake([
        'laradashboard.com/api/marketplace/modules/bulk-lookup' => Http::response([
            'success' => true,
            'data' => [
                [
                    'slug' => 'crm',
                    'name' => 'CRM',
                    'version' => '1.0.0',
                    'module_type' => 'free',
                    'is_free' => true,
                ],
            ],
        ]),
    ]);

    $trustedModules = app(InstallationService::class)->getTrustedModulesForInstall([
        'crm',
        'attacker-module',
    ]);

    expect($trustedModules)->toHaveCount(1);
    expect($trustedModules[0]['slug'])->toBe('crm');

    if ($originalStatuses !== null) {
        File::put($statusesPath, $originalStatuses);
    } else {
        File::delete($statusesPath);
    }
});

test('installer builds download urls from marketplace config only', function () {
    config(['laradashboard.marketplace.url' => 'https://laradashboard.com']);

    $url = app(InstallationService::class)->buildTrustedModuleDownloadUrl('crm', '2.1.0');

    expect($url)->toBe('https://laradashboard.com/api/modules/crm/download/2.1.0');
});
