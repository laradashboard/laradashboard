<?php

declare(strict_types=1);

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Role;
use Illuminate\Support\Facades\File;

function createCoreUpgradeTestZip(string $path): void
{
    File::ensureDirectoryExists(dirname($path));

    $zip = new ZipArchive();
    $opened = $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    expect($opened)->toBeTrue();
    $zip->addFromString('readme.txt', 'safe backup fixture');
    $zip->close();
}

function recordCoreUpgradeTestPath(string $path): string
{
    $paths = test()->coreUpgradeTestPaths ?? [];
    $paths[] = $path;
    test()->coreUpgradeTestPaths = $paths;

    return $path;
}

beforeEach(function () {
    $this->withoutMiddleware(VerifyCsrfToken::class);
    config(['app.demo_mode' => false]);

    $this->coreUpgradeTestPaths = [];
    File::ensureDirectoryExists(storage_path('app/core-backups'));

    Role::firstOrCreate(['name' => 'SettingsEditor', 'guard_name' => 'web']);
    $this->settingsEditor = $this->createUserWithRole('SettingsEditor', [
        'settings.view',
        'settings.edit',
    ]);
});

afterEach(function () {
    foreach ($this->coreUpgradeTestPaths ?? [] as $path) {
        if (is_link($path) || File::exists($path)) {
            File::delete($path);
        }
    }

    $restoreDir = storage_path('app/core-upgrades-temp/restore');
    if (File::isDirectory($restoreDir)) {
        File::deleteDirectory($restoreDir);
    }
});

test('a user with settings.edit can download a legitimate backup', function () {
    $filename = 'ld-cwe22-http-download.zip';
    $path = recordCoreUpgradeTestPath(storage_path('app/core-backups/'.$filename));
    createCoreUpgradeTestZip($path);

    $response = $this->actingAs($this->settingsEditor)
        ->get(route('admin.core-upgrades.download', ['filename' => $filename]));

    $response->assertOk();
    $response->assertDownload($filename);
});

test('a user with settings.edit cannot download a file outside the backup directory', function (string $filename) {
    $outside = recordCoreUpgradeTestPath(storage_path('app/ld-cwe22-http-outside-download.zip'));
    createCoreUpgradeTestZip($outside);

    $response = $this->actingAs($this->settingsEditor)
        ->get('/admin/settings/core-upgrades/download/'.$filename);

    $response->assertNotFound();
    expect($response->getContent())->not->toContain(storage_path())
        ->and(File::exists($outside))->toBeTrue();
})->with([
    ['../ld-cwe22-http-outside-download.zip'],
    ['..%2Fld-cwe22-http-outside-download.zip'],
    ['foo%2Fbar.zip'],
]);

test('a user with settings.edit can delete a legitimate backup', function () {
    $filename = 'ld-cwe22-http-delete.zip';
    $path = recordCoreUpgradeTestPath(storage_path('app/core-backups/'.$filename));
    createCoreUpgradeTestZip($path);

    $response = $this->actingAs($this->settingsEditor)
        ->from(route('admin.core-upgrades.index'))
        ->post(route('admin.core-upgrades.delete-backup'), [
            'backup_file' => $filename,
        ]);

    $response->assertRedirect(route('admin.core-upgrades.index'));
    $response->assertSessionHas('success');
    expect(File::exists($path))->toBeFalse();
});

test('a user with settings.edit cannot delete a file outside the backup directory', function (string $filename) {
    $outside = recordCoreUpgradeTestPath(storage_path('app/ld-cwe22-http-outside-delete.zip'));
    File::put($outside, 'keep me');

    $response = $this->actingAs($this->settingsEditor)
        ->from(route('admin.core-upgrades.index'))
        ->post(route('admin.core-upgrades.delete-backup'), [
            'backup_file' => $filename,
        ]);

    $response->assertRedirect(route('admin.core-upgrades.index'));
    $response->assertSessionHasErrors('backup_file');
    expect(session('errors')->first('backup_file'))->not->toContain(storage_path())
        ->and(File::exists($outside))->toBeTrue();
})->with([
    ['../ld-cwe22-http-outside-delete.zip'],
    ['../../ld-cwe22-http-outside-delete.zip'],
    ['../../../ld-cwe22-http-outside-delete.zip'],
    ['foo/bar.zip'],
    ['foo/../ld-cwe22-http-outside-delete.zip'],
    ['/etc/passwd'],
]);

test('a user with settings.edit can restore a legitimate backup', function () {
    $filename = 'ld-cwe22-http-restore.zip';
    $path = recordCoreUpgradeTestPath(storage_path('app/core-backups/'.$filename));
    createCoreUpgradeTestZip($path);

    $response = $this->actingAs($this->settingsEditor)
        ->postJson(route('admin.core-upgrades.restore'), [
            'backup_file' => $filename,
        ]);

    $response->assertOk()->assertJson([
        'success' => true,
        'message' => __('Successfully restored from backup.'),
    ]);
    expect($response->getContent())->not->toContain(storage_path());
});

test('a user with settings.edit cannot restore an archive outside the backup directory', function (string $filename) {
    $outside = recordCoreUpgradeTestPath(storage_path('app/ld-cwe22-http-outside-restore.zip'));
    createCoreUpgradeTestZip($outside);

    $response = $this->actingAs($this->settingsEditor)
        ->postJson(route('admin.core-upgrades.restore'), [
            'backup_file' => $filename,
        ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors('backup_file');
    expect($response->getContent())->not->toContain(storage_path())
        ->and($response->getContent())->not->toContain($outside);
})->with([
    ['../ld-cwe22-http-outside-restore.zip'],
    ['../../ld-cwe22-http-outside-restore.zip'],
    ['../../../ld-cwe22-http-outside-restore.zip'],
    ['foo/bar.zip'],
    ['foo/../ld-cwe22-http-outside-restore.zip'],
    ['/etc/passwd'],
]);

test('restore rejects a symlink that escapes the backup directory', function () {
    $outside = recordCoreUpgradeTestPath(storage_path('app/ld-cwe22-http-symlink-target.zip'));
    createCoreUpgradeTestZip($outside);

    $linkName = 'ld-cwe22-http-evil-link.zip';
    $link = recordCoreUpgradeTestPath(storage_path('app/core-backups/'.$linkName));
    if (is_link($link) || File::exists($link)) {
        File::delete($link);
    }

    $created = @symlink($outside, $link);
    if ($created === false) {
        test()->markTestSkipped('Symlinks are not supported in this environment.');
    }

    $response = $this->actingAs($this->settingsEditor)
        ->postJson(route('admin.core-upgrades.restore'), [
            'backup_file' => $linkName,
        ]);

    $response->assertNotFound();
    $response->assertJson([
        'success' => false,
        'message' => __('Backup file not found.'),
    ]);
    expect($response->getContent())->not->toContain(storage_path());
});
