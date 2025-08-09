<?php

namespace App\Concerns;

use App\Contacts\HasMediaInterface;
use Illuminate\Support\Collection;

/**
 * Custom implementation of Spatie's InteractsWithMedia trait
 * This ensures method compatibility with our HasMediaInterface
 */
trait InteractsWithMedia
{
    use \Spatie\MediaLibrary\InteractsWithMedia {
        clearMediaCollection as spatieMediaClearMediaCollection;
        clearMediaCollectionExcept as spatieMediaClearMediaCollectionExcept;
    }

    /**
     * Override clearMediaCollection to return HasMediaInterface instead of Spatie\MediaLibrary\HasMedia
     */
    public function clearMediaCollection(string $collectionName = 'default'): HasMediaInterface
    {
        $this->spatieMediaClearMediaCollection($collectionName);

        return $this;
    }

    /**
     * Override clearMediaCollectionExcept to return HasMediaInterface instead of Spatie\MediaLibrary\HasMedia
     */
    public function clearMediaCollectionExcept(string $collectionName = 'default', array|Collection $excludedMedia = []): HasMediaInterface
    {
        $this->spatieMediaClearMediaCollectionExcept($collectionName, $excludedMedia);

        return $this;
    }
}
