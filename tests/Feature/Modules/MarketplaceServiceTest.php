<?php

declare(strict_types=1);

use App\Services\Modules\MarketplaceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    config(['laradashboard.marketplace.url' => 'https://marketplace.test']);
    config(['laradashboard.marketplace.modules_endpoint' => '/api/modules']);
    config(['laradashboard.marketplace.modules_cache_duration' => 0]);
});

test('fetchModules returns modules from marketplace API', function () {
    Http::fake([
        'marketplace.test/api/modules*' => Http::response([
            'success' => true,
            'data' => [
                [
                    'name' => 'Test Module',
                    'slug' => 'test-module',
                    'description' => 'A test module',
                    'version' => '1.0.0',
                    'module_type' => 'free',
                ],
            ],
            'meta' => [
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 12,
                'total' => 1,
            ],
        ]),
    ]);

    $service = app(MarketplaceService::class);
    $result = $service->fetchModules();

    expect($result['success'])->toBeTrue()
        ->and($result['data'])->toHaveCount(1)
        ->and($result['data'][0]['slug'])->toBe('test-module')
        ->and($result['meta']['total'])->toBe(1);
});

test('fetchModules handles API connection failure gracefully', function () {
    Http::fake([
        'marketplace.test/api/modules*' => Http::response(null, 500),
    ]);

    $service = app(MarketplaceService::class);
    $result = $service->fetchModules();

    expect($result['success'])->toBeFalse()
        ->and($result['error'])->not->toBeNull()
        ->and($result['data'])->toBeEmpty();
});

test('fetchModules passes search and type filters to API', function () {
    Http::fake([
        'marketplace.test/api/modules*' => Http::response([
            'success' => true,
            'data' => [],
            'meta' => ['current_page' => 1, 'last_page' => 1, 'per_page' => 12, 'total' => 0],
        ]),
    ]);

    $service = app(MarketplaceService::class);
    $service->fetchModules(search: 'crm', type: 'free', page: 2);

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'search=crm')
            && str_contains($request->url(), 'type=free')
            && str_contains($request->url(), 'page=2');
    });
});

test('getInstalledModuleSlugs returns lowercase module names', function () {
    $service = app(MarketplaceService::class);
    $slugs = $service->getInstalledModuleSlugs();

    expect($slugs)->toBeArray();

    // All slugs should be lowercase
    foreach ($slugs as $slug) {
        expect($slug)->toBe(strtolower($slug));
    }
});
