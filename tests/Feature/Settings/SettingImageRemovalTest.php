<?php

declare(strict_types=1);

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware(VerifyCsrfToken::class);

    $this->admin = User::factory()->create();
    $adminRole = Role::firstOrCreate(['name' => 'Superadmin', 'guard_name' => 'web']);

    Permission::firstOrCreate(['name' => 'settings.edit', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'settings.view', 'guard_name' => 'web']);
    $adminRole->givePermissionTo(['settings.edit', 'settings.view']);
    $this->admin->assignRole($adminRole);

    $this->regularUser = User::factory()->create();
});

test('authorized user can remove a site logo image', function () {
    Setting::updateOrCreate(
        ['option_name' => 'site_logo_lite'],
        ['option_value' => '/uploads/settings/test-logo.png']
    );

    $response = $this->actingAs($this->admin)->deleteJson(route('admin.settings.remove-image'), [
        'option_key' => 'site_logo_lite',
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'message' => __('Image removed successfully.'),
    ]);

    $this->assertDatabaseHas('settings', [
        'option_name' => 'site_logo_lite',
        'option_value' => '',
    ]);
});

test('authorized user can remove a site favicon image', function () {
    Setting::updateOrCreate(
        ['option_name' => 'site_favicon'],
        ['option_value' => '/uploads/settings/test-favicon.ico']
    );

    $response = $this->actingAs($this->admin)->deleteJson(route('admin.settings.remove-image'), [
        'option_key' => 'site_favicon',
    ]);

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);

    $this->assertDatabaseHas('settings', [
        'option_name' => 'site_favicon',
        'option_value' => '',
    ]);
});

test('remove image rejects invalid option key', function () {
    $response = $this->actingAs($this->admin)->deleteJson(route('admin.settings.remove-image'), [
        'option_key' => 'some_random_setting',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('option_key');
});

test('remove image rejects missing option key', function () {
    $response = $this->actingAs($this->admin)->deleteJson(route('admin.settings.remove-image'), []);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('option_key');
});

test('unauthorized user cannot remove a setting image', function () {
    $response = $this->actingAs($this->regularUser)->deleteJson(route('admin.settings.remove-image'), [
        'option_key' => 'site_logo_lite',
    ]);

    $response->assertStatus(403);
});

test('unauthenticated user cannot remove a setting image', function () {
    $response = $this->deleteJson(route('admin.settings.remove-image'), [
        'option_key' => 'site_logo_lite',
    ]);

    $response->assertStatus(401);
});
