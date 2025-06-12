<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Post;

class PostService
{
    /**
     * Get posts with filters
     *
     * @param array $filters
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getPosts(array $filters = [])
    {
        // Set default post type if not provided.
        if (! isset($filters['post_type'])) {
            $filters['post_type'] = 'post';
        }

        // Create base query with post type filter.
        $query = Post::where('post_type', $filters['post_type'])
            ->with(['user', 'terms']);

        // Handle category filter separately.
        if (isset($filters['category']) && ! empty($filters['category'])) {
            $query->filterByCategory($filters['category']);
            unset($filters['category']); // Remove to prevent double filtering
        }

        // Handle tag filter separately.
        if (isset($filters['tag']) && ! empty($filters['tag'])) {
            $query->filterByTag($filters['tag']);
            unset($filters['tag']); // Remove to prevent double filtering
        }

        $query = $query->applyFilters($filters);
        return $query->paginateData([
            'per_page' => config('settings.default_pagination') ?? 10,
        ]);
    }

    /**
     * Get a post by ID.
     */
    public function getPostById(?int $id, ?string $postType = null): ?Post
    {
        if (empty($id)) {
            return null;
        }

        $query = Post::query();

        if ($postType) {
            $query->where('post_type', $postType)
                ->with(['user', 'terms']);
        }

        return $query->findOrFail($id);
    }
}
