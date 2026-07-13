<?php

declare(strict_types=1);

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Role;
use App\Models\User;
use App\Services\BackupService;
use App\Services\CoreUpgradeService;
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

test('upgrade in demo mode runs without creating a backup', function () {
    config(['app.demo_mode' => true]);

    // BackupService::createBackup() must NOT be called in demo mode —
    // backup archives expose the full source tree to demo visitors.
    $backupSpy = $this->mock(BackupService::class);
    $backupSpy->shouldNotReceive('createBackup');

    $upgradeMock = $this->mock(CoreUpgradeService::class);
    $upgradeMock->shouldReceive('performUpgrade')
        ->once()
        ->with('1.2.3', null)
        ->andReturn(['success' => true, 'message' => 'Upgrade complete.']);

    $response = $this->actingAs($this->admin)
        ->postJson(route('admin.core-upgrades.upgrade'), [
            'version' => '1.2.3',
            'create_backup' => true,
        ]);

    $response->assertOk()->assertJson(['success' => true]);
});

test('upgrade outside demo mode still creates a backup when requested', function () {
    config(['app.demo_mode' => false]);

    $backupMock = $this->mock(BackupService::class);
    $backupMock->shouldReceive('createBackup')
        ->once()
        ->andReturn('/tmp/fake-backup.zip');

    $upgradeMock = $this->mock(CoreUpgradeService::class);
    $upgradeMock->shouldReceive('performUpgrade')
        ->once()
        ->with('1.2.3', '/tmp/fake-backup.zip')
        ->andReturn(['success' => true, 'message' => 'Upgrade complete.']);

    $response = $this->actingAs($this->admin)
        ->postJson(route('admin.core-upgrades.upgrade'), [
            'version' => '1.2.3',
            'create_backup' => true,
        ]);

    $response->assertOk()->assertJson(['success' => true]);
});
