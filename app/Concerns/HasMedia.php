<?php

namespace App\Concerns;

use App\Models\Media;

trait HasMedia
{
    use InteractsWithMedia;

    /**
     * Register media collections and conversions
     */
    public function registerMediaCollections(): void
    {
        // Default implementation - can be overridden in models
        $this->addMediaCollection('default');
    }

    /**
     * Register media conversions
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        // Default conversions - can be overridden in models
        $this->addMediaConversion('thumb')
            ->width(200)
            ->height(200)
            ->sharpen(10);

        $this->addMediaConversion('medium')
            ->width(500)
            ->height(500);

        $this->addMediaConversion('large')
            ->width(1000)
            ->height(1000);
    }

    /**
     * Get the URL of the media in the specified collection
     */
    public function getMediaUrl(string $collection = 'default', string $conversion = ''): ?string
    {
        $media = $this->getFirstMedia($collection);

        if (! $media) {
            return null;
        }

        return $conversion ? $media->getUrl($conversion) : $media->getUrl();
    }

    /**
     * Get all media URLs for a collection
     */
    public function getAllMediaUrls(string $collection = 'default'): array
    {
        return $this->getMedia($collection)->map(function ($media) {
            return [
                'original' => $media->getUrl(),
                'thumb' => $media->getUrl('thumb'),
                'medium' => $media->getUrl('medium'),
                'large' => $media->getUrl('large'),
            ];
        })->toArray();
    }
}
