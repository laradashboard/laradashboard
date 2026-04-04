<?php

declare(strict_types=1);

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Role;
use App\Models\User;
use App\Notifications\AccountCreatedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Permission;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware(VerifyCsrfToken::class);

    $this->admin = User::factory()->create();
    $adminRole = Role::firstOrCreate(['name' => 'Superadmin', 'guard_name' => 'web']);

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

test('admin-created user is automatically email verified', function () {
    $role = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);

    Notification::fake();

    $this->actingAs($this->admin)->post('/admin/users', [
        'first_name' => 'Jane',
        'last_name' => 'Smith',
        'email' => 'jane@example.com',
        'username' => 'janesmith',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'roles' => ['editor'],
    ]);

    $user = User::where('email', 'jane@example.com')->first();

    expect($user)->not->toBeNull();
    expect($user->email_verified_at)->not->toBeNull();
    expect($user->hasVerifiedEmail())->toBeTrue();
});

test('admin-created user receives login link when checkbox is checked', function () {
    $role = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);

    Notification::fake();

    $this->actingAs($this->admin)->post('/admin/users', [
        'first_name' => 'Jane',
        'last_name' => 'Smith',
        'email' => 'jane@example.com',
        'username' => 'janesmith',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'roles' => ['editor'],
        'send_login_link' => '1',
    ]);

    $user = User::where('email', 'jane@example.com')->first();

    Notification::assertSentTo($user, AccountCreatedNotification::class);
});

test('admin-created user does not receive login link when checkbox is unchecked', function () {
    $role = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);

    Notification::fake();

    $this->actingAs($this->admin)->post('/admin/users', [
        'first_name' => 'Jane',
        'last_name' => 'Smith',
        'email' => 'jane@example.com',
        'username' => 'janesmith',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'roles' => ['editor'],
    ]);

    $user = User::where('email', 'jane@example.com')->first();

    Notification::assertNotSentTo($user, AccountCreatedNotification::class);
});

test('admin can send login link from user edit page', function () {
    Notification::fake();

    $user = User::factory()->create();

    $response = $this->actingAs($this->admin)
        ->post("/admin/users/{$user->id}/send-login-link");

    $response->assertRedirect();
    $response->assertSessionHas('success');

    Notification::assertSentTo($user, AccountCreatedNotification::class);
});

test('unauthorized user cannot send login link', function () {
    $regularUser = User::factory()->create();
    $targetUser = User::factory()->create();

    $response = $this->actingAs($regularUser)
        ->post("/admin/users/{$targetUser->id}/send-login-link");

    $response->assertStatus(403);
});

test('unauthenticated user cannot send login link', function () {
    $user = User::factory()->create();

    $response = $this->post("/admin/users/{$user->id}/send-login-link");

    $response->assertRedirect('/login');
});
