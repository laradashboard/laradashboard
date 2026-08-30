<?php

declare(strict_types=1);

use App\Services\BackupService;
use Illuminate\Support\Facades\File;

function backupServiceTestPath(string $relative): string
{
    return storage_path('app/'.$relative);
}

function createMinimalBackupZip(string $path): void
{
    File::ensureDirectoryExists(dirname($path));

    $zip = new ZipArchive();
    $opened = $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    expect($opened)->toBeTrue();
    $zip->addFromString('readme.txt', 'safe backup fixture');
    $zip->close();
}

function recordBackupTestPath(string $path): string
{
    $paths = test()->backupTestPaths ?? [];
    $paths[] = $path;
    test()->backupTestPaths = $paths;

    return $path;
}

beforeEach(function () {
    $this->backupTestPaths = [];
    File::ensureDirectoryExists(storage_path('app/core-backups'));
    $this->backupService = app(BackupService::class);
});

afterEach(function () {
    foreach ($this->backupTestPaths ?? [] as $path) {
        if (is_link($path) || File::exists($path)) {
            File::delete($path);
        }
    }

    $restoreDir = storage_path('app/core-upgrades-temp/restore');
    if (File::isDirectory($restoreDir)) {
        File::deleteDirectory($restoreDir);
    }
});

test('a legitimate backup filename resolves inside the backup directory', function () {
    $filename = 'ld-cwe22-backup.zip';
    $path = recordBackupTestPath(storage_path('app/core-backups/'.$filename));
    createMinimalBackupZip($path);

    $resolved = $this->backupService->resolveBackupFile($filename);
    $backupDirectory = realpath(storage_path('app/core-backups'));

    expect($resolved)->toBe(realpath($path))
        ->and($backupDirectory)->not->toBeFalse()
        ->and($resolved)->not->toBe($backupDirectory);
});

test('generated backup filenames resolve successfully', function () {
    $filename = 'backup-core_with_modules-with-vendor-1.2.2-2026-08-27_105300.zip';
    $path = recordBackupTestPath(storage_path('app/core-backups/'.$filename));
    createMinimalBackupZip($path);

    expect($this->backupService->resolveBackupFile($filename))->toBe(realpath($path));
});

test('path traversal filenames are rejected', function (string $filename) {
    $outside = recordBackupTestPath(backupServiceTestPath('ld-cwe22-outside-traversal.zip'));
    createMinimalBackupZip($outside);

    expect($this->backupService->resolveBackupFile($filename))->toBeNull();
})->with([
    ['../evil.zip'],
    ['../../evil.zip'],
    ['../../../evil.zip'],
    ['..\\evil.zip'],
    ['foo/bar.zip'],
    ['foo\\bar.zip'],
    ['foo/../evil.zip'],
    ['/etc/passwd'],
    ['C:\\Windows\\win.ini'],
]);

test('an absolute path is rejected as a backup filename', function () {
    $outside = recordBackupTestPath(backupServiceTestPath('ld-cwe22-absolute.zip'));
    createMinimalBackupZip($outside);

    expect($this->backupService->resolveBackupFile($outside))->toBeNull()
        ->and($this->backupService->resolveBackupFile('/etc/passwd'))->toBeNull();
});

test('a missing backup filename does not resolve', function () {
    expect($this->backupService->resolveBackupFile('does-not-exist.zip'))->toBeNull();
});

test('a symlink that escapes the backup directory is rejected', function () {
    $outside = recordBackupTestPath(backupServiceTestPath('ld-cwe22-symlink-target.zip'));
    createMinimalBackupZip($outside);

    $link = recordBackupTestPath(storage_path('app/core-backups/ld-cwe22-evil-link.zip'));
    if (is_link($link) || File::exists($link)) {
        File::delete($link);
    }

    $created = @symlink($outside, $link);
    if ($created === false) {
        test()->markTestSkipped('Symlinks are not supported in this environment.');
    }

    expect($this->backupService->resolveBackupFile('ld-cwe22-evil-link.zip'))->toBeNull();
});

test('deleteBackup removes a legitimate backup inside core-backups', function () {
    $filename = 'ld-cwe22-delete-me.zip';
    $path = recordBackupTestPath(storage_path('app/core-backups/'.$filename));
    createMinimalBackupZip($path);

    expect($this->backupService->deleteBackup($filename))->toBeTrue()
        ->and(File::exists($path))->toBeFalse();
});

test('deleteBackup cannot remove a file outside the backup directory', function () {
    $outside = recordBackupTestPath(backupServiceTestPath('ld-cwe22-must-not-delete.zip'));
    File::put($outside, 'keep me');

    expect($this->backupService->deleteBackup('../ld-cwe22-must-not-delete.zip'))->toBeFalse()
        ->and($this->backupService->deleteBackup('../../ld-cwe22-must-not-delete.zip'))->toBeFalse()
        ->and($this->backupService->deleteBackup($outside))->toBeFalse()
        ->and(File::exists($outside))->toBeTrue();
});

test('restoreFromBackup restores a legitimate archive inside core-backups', function () {
    $filename = 'ld-cwe22-restore-ok.zip';
    $path = recordBackupTestPath(storage_path('app/core-backups/'.$filename));
    createMinimalBackupZip($path);

    expect($this->backupService->restoreFromBackup($path))->toBeTrue();
});

test('restoreFromBackup rejects an archive outside core-backups', function () {
    $outside = recordBackupTestPath(backupServiceTestPath('ld-cwe22-evil-restore.zip'));
    createMinimalBackupZip($outside);

    expect($this->backupService->restoreFromBackup($outside))->toBeFalse()
        ->and($this->backupService->restoreFromBackup($this->backupService->getBackupPath().'/../ld-cwe22-evil-restore.zip'))->toBeFalse();
});
