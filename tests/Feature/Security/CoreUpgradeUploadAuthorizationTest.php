<?php

declare(strict_types=1);

use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Permission;
use Tests\Support\Security\InteractsWithSecurityUsers;

pest()->use(RefreshDatabase::class);

uses(InteractsWithSecurityUsers::class);

beforeEach(function () {
    $this->withoutMiddleware(VerifyCsrfToken::class);
    $this->setUpSecurityUsers();
    config(['app.demo_mode' => false]);

    Permission::firstOrCreate(['name' => 'settings.edit', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'settings.view', 'guard_name' => 'web']);
    $this->adminUser->givePermissionTo(['settings.edit', 'settings.view']);
    $this->superadminUser->givePermissionTo(['settings.edit', 'settings.view']);
});

test('admin with settings.edit cannot upload manual core upgrades', function () {
    $zip = UploadedFile::fake()->create('upgrade.zip', 100, 'application/zip');

    $this->actingAs($this->adminUser)
        ->post(route('admin.core-upgrades.upload'), [
            'upgrade_file' => $zip,
            'create_backup' => 0,
        ])
        ->assertForbidden();
});

test('superadmin can access manual core upgrade upload endpoint', function () {
    $zip = UploadedFile::fake()->create('upgrade.zip', 100, 'application/zip');

    $response = $this->actingAs($this->superadminUser)
        ->post(route('admin.core-upgrades.upload'), [
            'upgrade_file' => $zip,
            'create_backup' => 0,
        ]);

    expect($response->status())->not->toBe(403);
});

test('admin with settings.edit cannot open or operate core upgrades', function (string $method, string $uri, array $payload) {
    $this->actingAs($this->adminUser)
        ->call($method, $uri, $payload)
        ->assertForbidden();
})->with([
    'page' => ['GET', '/admin/settings/core-upgrades', []],
    'check' => ['POST', '/admin/settings/core-upgrades/check', []],
    'upgrade' => ['POST', '/admin/settings/core-upgrades/upgrade', ['version' => '1.2.3']],
    'backup' => ['POST', '/admin/settings/core-upgrades/backup', ['backup_type' => 'core']],
    'download' => ['GET', '/admin/settings/core-upgrades/download/backup.zip', []],
    'restore' => ['POST', '/admin/settings/core-upgrades/restore', ['backup_file' => 'backup.zip']],
    'delete backup' => ['POST', '/admin/settings/core-upgrades/delete-backup', ['backup_file' => 'backup.zip']],
    'update status' => ['GET', '/admin/settings/core-upgrades/update-status', []],
]);

test('superadmin can open the core upgrades page', function () {
    $this->actingAs($this->superadminUser)
        ->get(route('admin.core-upgrades.index'))
        ->assertOk()
        ->assertSee(__('Manual Upload'))
        ->assertSee(__('Upload & Upgrade'));
});

test('core upgrades menu is hidden from admin and visible to superadmin', function () {
    $href = route('admin.core-upgrades.index');

    $this->actingAs($this->adminUser)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertDontSee($href, false);

    $this->actingAs($this->superadminUser)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee($href, false);
});
