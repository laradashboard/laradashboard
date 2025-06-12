<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PostMeta;
use Illuminate\Support\Collection;

class PostMetaService
{
    /**
     * Get meta value for a post
     *
     * @param  mixed  $default
     * @return mixed
     */
    public function getMeta(int $postId, string $key, $default = null)
    {
        $meta = PostMeta::where('post_id', $postId)
            ->where('meta_key', $key)
            ->first();

        return $meta ? $meta->meta_value : $default;
    }

    /**
     * Set meta value for a post
     *
     * @param  mixed  $value
     * @param  mixed  $defaultValue
     */
    public function setMeta(int $postId, string $key, $value, string $type = 'input', $defaultValue = null): PostMeta
    {
        return PostMeta::updateOrCreate(
            [
                'post_id' => $postId,
                'meta_key' => $key,
            ],
            [
                'meta_value' => $value,
                'type' => $type,
                'default_value' => $defaultValue,
            ]
        );
    }

    /**
     * Delete meta for a post
     */
    public function deleteMeta(int $postId, string $key): bool
    {
        return PostMeta::where('post_id', $postId)
            ->where('meta_key', $key)
            ->delete() > 0;
    }

    /**
     * Get all meta for a post
     */
    public function getAllMeta(int $postId): Collection
    {
        return PostMeta::where('post_id', $postId)->get();
    }

    /**
     * Get all meta for a post as array with additional info
     */
    public function getAllMetaAsArray(int $postId): array
    {
        return PostMeta::where('post_id', $postId)
            ->get()
            ->mapWithKeys(function ($meta) {
                return [
                    $meta->meta_key => [
                        'value' => $meta->meta_value,
                        'type' => $meta->type,
                        'default_value' => $meta->default_value,
                    ],
                ];
            })
            ->toArray();
    }

    /**
     * Get all meta for a post as simple key-value pairs
     */
    public function getAllMetaValues(int $postId): array
    {
        return PostMeta::where('post_id', $postId)
            ->pluck('meta_value', 'meta_key')
            ->toArray();
    }

    /**
     * Update multiple meta values for a post
     *
     * @param  array  $metaData  Array of key => value pairs or key => [value, type, default] arrays
     */
    public function updateMultipleMeta(int $postId, array $metaData): void
    {
        foreach ($metaData as $key => $data) {
            if (! empty($key)) {
                if (is_array($data)) {
                    $this->setMeta(
                        $postId,
                        $key,
                        $data['value'] ?? '',
                        $data['type'] ?? 'input',
                        $data['default_value'] ?? null
                    );
                } else {
                    $this->setMeta($postId, $key, $data);
                }
            }
        }
    }

    /**
     * Delete multiple meta values for a post
     *
     * @return int Number of deleted records
     */
    public function deleteMultipleMeta(int $postId, array $keys): int
    {
        return PostMeta::where('post_id', $postId)
            ->whereIn('meta_key', $keys)
            ->delete();
    }

    /**
     * Sync meta data - delete existing and create new
     */
    public function syncMeta(int $postId, array $metaData): void
    {
        // Delete all existing meta for this post
        PostMeta::where('post_id', $postId)->delete();

        // Create new meta
        $this->updateMultipleMeta($postId, $metaData);
    }
}
