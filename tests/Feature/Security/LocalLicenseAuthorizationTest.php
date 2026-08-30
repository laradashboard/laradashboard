<?php

declare(strict_types=1);

use App\Models\Setting;
use App\Models\User;
use App\Services\LicenseVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

pest()->use(RefreshDatabase::class);

function licenseSubscriberUser(): User
{
    $user = User::factory()->create();
    $role = Role::firstOrCreate(['name' => 'Subscriber', 'guard_name' => 'web']);
    $role->syncPermissions([
        Permission::firstOrCreate(['name' => 'dashboard.view', 'guard_name' => 'web']),
        Permission::firstOrCreate(['name' => 'profile.view', 'guard_name' => 'web']),
    ]);
    $user->assignRole($role);

    return $user;
}

function licenseManagerUser(): User
{
    $user = User::factory()->create();
    Permission::firstOrCreate(['name' => 'module.activate', 'guard_name' => 'web']);
    $user->givePermissionTo('module.activate');

    return $user;
}

beforeEach(function () {
    app(LicenseVerificationService::class)->storeLicenseLocally(
        'pro-module',
        'REAL-VENDOR-LICENSE-SECRET',
        'Pro Module'
    );
});

test('unauthenticated users cannot read stored module licenses', function () {
    $this->getJson('/api/admin/licenses/show?module_slug=pro-module')
        ->assertUnauthorized();
});

test('low-privileged users cannot read stored module licenses', function () {
    $this->actingAs(licenseSubscriberUser())
        ->getJson('/api/admin/licenses/show?module_slug=pro-module')
        ->assertForbidden();
});

test('users with module.activate can read stored module licenses', function () {
    $this->actingAs(licenseManagerUser())
        ->getJson('/api/admin/licenses/show?module_slug=pro-module')
        ->assertOk()
        ->assertJsonPath('data.license_key', 'REAL-VENDOR-LICENSE-SECRET');
});

test('low-privileged users cannot overwrite stored module licenses', function () {
    $this->actingAs(licenseSubscriberUser())
        ->postJson('/api/admin/licenses/store', [
            'module_slug' => 'pro-module',
            'module_name' => 'Pro Module',
            'license_key' => 'ATTACKER-CONTROLLED-LICENSE-KEY',
        ])
        ->assertForbidden();

    expect(app(LicenseVerificationService::class)->getStoredLicense('pro-module')['license_key'])
        ->toBe('REAL-VENDOR-LICENSE-SECRET');
});

test('users with module.activate can store module licenses locally', function () {
    $this->actingAs(licenseManagerUser())
        ->postJson('/api/admin/licenses/store', [
            'module_slug' => 'pro-module',
            'module_name' => 'Pro Module',
            'license_key' => 'UPDATED-LICENSE-KEY-1234567890',
        ])
        ->assertOk()
        ->assertJson(['success' => true]);

    expect(app(LicenseVerificationService::class)->getStoredLicense('pro-module')['license_key'])
        ->toBe('UPDATED-LICENSE-KEY-1234567890');
});

test('low-privileged users cannot delete stored module licenses', function () {
    $this->actingAs(licenseSubscriberUser())
        ->postJson('/api/admin/licenses/remove', [
            'module_slug' => 'pro-module',
        ])
        ->assertForbidden();

    expect(app(LicenseVerificationService::class)->getStoredLicense('pro-module'))->not->toBeNull();
});

test('users with module.activate can delete stored module licenses', function () {
    $this->actingAs(licenseManagerUser())
        ->postJson('/api/admin/licenses/remove', [
            'module_slug' => 'pro-module',
        ])
        ->assertOk()
        ->assertJson(['success' => true]);

    expect(app(LicenseVerificationService::class)->getStoredLicense('pro-module'))->toBeNull();
});

test('users with settings.edit can manage stored module licenses', function () {
    $user = User::factory()->create();
    Permission::firstOrCreate(['name' => 'settings.edit', 'guard_name' => 'web']);
    $user->givePermissionTo('settings.edit');

    $this->actingAs($user)
        ->getJson('/api/admin/licenses/show?module_slug=pro-module')
        ->assertOk();

    $this->actingAs($user)
        ->postJson('/api/admin/licenses/remove', ['module_slug' => 'pro-module'])
        ->assertOk();

    expect(Setting::where('option_name', 'module_licenses')->value('option_value'))->toBe('[]');
});
