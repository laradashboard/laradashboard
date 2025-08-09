<?php

declare(strict_types=1);

namespace App\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait HasUniqueSlug
{
    /**
     * Generate a unique slug for the given model and column
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     */
    public function generateUniqueSlug(Model $model, string $columnName = 'slug', string $separator = '-', ?string $baseSlug = null, int $counter = 0): string
    {
        // If no base slug provided, generate from the title/name field
        if ($baseSlug === null) {
            $sourceField = $this->getSlugSourceField($model);
            $baseSlug = Str::slug($model->{$sourceField}, $separator);
        }

        // Generate the slug with counter if needed
        $slug = $counter === 0 ? $baseSlug : $baseSlug . $separator . $counter;

        // Check if slug exists (excluding current model if updating)
        $query = $model->newQuery()->where($columnName, $slug);

        // If updating existing model, exclude it from the check
        if ($model->exists && $model->getKey()) {
            $query->where($model->getKeyName(), '!=', $model->getKey());
        }

        // If slug exists, increment counter and try again
        if ($query->exists()) {
            return $this->generateUniqueSlug($model, $columnName, $separator, $baseSlug, $counter + 1);
        }

        return $slug;
    }

    /**
     * Get the source field for slug generation
     * Override this method in your model if needed
     *
     * @param Model $model
     */
    protected function getSlugSourceField(Model $model): string
    {
        // Check common field names in order of preference
        $possibleFields = ['title', 'name', 'label'];

        foreach ($possibleFields as $field) {
            if (isset($model->{$field})) {
                return $field;
            }
        }

        // Fallback to 'name' if none found
        return 'name';
    }

    /**
     * Automatically generate slug before saving (if slug is empty)
     * Add this to your model's boot method or use model events
     */
    public function setSlugAttribute($value): void
    {
        // If slug is provided, use it; otherwise generate one
        if (empty($value)) {
            $this->attributes['slug'] = $this->generateUniqueSlug($this);
        } else {
            $this->attributes['slug'] = $this->generateUniqueSlug($this, 'slug', '-', Str::slug($value, '-'));
        }
    }

    /**
     * Generate unique slug for a specific label/string.
     */
    public function generateSlugFromString(string $label, string $columnName = 'slug', string $separator = '-', $model = null): string
    {
        $baseSlug = Str::slug($label, $separator);

        return $this->generateUniqueSlug($model ?? $this, $columnName, $separator, $baseSlug);
    }
}
