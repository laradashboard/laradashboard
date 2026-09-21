<?php

declare(strict_types=1);

use App\Models\Media;
use App\Services\MediaLibraryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

pest()->use(RefreshDatabase::class);

test('mcp base64 fallback limit does not exceed multipart upload limit', function () {
    expect(MediaLibraryService::MCP_BASE64_FALLBACK_MAX_BYTES)
        ->toBeLessThanOrEqual(MediaLibraryService::MCP_MAX_UPLOAD_BYTES);
});

test('upload from base64 stores image in media library', function () {
    $service = app(MediaLibraryService::class);
    $pngBase64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

    $media = $service->uploadFromBase64(
        filename: 'unit-test-hero.png',
        mimeType: 'image/png',
        contentBase64: $pngBase64,
        title: 'Unit Test Hero',
        altText: 'Alt text',
    );

    expect($media)->toBeInstanceOf(Media::class);
    expect($media->mime_type)->toBe('image/png');
    expect($media->custom_properties['alt_text'] ?? null)->toBe('Alt text');

    $formatted = $service->formatMediaForMcp($media);

    expect($formatted)->toHaveKeys([
        'id',
        'name',
        'file_name',
        'mime_type',
        'size',
        'human_readable_size',
        'url',
        'created_at',
        'serve_ok',
        'http_status',
        'bytes',
        'mime',
    ]);
    expect($formatted['serve_ok'])->toBeTrue();
    expect($formatted['created_at'])->toBeString();
});

test('upload from base64 rejects payloads above fallback limit', function () {
    $service = app(MediaLibraryService::class);
    $tooLarge = str_repeat('a', MediaLibraryService::MCP_BASE64_FALLBACK_MAX_BYTES + 1);

    $service->uploadFromBase64(
        filename: 'too-large.jpg',
        mimeType: 'image/jpeg',
        contentBase64: base64_encode($tooLarge),
    );
})->throws(ValidationException::class, 'create-media-upload');

test('multipart upload rejects corrupted jpeg bytes', function () {
    $service = app(MediaLibraryService::class);
    $tmp = tempnam(sys_get_temp_dir(), 'ld_bad_jpeg_');
    file_put_contents($tmp, "\xFF\xD8\xFF\xE0\x00\x10JFIF\x00\x01\x01\x01\x00H\x00H\x00\x00not-a-real-image");
    $file = new UploadedFile($tmp, 'bad.jpg', 'image/jpeg', null, true);

    $service->uploadStandaloneFile($file);
})->throws(ValidationException::class, 'invalid or corrupted');

test('upload from base64 rejects invalid mime type', function () {
    $service = app(MediaLibraryService::class);

    $service->uploadFromBase64(
        filename: 'bad.exe',
        mimeType: 'application/x-msdownload',
        contentBase64: base64_encode('fake'),
    );
})->throws(ValidationException::class);
