<?php

namespace App\Contacts;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use App\Models\Media;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\MediaCollections\FileAdder;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Custom interface to replace Spatie's HasMedia interface
 * This avoids direct dependency on Spatie in modules
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
interface HasMediaInterface
{
    public function media(): MorphMany;

    public function addMedia(string|UploadedFile $file): FileAdder;

    public function copyMedia(string|UploadedFile $file): FileAdder;

    public function hasMedia(string $collectionName = ''): bool;

    public function getMedia(string $collectionName = 'default', array|callable $filters = []): Collection;

    public function clearMediaCollection(string $collectionName = 'default'): HasMediaInterface;

    public function clearMediaCollectionExcept(string $collectionName = 'default', array|Collection $excludedMedia = []): HasMediaInterface;

    public function shouldDeletePreservingMedia(): bool;

    public function loadMedia(string $collectionName);

    public function addMediaConversion(string $name): Conversion;

    public function registerMediaConversions(?Media $media = null): void;

    public function registerMediaCollections(): void;

    public function registerAllMediaConversions(): void;

    public function getMediaModel(): string;
}
