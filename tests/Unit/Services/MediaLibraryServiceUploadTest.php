<?php

declare(strict_types=1);

use App\Models\Media;
use App\Services\MediaLibraryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

pest()->use(RefreshDatabase::class);

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
});

test('upload from base64 rejects invalid mime type', function () {
    $service = app(MediaLibraryService::class);

    $service->uploadFromBase64(
        filename: 'bad.exe',
        mimeType: 'application/x-msdownload',
        contentBase64: base64_encode('fake'),
    );
})->throws(ValidationException::class);
