<?php

declare(strict_types=1);

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware(VerifyCsrfToken::class);

    $this->user = User::factory()->create();
    $this->authorizedUser = User::factory()->create();

    // Set up authorized user with settings.edit permission
    $permission = Permission::firstOrCreate(['name' => 'settings.edit', 'guard_name' => 'web']);
    $role = Role::firstOrCreate(['name' => 'ThemeAdmin', 'guard_name' => 'web']);
    $role->givePermissionTo($permission);
    $this->authorizedUser->assignRole($role);
});

// ─── Theme Index ─────────────────────────────────────────────────────────────

test('unauthenticated user cannot access theme page', function () {
    $response = $this->get(route('admin.theme.index'));

    $response->assertRedirect();
});

test('user without settings.edit permission cannot access theme page', function () {
    $response = $this->actingAs($this->user)->get(route('admin.theme.index'));

    $response->assertForbidden();
});

test('authorized user can access theme page', function () {
    $response = $this->actingAs($this->authorizedUser)->get(route('admin.theme.index'));

    $response->assertOk();
    $response->assertViewIs('backend.pages.theme.index');
});

test('theme page defaults to choose-theme tab', function () {
    $response = $this->actingAs($this->authorizedUser)->get(route('admin.theme.index'));

    $response->assertOk();
    $response->assertViewHas('tab', 'choose-theme');
});

test('theme page accepts tab parameter', function () {
    $response = $this->actingAs($this->authorizedUser)->get(route('admin.theme.index', ['tab' => 'admin-theme']));

    $response->assertOk();
    $response->assertViewHas('tab', 'admin-theme');
});

// ─── Theme Store ─────────────────────────────────────────────────────────────

test('user without settings.edit permission cannot store theme settings', function () {
    $response = $this->actingAs($this->user)->post(route('admin.theme.store'), [
        'site_tagline' => 'Test Tagline',
    ]);

    $response->assertForbidden();
});

test('authorized user can store theme settings', function () {
    $response = $this->actingAs($this->authorizedUser)->post(route('admin.theme.store'), [
        'site_tagline' => 'Test Tagline',
        'copyright_text' => 'All rights reserved.',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');
});

// ─── Theme Activation ───────────────────────────────────────────────────────

test('theme page passes themes and activeTheme to view', function () {
    $response = $this->actingAs($this->authorizedUser)->get(route('admin.theme.index'));

    $response->assertOk();
    $response->assertViewHas('themes');
    $response->assertViewHas('activeTheme');
});

test('unauthenticated user cannot activate theme', function () {
    $response = $this->post(route('admin.theme.activate'), [
        'theme' => 'starter26',
    ]);

    $response->assertRedirect();
});

test('user without permission cannot activate theme', function () {
    $response = $this->actingAs($this->user)->post(route('admin.theme.activate'), [
        'theme' => 'starter26',
    ]);

    $response->assertForbidden();
});

test('theme activation requires a theme parameter', function () {
    $response = $this->actingAs($this->authorizedUser)->post(route('admin.theme.activate'), []);

    $response->assertSessionHasErrors('theme');
});
