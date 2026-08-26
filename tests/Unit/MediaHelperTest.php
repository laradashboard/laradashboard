<?php

declare(strict_types=1);

use App\Support\Helper\MediaHelper;
use Illuminate\Http\UploadedFile;

test('can get upload limits', function () {
    $limits = MediaHelper::getUploadLimits();

    expect($limits)->toHaveKey('upload_max_filesize');
    expect($limits)->toHaveKey('post_max_size');
    expect($limits)->toHaveKey('effective_max_filesize');
    expect($limits)->toHaveKey('max_file_uploads');
    expect($limits)->toHaveKey('allowed_mime_types');
    expect($limits)->toHaveKey('allowed_extensions');
    expect($limits['allowed_extensions'])->toContain('svg');
    expect($limits['allowed_mime_types'])->toContain('image/svg+xml');

    $expected = min($limits['upload_max_filesize'], $limits['post_max_size']);
    expect($limits['effective_max_filesize'])->toEqual($expected);
});

test('can parse php size strings', function () {
    expect(MediaHelper::parseSize('1K'))->toEqual(1024);
    expect(MediaHelper::parseSize('1M'))->toEqual(1024 * 1024);
    expect(MediaHelper::parseSize('1G'))->toEqual(1024 * 1024 * 1024);
    expect(MediaHelper::parseSize('2K'))->toEqual(2048);
    expect(MediaHelper::parseSize('10M'))->toEqual(10 * 1024 * 1024);
});

test('format file size', function () {
    expect(MediaHelper::formatFileSize(1024))->toEqual('1024 B');
    expect(MediaHelper::formatFileSize(2048))->toEqual('2 KB');
    expect(MediaHelper::formatFileSize(1024 * 1024))->toEqual('1024 KB');
    expect(MediaHelper::formatFileSize(1024 * 1024 * 1024))->toEqual('1024 MB');
    expect(MediaHelper::formatFileSize(500))->toEqual('500 B');
});

test('extension allowlist rejects executable uploads case-insensitively', function () {
    $php8 = UploadedFile::fake()->create('shell.php8', 10);
    $phar = UploadedFile::fake()->create('shell.phar', 10);
    $phtml = UploadedFile::fake()->create('shell.phtml', 10);
    $svg = UploadedFile::fake()->create('IMAGE.SVG', 10);

    expect(MediaHelper::isDangerousFile($php8))->toBeTrue();
    expect(MediaHelper::isDangerousFile($phar))->toBeTrue();
    expect(MediaHelper::isDangerousFile($phtml))->toBeTrue();
    expect(MediaHelper::hasAllowedExtension($svg))->toBeTrue();
    expect(MediaHelper::isSvgFile($svg))->toBeTrue();
});

test('mime restrictions are not bypassed when demo mode is off', function () {
    config(['app.demo_mode' => false]);

    expect(MediaHelper::isAllowedMimeType('text/x-php'))->toBeFalse();
    expect(MediaHelper::isAllowedInDemoMode('application/x-php'))->toBeFalse();
    expect(MediaHelper::isAllowedMimeType('image/svg+xml'))->toBeTrue();
    expect(MediaHelper::isAllowedMimeType('image/png'))->toBeTrue();
});

test('unique filenames strip path traversal and keep a safe extension', function () {
    $name = MediaHelper::generateUniqueFilename('../../etc/passwd.svg');

    expect($name)->not->toContain('..');
    expect($name)->not->toContain('/');
    expect($name)->not->toContain('\\');
    expect($name)->toEndWith('.svg');
});
