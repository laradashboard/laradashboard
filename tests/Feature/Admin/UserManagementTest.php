<?php

declare(strict_types=1);

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    // Disable CSRF protection for tests.
    $this->withoutMiddleware(VerifyCsrfToken::class);

    // Create admin user with permissions
    $this->admin = User::factory()->create();
    $adminRole = Role::firstOrCreate(['name' => 'Superadmin', 'guard_name' => 'web']);

    // Create necessary permissions
    Permission::firstOrCreate(['name' => 'user.view', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'user.create', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'user.edit', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'user.delete', 'guard_name' => 'web']);

    $adminRole->syncPermissions([
        'user.view',
        'user.create',
        'user.edit',
        'user.delete',
    ]);

    $this->admin->assignRole($adminRole);
});

test('admin can view users list', function () {
    $response = $this->actingAs($this->admin)->get('/admin/users');
    $response->assertStatus(200);
    $response->assertViewIs('backend.pages.users.index');
});

test('admin can create user', function () {
    $role = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);

    $response = $this->actingAs($this->admin)->post('/admin/users', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'username' => 'johndoe',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'roles' => ['editor'],
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('users', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'username' => 'johndoe',
    ]);

    $user = User::where('email', 'john@example.com')->first();
    expect($user->hasRole('editor'))->toBeTrue();
});

test('admin can update user', function () {
    $user = User::create([
        'first_name' => 'Original',
        'last_name' => 'Name',
        'email' => 'original@example.com',
        'username' => 'originaluser',
        'password' => Hash::make('password'),
    ]);

    $role = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);

    $response = $this->actingAs($this->admin)->put("/admin/users/{$user->id}", [
        'first_name' => 'Updated',
        'last_name' => 'Name',
        'email' => 'updated@example.com',
        'username' => 'updateduser',
        'roles' => ['editor'],
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'first_name' => 'Updated',
        'last_name' => 'Name',
        'email' => 'updated@example.com',
        'username' => 'updateduser',
    ]);

    $updatedUser = User::find($user->id);
    expect($updatedUser->hasRole('editor'))->toBeTrue();
});

test('admin can delete user', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($this->admin)->delete("/admin/users/{$user->id}");

    $response->assertRedirect();
    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

test('admin cannot delete themselves', function () {
    $response = $this->actingAs($this->admin)->delete("/admin/users/{$this->admin->id}");

    $response->assertRedirect();
    $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
});

test('user without permission cannot manage users', function () {
    $regularUser = User::factory()->create();

    $response = $this->actingAs($regularUser)->get('/admin/users');
    $response->assertStatus(403);

    $response = $this->actingAs($regularUser)->post('/admin/users', [
        'first_name' => 'New',
        'last_name' => 'User',
        'username' => 'newuser',
        'email' => 'newuser@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);
    $response->assertStatus(403);
});

test('validation works when creating user', function () {
    $response = $this->actingAs($this->admin)->post('/admin/users', [
        'first_name' => '',
        'last_name' => '',
        'email' => '',
        'password' => '',
    ]);

    $response->assertSessionHasErrors(['first_name', 'last_name', 'email', 'password']);
});
