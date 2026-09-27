<?php

declare(strict_types=1);

use App\Support\Security\SafeZipExtractor;
use Illuminate\Support\Facades\File;

function safeZipExtractorFixturePath(string $relative): string
{
    return storage_path('framework/testing/zip-slip/'.$relative);
}

function createZipArchive(string $path, array $entries): void
{
    File::ensureDirectoryExists(dirname($path));

    $zip = new ZipArchive();
    $opened = $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    expect($opened)->toBeTrue();

    foreach ($entries as $name => $contents) {
        $zip->addFromString($name, $contents);
    }

    $zip->close();
}

describe('SafeZipExtractor', function () {
    beforeEach(function () {
        $this->extractor = new SafeZipExtractor();
        $this->baseDir = safeZipExtractorFixturePath(uniqid('extract_', true));
        File::ensureDirectoryExists($this->baseDir);
    });

    afterEach(function () {
        if (isset($this->baseDir) && File::isDirectory($this->baseDir)) {
            File::deleteDirectory(dirname($this->baseDir));
        }
    });

    test('extracts legitimate archives into the destination directory', function () {
        $zipPath = safeZipExtractorFixturePath('legitimate.zip');
        createZipArchive($zipPath, [
            'module.json' => '{"name":"demo"}',
            'src/readme.txt' => 'hello',
        ]);

        $destination = $this->baseDir.'/out';

        expect($this->extractor->extract($zipPath, $destination))->toBeTrue()
            ->and(File::exists($destination.'/module.json'))->toBeTrue()
            ->and(File::get($destination.'/src/readme.txt'))->toBe('hello');
    });

    test('rejects zip-slip entries that traverse outside the destination', function () {
        $zipPath = safeZipExtractorFixturePath('slip.zip');
        $escapeDir = safeZipExtractorFixturePath('escape_target');
        File::ensureDirectoryExists($escapeDir);

        createZipArchive($zipPath, [
            '../../../../framework/testing/zip-slip/escape_target/PWNED.txt' => 'zip-slip-write',
        ]);

        $destination = $this->baseDir.'/sandbox';

        expect($this->extractor->extract($zipPath, $destination))->toBeFalse()
            ->and(File::exists($escapeDir.'/PWNED.txt'))->toBeFalse();
    });

    test('rejects absolute and parent-segment entry names', function (string $entryName) {
        expect($this->extractor->normalizeEntryPath($entryName))->toBeNull();
    })->with([
        '../evil.txt',
        'foo/../../secret.txt',
        '/etc/passwd',
        'C:/Windows/win.ini',
        "foo\0bar.txt",
    ]);

    test('normalizeEntryPath keeps safe relative paths', function () {
        expect($this->extractor->normalizeEntryPath('module/src/File.php'))
            ->toBe('module'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'File.php');
    });
});
