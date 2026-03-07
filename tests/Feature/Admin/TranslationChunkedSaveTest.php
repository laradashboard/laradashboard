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

    $this->admin = User::factory()->create();
    $adminRole = Role::firstOrCreate(['name' => 'Superadmin', 'guard_name' => 'web']);

    Permission::firstOrCreate(['name' => 'settings.edit', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'settings.view', 'guard_name' => 'web']);
    $adminRole->givePermissionTo(['settings.edit', 'settings.view']);
    $this->admin->assignRole($adminRole);
});

test('save chunk saves translations and returns redirect', function () {
    $response = $this->actingAs($this->admin)->postJson(route('admin.translations.save-chunk'), [
        'lang' => 'bn',
        'group' => 'json',
        'translations' => ['Hello' => 'হ্যালো', 'World' => 'বিশ্ব'],
    ]);

    $response->assertOk()
        ->assertJson(['status' => 'saved', 'count' => 2])
        ->assertJsonStructure(['redirect']);
});

test('save chunk merges with existing translations', function () {
    // Save first page
    $this->actingAs($this->admin)->postJson(route('admin.translations.save-chunk'), [
        'lang' => 'bn',
        'group' => 'json',
        'translations' => ['Hello' => 'হ্যালো'],
    ])->assertOk();

    // Save second page — should not lose first page's translation
    $this->actingAs($this->admin)->postJson(route('admin.translations.save-chunk'), [
        'lang' => 'bn',
        'group' => 'json',
        'translations' => ['World' => 'বিশ্ব'],
    ])->assertOk();

    // Verify both translations exist
    $translations = app(\App\Services\TranslationService::class)->getTranslations('bn', 'json');
    expect($translations)->toHaveKey('Hello', 'হ্যালো')
        ->toHaveKey('World', 'বিশ্ব');
});

test('save chunk filters empty values for json group', function () {
    $response = $this->actingAs($this->admin)->postJson(route('admin.translations.save-chunk'), [
        'lang' => 'bn',
        'group' => 'json',
        'translations' => ['Hello' => 'হ্যালো', 'Empty' => '', 'Null' => null],
    ]);

    $response->assertOk()
        ->assertJson(['status' => 'saved', 'count' => 3]);
});

test('save chunk preserves page context in redirect', function () {
    $response = $this->actingAs($this->admin)->postJson(route('admin.translations.save-chunk'), [
        'lang' => 'bn',
        'group' => 'json',
        'translations' => ['Hello' => 'হ্যালো'],
        'page' => 3,
        'per_page' => 100,
        'search' => 'hello',
    ]);

    $response->assertOk();
    $redirect = $response->json('redirect');
    expect($redirect)->toContain('page=3')
        ->toContain('per_page=100')
        ->toContain('search=hello');
});

test('save chunk requires authorization', function () {
    $regularUser = User::factory()->create();

    $response = $this->actingAs($regularUser)->postJson(route('admin.translations.save-chunk'), [
        'lang' => 'bn',
        'group' => 'json',
        'translations' => ['Hello' => 'হ্যালো'],
    ]);

    $response->assertForbidden();
});
