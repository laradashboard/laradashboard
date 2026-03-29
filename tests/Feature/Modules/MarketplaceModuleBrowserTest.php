<?php

declare(strict_types=1);

use App\Livewire\Marketplace\MarketplaceModuleBrowser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    config(['laradashboard.marketplace.url' => 'https://marketplace.test']);
    config(['laradashboard.marketplace.modules_endpoint' => '/api/modules']);
    config(['laradashboard.marketplace.modules_cache_duration' => 0]);

    Http::fake([
        'marketplace.test/api/modules*' => Http::response([
            'success' => true,
            'data' => [
                [
                    'name' => 'CRM Module',
                    'slug' => 'crm',
                    'description' => 'Customer relationship management',
                    'version' => '2.0.0',
                    'module_type' => 'free',
                ],
                [
                    'name' => 'Pro Analytics',
                    'slug' => 'pro-analytics',
                    'description' => 'Advanced analytics dashboard',
                    'version' => '1.0.0',
                    'module_type' => 'pro',
                ],
            ],
            'meta' => [
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 12,
                'total' => 2,
            ],
        ]),
    ]);
});

test('marketplace browser component renders', function () {
    Livewire::test(MarketplaceModuleBrowser::class)
        ->assertStatus(200);
});

test('marketplace browser loads modules on init', function () {
    Livewire::test(MarketplaceModuleBrowser::class)
        ->call('loadModules')
        ->assertSet('loaded', true)
        ->assertSee('CRM Module')
        ->assertSee('Pro Analytics');
});

test('marketplace browser can filter by type', function () {
    Livewire::test(MarketplaceModuleBrowser::class)
        ->call('loadModules')
        ->call('setTypeFilter', 'free')
        ->assertSet('typeFilter', 'free')
        ->assertSet('page', 1);
});

test('marketplace browser resets page on search', function () {
    Livewire::test(MarketplaceModuleBrowser::class)
        ->call('loadModules')
        ->set('page', 3)
        ->set('search', 'crm')
        ->assertSet('page', 1);
});

test('marketplace browser blocks install in demo mode', function () {
    config(['app.demo_mode' => true]);

    Livewire::test(MarketplaceModuleBrowser::class)
        ->call('loadModules')
        ->call('installModule', 'test-module', '1.0.0')
        ->assertDispatched('notify', fn (string $name, array $params) => $params[0]['variant'] === 'error');
});
