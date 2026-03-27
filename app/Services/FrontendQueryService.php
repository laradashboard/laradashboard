<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Hooks\FrontendFilterHook;
use App\Models\Post;
use App\Models\Term;
use App\Support\Facades\Hook;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class FrontendQueryService
{
    /**
     * Find the homepage post from settings or by slug fallback.
     */
    public function findHomepage(): ?Post
    {
        if (! class_exists(Post::class)) {
            return null;
        }

        $homepageId = config('settings.homepage_id');
        $page = null;

        if ($homepageId) {
            $page = Post::query()
                ->where('id', $homepageId)
                ->where('post_type', 'page')
                ->where('status', 'published')
                ->first();
        }

        if (! $page) {
            $page = Post::query()
                ->where('slug', 'home')
                ->where('post_type', 'page')
                ->where('status', 'published')
                ->first();
        }

        return $this->filterOrFallback(FrontendFilterHook::HOMEPAGE_QUERY, $page, Post::class);
    }

    /**
     * Find the blog listing page from settings or by slug fallback.
     */
    public function findBlogPage(): ?Post
    {
        if (! class_exists(Post::class)) {
            return null;
        }

        $blogPageId = config('settings.blog_page_id');
        $page = null;

        if ($blogPageId) {
            $page = Post::query()
                ->where('id', $blogPageId)
                ->where('post_type', 'page')
                ->where('status', 'published')
                ->first();
        }

        if (! $page) {
            $page = Post::query()
                ->where('slug', 'blog')
                ->where('post_type', 'page')
                ->where('status', 'published')
                ->first();
        }

        return $page;
    }

    /**
     * Find a published post by slug.
     */
    public function findPublishedPostBySlug(string $slug): ?Post
    {
        if (! class_exists(Post::class)) {
            return null;
        }

        return Post::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();
    }

    /**
     * Find a published page by slug.
     */
    public function findPublishedPageBySlug(string $slug): ?Post
    {
        if (! class_exists(Post::class)) {
            return null;
        }

        return Post::query()
            ->where('slug', $slug)
            ->where('post_type', 'page')
            ->where('status', 'published')
            ->firstOrFail();
    }

    /**
     * Get related posts for a given post (same category).
     */
    public function relatedPosts(Post $post, int $limit = 3): Collection
    {
        $results = Post::query()
            ->where('id', '!=', $post->id)
            ->where('status', 'published')
            ->when($post->terms->where('taxonomy', 'category')->first(), function ($query) use ($post) {
                $categoryId = $post->terms->where('taxonomy', 'category')->first()->id;
                $query->whereHas('terms', fn ($q) => $q->where('terms.id', $categoryId));
            })
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        $filtered = Hook::applyFilters(FrontendFilterHook::RELATED_POSTS, $results, $post);

        return $filtered instanceof Collection ? $filtered : $results;
    }

    /**
     * Get previous and next posts relative to a given post.
     */
    public function adjacentPosts(Post $post): array
    {
        $previous = Post::query()
            ->where('status', 'published')
            ->where('created_at', '<', $post->created_at)
            ->orderBy('created_at', 'desc')
            ->first();

        $next = Post::query()
            ->where('status', 'published')
            ->where('created_at', '>', $post->created_at)
            ->orderBy('created_at', 'asc')
            ->first();

        return ['previous' => $previous, 'next' => $next];
    }

    /**
     * Find a term by slug and taxonomy.
     */
    public function findTermBySlug(string $slug, string $taxonomy): ?Term
    {
        if (! class_exists(Term::class)) {
            return null;
        }

        return Term::query()
            ->where('slug', $slug)
            ->where('taxonomy', $taxonomy)
            ->firstOrFail();
    }

    /**
     * Get the base query for published posts.
     */
    public function publishedPostsQuery(): Builder
    {
        $query = Post::query()
            ->where('status', 'published')
            ->where('post_type', 'post');

        $filtered = Hook::applyFilters(FrontendFilterHook::POSTS_QUERY, $query);

        return $filtered instanceof Builder ? $filtered : $query;
    }

    /**
     * Get a query for posts belonging to a term.
     */
    public function postsForTerm(Term $term): Builder
    {
        $query = Post::query()
            ->where('status', 'published')
            ->whereHas('terms', fn ($q) => $q->where('terms.id', $term->id))
            ->orderBy('created_at', 'desc');

        $filtered = Hook::applyFilters(FrontendFilterHook::TAXONOMY_QUERY, $query, $term);

        return $filtered instanceof Builder ? $filtered : $query;
    }

    /**
     * Search posts by query string.
     */
    public function searchPosts(string $searchQuery): Builder
    {
        $query = Post::query()
            ->where('status', 'published')
            ->where(function ($q) use ($searchQuery) {
                $q->where('title', 'like', '%' . $searchQuery . '%')
                    ->orWhere('content', 'like', '%' . $searchQuery . '%');
            })
            ->orderBy('created_at', 'desc');

        $filtered = Hook::applyFilters(FrontendFilterHook::SEARCH_QUERY, $query, $searchQuery);

        return $filtered instanceof Builder ? $filtered : $query;
    }

    /**
     * Get all categories as slug => name array.
     */
    public function getCategories(): array
    {
        if (! class_exists(Term::class)) {
            return [];
        }

        return Term::query()
            ->where('taxonomy', 'category')
            ->orderBy('name')
            ->pluck('name', 'slug')
            ->toArray();
    }

    /**
     * Paginate a posts query.
     */
    public function paginatePosts(Builder $query, ?int $perPage = null): LengthAwarePaginator
    {
        $perPage = $perPage ?? (int) config('settings.posts_per_page', 12);

        return $query->paginate($perPage);
    }

    /**
     * Apply search filter to a query.
     */
    public function applySearchFilter(Builder $query, string $search): Builder
    {
        if (! $search) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', '%' . $search . '%')
                ->orWhere('content', 'like', '%' . $search . '%');
        });
    }

    /**
     * Apply category filter to a query.
     */
    public function applyCategoryFilter(Builder $query, string $categorySlug): Builder
    {
        if (! $categorySlug) {
            return $query;
        }

        return $query->whereHas('terms', function ($q) use ($categorySlug) {
            $q->where('slug', $categorySlug)
                ->where('taxonomy', 'category');
        });
    }

    /**
     * Apply sort to a query.
     */
    public function applySort(Builder $query, string $sort): Builder
    {
        match ($sort) {
            'oldest' => $query->orderBy('created_at', 'asc'),
            'title' => $query->orderBy('title', 'asc'),
            default => $query->orderBy('created_at', 'desc'),
        };

        return $query;
    }

    /**
     * Apply a filter hook safely, falling back to original value if the filter
     * returns an unexpected type (Eventy returns '' when no listeners exist).
     */
    private function filterOrFallback(FrontendFilterHook $hook, mixed $value, ?string $expectedClass = null): mixed
    {
        $filtered = Hook::applyFilters($hook, $value);

        if ($expectedClass && ! $filtered instanceof $expectedClass) {
            return $value;
        }

        if ($filtered === '' && $value !== '') {
            return $value;
        }

        return $filtered;
    }
}
