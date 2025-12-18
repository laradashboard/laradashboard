<?php

declare(strict_types=1);

namespace App\Models;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Module model representing a filesystem-based module.
 *
 * @property string $id
 * @property string $name
 * @property string $title
 * @property string $description
 * @property string $icon
 * @property string|null $logo_image
 * @property string|null $banner_image
 * @property string $version
 * @property string|null $author
 * @property string|null $author_url
 * @property string|null $documentation_url
 * @property array $tags
 * @property bool $status
 * @property string|null $category
 * @property int $priority
 */
class Module implements Arrayable, ArrayAccess
{
    public string $id;

    public string $name;

    public string $title = '';

    public string $description = '';

    public string $icon = 'lucide:box';

    public ?string $logo_image = null;

    public ?string $banner_image = null;

    public string $version = '1.0.0';

    public ?string $author = null;

    public ?string $author_url = null;

    public ?string $documentation_url = null;

    public array $tags = [];

    public bool $status = false;

    public ?string $category = null;

    public int $priority = 0;

    public function __construct(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }

        if ($this->id === '') {
            $this->id = $this->name;
        }

        $this->status = $attributes['status'] ?? false;
    }

    /**
     * Check if module has a logo image.
     */
    public function hasLogoImage(): bool
    {
        return ! empty($this->logo_image);
    }

    /**
     * Check if module has a banner image.
     */
    public function hasBannerImage(): bool
    {
        return ! empty($this->banner_image);
    }

    /**
     * Get the logo URL (handles both relative and absolute paths).
     */
    public function getLogoUrl(): ?string
    {
        if (! $this->hasLogoImage()) {
            return null;
        }

        // If it's already a URL, return as-is
        if (str_starts_with($this->logo_image, 'http://') || str_starts_with($this->logo_image, 'https://')) {
            return $this->logo_image;
        }

        // Otherwise, treat as a path relative to module's assets
        return asset("build-{$this->name}/{$this->logo_image}");
    }

    /**
     * Get the banner URL (handles both relative and absolute paths).
     */
    public function getBannerUrl(): ?string
    {
        if (! $this->hasBannerImage()) {
            return null;
        }

        // If it's already a URL, return as-is
        if (str_starts_with($this->banner_image, 'http://') || str_starts_with($this->banner_image, 'https://')) {
            return $this->banner_image;
        }

        // Otherwise, treat as a path relative to module's assets
        return asset("build-{$this->name}/{$this->banner_image}");
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }

    /**
     * Get attributes as array for compatibility with datatable.
     *
     * @return array<string, mixed>
     */
    public function getAttributes(): array
    {
        return $this->toArray();
    }

    /**
     * ArrayAccess: Check if offset exists.
     */
    public function offsetExists(mixed $offset): bool
    {
        return property_exists($this, $offset);
    }

    /**
     * ArrayAccess: Get value at offset.
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->{$offset} ?? null;
    }

    /**
     * ArrayAccess: Set value at offset.
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->{$offset} = $value;
    }

    /**
     * ArrayAccess: Unset value at offset.
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->{$offset});
    }
}
