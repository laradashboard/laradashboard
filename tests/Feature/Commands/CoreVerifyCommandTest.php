<?php

declare(strict_types=1);

use App\Services\BackupService;
use App\Services\CoreUpgradeService;
use App\Services\UpgradeSnapshotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;

pest()->use(RefreshDatabase::class);

test('core verify command passes with matching snapshot', function () {
    $service = app(UpgradeSnapshotService::class);
    $snapshot = $service->capture();
    $path = storage_path('app/upgrade-snapshots/verify-test.json');
    File::ensureDirectoryExists(dirname($path));
    File::put($path, json_encode($snapshot));

    $this->artisan('core:verify', [
        '--expected-version' => $snapshot['core_version'],
        '--compare-with' => $path,
    ])->assertSuccessful();

    File::delete($path);
});

test('core verify command fails when module state changes', function () {
    $path = storage_path('app/upgrade-snapshots/mismatch-test.json');
    File::ensureDirectoryExists(dirname($path));

    File::put($path, json_encode([
        'core_version' => '1.0.0',
        'modules' => [
            'removed-module' => [
                'name' => 'RemovedModule',
                'folder' => 'RemovedModule',
                'version' => '1.0.0',
                'enabled' => true,
            ],
        ],
        'migration_count' => 999,
    ]));

    $this->artisan('core:verify', [
        '--compare-with' => $path,
    ])->assertFailed();

    File::delete($path);
});

test('core upgrade command delegates to upgrade service', function () {
    $backupMock = $this->mock(BackupService::class);
    $backupMock->shouldReceive('createBackup')
        ->once()
        ->andReturn('/tmp/fake-backup.zip');

    $upgradeMock = $this->mock(CoreUpgradeService::class);
    $upgradeMock->shouldReceive('performUpgrade')
        ->once()
        ->with('9.9.9', '/tmp/fake-backup.zip')
        ->andReturn(['success' => true, 'message' => 'Done']);

    $this->artisan('core:upgrade', [
        'version' => '9.9.9',
        '--force' => true,
    ])->assertSuccessful();
});
