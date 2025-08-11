<?php

declare(strict_types=1);

namespace App\Models;

use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;

/**
 * Wrapper for Spatie Media to avoid direct dependency in modules
 */
class Media extends SpatieMedia
{
    // Inherit all functionality from Spatie Media
    /**
     * Ensure JSON-casted attributes always return arrays.
     */
    public function getManipulationsAttribute($value): array
    {
        return $this->normalizeJsonArray($value);
    }

    public function getCustomPropertiesAttribute($value): array
    {
        return $this->normalizeJsonArray($value);
    }

    public function getGeneratedConversionsAttribute($value): array
    {
        return $this->normalizeJsonArray($value);
    }

    public function getResponsiveImagesAttribute($value): array
    {
        return $this->normalizeJsonArray($value);
    }

    /**
     * Also normalize on set to avoid persisting strings like '[]'.
     */
    public function setManipulationsAttribute($value): void
    {
        $this->attributes['manipulations'] = json_encode($this->normalizeJsonArray($value));
    }

    public function setCustomPropertiesAttribute($value): void
    {
        $this->attributes['custom_properties'] = json_encode($this->normalizeJsonArray($value));
    }

    public function setGeneratedConversionsAttribute($value): void
    {
        $this->attributes['generated_conversions'] = json_encode($this->normalizeJsonArray($value));
    }

    public function setResponsiveImagesAttribute($value): void
    {
        $this->attributes['responsive_images'] = json_encode($this->normalizeJsonArray($value));
    }

    private function normalizeJsonArray($value): array
    {
        if (is_array($value)) {
            return $value;
        }
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }
        return [];
    }
}
