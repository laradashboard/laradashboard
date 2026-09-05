<?php

declare(strict_types=1);

use App\Enums\Hooks\DashboardFilterHook;
use App\Models\Permission;
use App\Models\User;
use App\Support\Facades\Hook;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    Permission::firstOrCreate(['name' => 'dashboard.view', 'guard_name' => 'web']);
    $this->withoutVite();
});

test('dashboard renders module stat cards registered through dashboard stats hook', function () {
    Hook::addFilter(DashboardFilterHook::DASHBOARD_STATS, function (array $stats) {
        $stats[] = [
            'id' => 'test_dashboard_stat',
            'order' => 1,
            'icon' => 'heroicons:star',
            'icon_bg' => '#6366F1',
            'label' => 'Hook Stat',
            'value' => 42,
            'url' => '#',
        ];

        return $stats;
    });

    $user = User::factory()->create();
    $user->givePermissionTo('dashboard.view');

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Hook Stat')
        ->assertSee('42');
});

test('dashboard hides module stat cards when permission is denied', function () {
    Hook::addFilter(DashboardFilterHook::DASHBOARD_STATS, function (array $stats) {
        $stats[] = [
            'id' => 'restricted_dashboard_stat',
            'order' => 1,
            'permission' => 'module.view',
            'icon' => 'heroicons:lock-closed',
            'icon_bg' => '#6366F1',
            'label' => 'Restricted Stat',
            'value' => 99,
            'url' => '#',
        ];

        return $stats;
    });

    $user = User::factory()->create();
    $user->givePermissionTo('dashboard.view');

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertDontSee('Restricted Stat');
});
