<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\MediaObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;

/**
 * Wrapper for Spatie Media to avoid direct dependency in modules
 */
#[ObservedBy([MediaObserver::class])]
class Media extends SpatieMedia
{
    /**
     * Standalone library media can retain generated conversion flags after being
     * detached from a model, but Spatie 11.23's default accessor throws when the
     * conversion is no longer registered on the related model.
     */
    protected function previewUrl(): Attribute
    {
        return Attribute::get(function (): string {
            if (! $this->hasGeneratedConversion('preview')) {
                return '';
            }

            try {
                return $this->getUrl('preview');
            } catch (\Throwable) {
                try {
                    return $this->getUrl();
                } catch (\Throwable) {
                    return '';
                }
            }
        });
    }
}
