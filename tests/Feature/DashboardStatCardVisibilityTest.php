<?php

declare(strict_types=1);

use App\Enums\Hooks\DashboardFilterHook;
use App\Livewire\Dashboard\StatCardGrid;
use App\Livewire\Dashboard\WidgetCustomizer;
use App\Models\Permission;
use App\Models\User;
use App\Models\UserMeta;
use App\Services\Dashboard\DashboardWidgetService;
use App\Support\Facades\Hook;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    Permission::firstOrCreate(['name' => 'dashboard.view', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'post.view', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'user.view', 'guard_name' => 'web']);
    $this->withoutVite();
});

function dashboardContext(): array
{
    return [
        'total_posts' => '5',
        'total_users' => '3',
        'total_roles' => '2',
        'total_permissions' => '10',
        'languages' => ['total' => '2', 'active' => '1'],
    ];
}

test('dashboard widget service hides and restores widgets per user', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(['post.view']);

    $service = app(DashboardWidgetService::class);
    $context = dashboardContext();

    expect(collect($service->getVisibleStatWidgets($context, $user))->pluck('id')->all())
        ->toContain('core_posts');

    $service->hideWidget('core_posts', $user);

    expect(collect($service->getVisibleStatWidgets($context, $user))->pluck('id')->all())
        ->not->toContain('core_posts');

    $service->showWidget('core_posts', $user);

    expect(collect($service->getVisibleStatWidgets($context, $user))->pluck('id')->all())
        ->toContain('core_posts');
});

test('dashboard widget preferences persist in user meta', function () {
    $user = User::factory()->create();

    app(DashboardWidgetService::class)->hideWidget('core_users', $user);

    $meta = UserMeta::query()
        ->where('user_id', $user->id)
        ->where('meta_key', DashboardWidgetService::USER_HIDDEN_META_KEY)
        ->first();

    expect($meta)->not->toBeNull()
        ->and(json_decode($meta->meta_value, true))->toContain('core_users');
});

test('stat card grid hides a widget from the dashboard grid', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(['dashboard.view', 'post.view']);

    $this->actingAs($user);

    Livewire::test(StatCardGrid::class, ['context' => dashboardContext()])
        ->assertSee('Posts')
        ->call('hideWidget', 'core_posts')
        ->assertDontSee('Posts');
});

test('widget customizer restores hidden module stats', function () {
    Hook::addFilter(DashboardFilterHook::DASHBOARD_STATS, function (array $stats) {
        $stats[] = [
            'id' => 'test_module_stat',
            'order' => 99,
            'label' => 'Module Stat',
            'value' => 7,
            'url' => '#',
        ];

        return $stats;
    });

    $user = User::factory()->create();
    $user->givePermissionTo('dashboard.view');

    $this->actingAs($user);

    Livewire::test(StatCardGrid::class, ['context' => dashboardContext()])
        ->call('hideWidget', 'test_module_stat')
        ->assertDontSee('Module Stat');

    Livewire::test(WidgetCustomizer::class)
        ->set('selectedVisibleIds', ['test_module_stat'])
        ->call('savePreferences')
        ->assertRedirect(route('admin.dashboard'));
});

test('dashboard hides section widgets when user hides them', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(['dashboard.view', 'user.view', 'post.view']);

    app(DashboardWidgetService::class)->hideWidget('quick_actions', $user);
    app(DashboardWidgetService::class)->hideWidget('user_growth', $user);

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertDontSee(__('Quick Actions'))
        ->assertDontSee(__('User Growth'));
});

test('dashboard shows customize control in header', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('dashboard.view');

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee(__('Customize dashboard'));
});
