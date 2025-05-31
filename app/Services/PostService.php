<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Post;
use Illuminate\Pagination\LengthAwarePaginator;

class PostService
{
    /**
     * Get posts with filters
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getPosts(array $filters = []): LengthAwarePaginator
    {
        // Set default post type if not provided.
        if (!isset($filters['post_type'])) {
            $filters['post_type'] = 'post';
        }

        // Create base query with post type filter.
        $query = Post::where('post_type', $filters['post_type'])
            ->with(['user', 'terms']);

        // Handle category filter separately.
        if (isset($filters['category']) && !empty($filters['category'])) {
            $query->filterByCategory($filters['category']);
            unset($filters['category']); // Remove to prevent double filtering
        }

        return $query->applyFilters($filters)
            ->paginateData([
                'per_page' => config('settings.default_pagination') ?? 10
            ]);
    }

    /**
     * Get a post by ID.
     *
     * @param int $id
     * @param string|null $postType
     * @return Post|null
     */
    public function getPostById(int $id, ?string $postType = null): ?Post
    {
        $query = Post::query();

        if ($postType) {
            $query->where('post_type', $postType);
        }

        return $query->findOrFail($id);
    }
}
