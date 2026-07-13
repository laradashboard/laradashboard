<?php

declare(strict_types=1);

use App\Models\Role;
use App\Models\User;
use Spatie\Permission\Models\Permission;

beforeEach(fn () => $this->setUpSecurityUsers());

test('non-superadmin cannot assign the superadmin role', function (string $action) {
    $targetUser = User::factory()->create([
        'email' => 'target@example.com',
        'username' => 'targetuser',
    ]);
    $targetUser->assignRole(Role::ADMIN);

    $payload = [
        'first_name' => 'Escalated',
        'last_name' => 'User',
        'email' => $action === 'create' ? 'escalated@example.com' : $targetUser->email,
        'username' => $action === 'create' ? 'escalateduser' : $targetUser->username,
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'roles' => [Role::SUPERADMIN],
    ];

    $response = $action === 'create'
        ? $this->actingAs($this->adminUser)->post('/admin/users', $payload)
        : $this->actingAs($this->adminUser)->put("/admin/users/{$targetUser->id}", array_diff_key($payload, array_flip(['password', 'password_confirmation'])));

    $response->assertSessionHasErrors('roles.0');

    if ($action === 'create') {
        expect(User::where('email', 'escalated@example.com')->exists())->toBeFalse();
    } else {
        expect($targetUser->fresh()->hasRole(Role::SUPERADMIN))->toBeFalse();
    }
})->with(['create', 'update']);

test('superadmin can assign roles to other users', function () {
    Permission::firstOrCreate(['name' => 'user.edit', 'guard_name' => 'web']);

    $targetUser = User::factory()->create();
    $targetUser->assignRole(Role::ADMIN);

    $response = $this->actingAs($this->superadminUser)->put("/admin/users/{$targetUser->id}", [
        'first_name' => $targetUser->first_name,
        'last_name' => $targetUser->last_name,
        'email' => $targetUser->email,
        'username' => $targetUser->username,
        'roles' => [Role::ADMIN],
    ]);

    $response->assertRedirect();
    expect($targetUser->fresh()->hasRole(Role::ADMIN))->toBeTrue();
});
