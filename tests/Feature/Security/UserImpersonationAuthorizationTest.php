<?php

declare(strict_types=1);

use App\Models\Role;

beforeEach(fn () => $this->setUpSecurityUsers());

test('user with login_as permission cannot impersonate a higher-privileged account', function () {
    $response = $this->actingAs($this->adminUser)
        ->get("/admin/users/{$this->superadminUser->id}/login-as");

    $response->assertForbidden();
    expect(auth()->id())->toBe($this->adminUser->id);
});

test('superadmin can impersonate a lower-privileged user', function () {
    $response = $this->actingAs($this->superadminUser)
        ->get("/admin/users/{$this->adminUser->id}/login-as");

    $response->assertRedirect(route('admin.dashboard'));
    expect(auth()->id())->toBe($this->adminUser->id);
});

test('user cannot impersonate themselves', function () {
    $user = $this->createUserWithRole(Role::ADMIN, ['user.login_as']);

    $response = $this->actingAs($user)
        ->get("/admin/users/{$user->id}/login-as");

    $response->assertForbidden();
    expect(auth()->id())->toBe($user->id);
});
