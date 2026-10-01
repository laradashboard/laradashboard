<?php

declare(strict_types=1);

use App\Models\Role;
use App\Models\User;
use App\Support\RolePermissionGuard;
use Spatie\Permission\Models\Permission;

beforeEach(fn () => $this->setUpSecurityUsers());

test('admin cannot rename or modify superadmin role in production', function () {
    config(['app.demo_mode' => false]);

    $superadminRole = Role::where('name', Role::SUPERADMIN)->firstOrFail();
    $superadminRole->forceFill(['is_super_admin' => true])->save();

    $response = $this->actingAs($this->adminUser)->put("/admin/roles/{$superadminRole->id}", [
        'name' => 'Superadmin2',
        'permissions' => ['role.view'],
    ]);

    $response->assertForbidden();
    expect($superadminRole->fresh()->name)->toBe(Role::SUPERADMIN);
});

test('admin cannot mint superadmin-only permissions on other roles', function () {
    config(['app.demo_mode' => false]);

    Permission::firstOrCreate(['name' => 'user.login_as', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'user.delete', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'role.view', 'guard_name' => 'web']);

    $adminRole = Role::where('name', Role::ADMIN)->firstOrFail();

    $response = $this->actingAs($this->adminUser)->put("/admin/roles/{$adminRole->id}", [
        'name' => Role::ADMIN,
        'permissions' => ['role.view', 'user.login_as', 'user.delete'],
    ]);

    $response->assertRedirect();
    $adminRole->refresh();

    expect($adminRole->hasPermissionTo('user.login_as'))->toBeFalse();
    expect($adminRole->hasPermissionTo('user.delete'))->toBeFalse();
    expect($adminRole->hasPermissionTo('role.view'))->toBeTrue();
});

test('admin with direct user.login_as permission cannot impersonate users', function () {
    Permission::firstOrCreate(['name' => 'user.login_as', 'guard_name' => 'web']);
    $this->adminUser->givePermissionTo('user.login_as');

    $targetUser = User::factory()->create();
    $targetUser->assignRole(Role::ADMIN);

    $response = $this->actingAs($this->adminUser)->get("/admin/users/{$targetUser->id}/login-as");

    $response->assertForbidden();
});

test('superadmin flag survives role rename attempts via assignment checks', function () {
    $superadminRole = Role::where('name', Role::SUPERADMIN)->firstOrFail();
    $superadminRole->forceFill(['is_super_admin' => true, 'name' => 'LegacySuperadminName'])->save();

    $targetUser = User::factory()->create();
    $targetUser->assignRole('LegacySuperadminName');

    expect($targetUser->fresh()->isSuperAdmin())->toBeTrue();
    expect(RolePermissionGuard::isSuperAdminOnlyPermission('user.login_as'))->toBeTrue();
});
