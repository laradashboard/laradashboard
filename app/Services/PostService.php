<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Services\Content\PostStatusAuthorizationService;
use Illuminate\Support\Facades\Auth;

class PostService
{
    public function __construct(
        private readonly PostStatusAuthorizationService $postStatusAuthorizationService,
    ) {
    }

    /**
     * Get posts with filters
     *
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

    /**
     * Get paginated posts with filters
     */
    public function getPaginatedPosts(array $filters = [], int $perPage = 10)
    {
        // Set default post type if not provided.
        if (! isset($filters['post_type'])) {
            $filters['post_type'] = 'post';
        }

        // Create base query with post type filter.
        $query = Post::where('post_type', $filters['post_type'])
            ->with(['author', 'terms']);

        // Handle category filter separately.
        if (isset($filters['category']) && ! empty($filters['category'])) {
            $query->filterByCategory($filters['category']);
            unset($filters['category']);
        }

        // Handle tag filter separately.
        if (isset($filters['tag']) && ! empty($filters['tag'])) {
            $query->filterByTag($filters['tag']);
            unset($filters['tag']);
        }

        $query = $query->applyFilters($filters);

        return $query->paginate($perPage);
    }

    /**
     * Create a new post
     */
    public function createPost(array $data): Post
    {
        $user = Auth::user();

        if ($user !== null && isset($data['status'])) {
            $data['status'] = $this->postStatusAuthorizationService->resolveStatus(
                $user,
                null,
                (string) $data['status'],
            );
        }

        $post = Post::create([
            'title' => $data['title'],
            'slug' => $data['slug'] ?? str()->slug($data['title']),
            'content' => $data['content'] ?? '',
            'design_json' => $data['design_json'] ?? null,
            'excerpt' => $data['excerpt'] ?? '',
            'post_type' => $data['post_type'] ?? 'post',
            'status' => $data['status'] ?? PostStatus::DRAFT->value,
            'published_at' => $data['published_at'] ?? null,
            'user_id' => $data['user_id'] ?? $data['author_id'] ?? null,
        ]);

        // Handle featured image upload to media library.
        if (isset($data['featured_image']) && $data['featured_image'] instanceof \Illuminate\Http\UploadedFile) {
            $post->addMediaFromRequest('featured_image')
                ->toMediaCollection('featured');
        }

        // Sync terms if provided
        if (isset($data['terms']) && ! empty($data['terms'])) {
            $post->terms()->sync($data['terms']);
        }

        // Handle post meta if provided
        if (isset($data['meta']) && ! empty($data['meta'])) {
            foreach ($data['meta'] as $key => $value) {
                $post->postMeta()->updateOrCreate(
                    ['meta_key' => $key],
                    ['meta_value' => $value]
                );
            }
        }

        return $post->load(['author', 'terms']);
    }

    /**
     * Update an existing post
     */
    public function updatePost(Post $post, array $data): Post
    {
        $user = Auth::user();

        if ($user !== null && array_key_exists('status', $data)) {
            $data['status'] = $this->postStatusAuthorizationService->resolveStatus(
                $user,
                $post,
                (string) $data['status'],
            );
        }

        $updateData = [
            'title' => $data['title'] ?? $post->title,
            'slug' => $data['slug'] ?? $post->slug,
            'content' => $data['content'] ?? $post->content,
            'design_json' => $data['design_json'] ?? $post->design_json,
            'excerpt' => $data['excerpt'] ?? $post->excerpt,
            'status' => $data['status'] ?? $post->status,
            'published_at' => $data['published_at'] ?? $post->published_at,
        ];

        $post->update($updateData);

        // Handle featured image upload to media library.
        if (isset($data['featured_image']) && $data['featured_image'] instanceof \Illuminate\Http\UploadedFile) {
            // Clear existing featured image.
            $post->clearMediaCollection('featured');

            // Add new featured image.
            $post->addMediaFromRequest('featured_image')
                ->toMediaCollection('featured');
        }

        // Handle featured image removal.
        if (isset($data['remove_featured_image']) && $data['remove_featured_image']) {
            $post->clearMediaCollection('featured');
        }

        // Sync terms if provided
        if (isset($data['terms'])) {
            $post->terms()->sync($data['terms']);
        }

        // Handle post meta if provided
        if (isset($data['meta']) && ! empty($data['meta'])) {
            foreach ($data['meta'] as $key => $value) {
                $post->postMeta()->updateOrCreate(
                    ['meta_key' => $key],
                    ['meta_value' => $value]
                );
            }
        }

        return $post->load(['author', 'terms']);
    }

    /**
     * Delete multiple posts
     */
    public function bulkDeletePosts(array $ids, string $postType = 'post'): int
    {
        if (empty($ids)) {
            return 0;
        }

        $posts = Post::where('post_type', $postType)
            ->whereIn('id', $ids)
            ->get();

        $deletedCount = 0;
        foreach ($posts as $post) {
            $post->delete();
            $deletedCount++;
        }

        return $deletedCount;
    }

    public function getPostPermalink(Post|int|null $post): ?string
    {
        if (is_numeric($post)) {
            $post = $this->getPostById($post);
        }

        if (! $post) {
            return null;
        }

        return route('post.show', ['post_type' => $post->post_type, 'slug' => $post->slug]);
    }

    public function getPostDate(Post|int|null $post, string $format = 'M d, Y'): ?string
    {
        if (is_numeric($post)) {
            $post = $this->getPostById($post);
        }

        if (! $post) {
            return null;
        }

        return $post->published_at ?
            $post->published_at->format($format) : $post->created_at->format($format);
    }

    public function getPostTerms(Post|int|null $post, string $taxonomy)
    {
        if (is_numeric($post)) {
            $post = $this->getPostById($post);
        }

        if (! $post) {
            return collect();
        }

        if ($taxonomy) {
            return $post->terms()->where('taxonomy', $taxonomy)->get();
        }

        return $post->terms;
    }
}
