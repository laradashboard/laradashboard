<?php

declare(strict_types=1);

namespace App\Services\Frontend;

use App\Enums\Hooks\FrontendFilterHook;
use App\Models\Post;
use App\Models\Term;
use App\Services\Builder\PostBuilderService;
use App\Support\Facades\Hook;
use Illuminate\Support\Str;

class SeoHelper
{
    public function __construct(
        private readonly PostBuilderService $postBuilderService
    ) {
    }

    /**
     * Build SEO params for a post (article).
     */
    public function forPost(Post $post): array
    {
        $displayTitle = $this->postBuilderService->getDisplayTitle($post);
        $seoTitle = trim((string) $post->getMeta(PostBuilderService::META_SEO_TITLE, ''));
        $seoDescription = trim((string) $post->getMeta(PostBuilderService::META_SEO_DESCRIPTION, ''));
        $seoKeywords = trim((string) $post->getMeta(PostBuilderService::META_SEO_KEYWORDS, ''));

        $params = [
            'title' => $seoTitle !== '' ? $seoTitle : $displayTitle,
            'description' => $seoDescription !== '' ? Str::limit(strip_tags($seoDescription), 160) : $this->extractDescription($post),
            'ogType' => 'article',
            '_toolbarPost' => $post,
        ];

        if ($seoKeywords !== '') {
            $params['keywords'] = $seoKeywords;
        }

        if ($post->featured_image) {
            $params['ogImage'] = $post->featured_image;
        }

        return $this->mergeDefaults($this->applyAdvancedSeoMeta($post, $params));
    }

    /**
     * Build SEO params for a page.
     */
    public function forPage(Post $page): array
    {
        $displayTitle = $this->postBuilderService->getDisplayTitle($page);
        $seoTitle = trim((string) $page->getMeta(PostBuilderService::META_SEO_TITLE, ''));
        $seoDescription = trim((string) $page->getMeta(PostBuilderService::META_SEO_DESCRIPTION, ''));
        $seoKeywords = trim((string) $page->getMeta(PostBuilderService::META_SEO_KEYWORDS, ''));

        $params = [
            'title' => $seoTitle !== '' ? $seoTitle : $displayTitle,
            'description' => $seoDescription !== '' ? Str::limit(strip_tags($seoDescription), 160) : $this->extractDescription($page),
            '_toolbarPost' => $page,
        ];

        if ($seoKeywords !== '') {
            $params['keywords'] = $seoKeywords;
        }

        if ($page->featured_image) {
            $params['ogImage'] = $page->featured_image;
        }

        return $this->mergeDefaults($this->applyAdvancedSeoMeta($page, $params));
    }

    /**
     * Build SEO params for a taxonomy term page.
     */
    public function forTerm(Term $term, string $taxonomy): array
    {
        $prefix = $taxonomy === 'tag' ? '#' : '';
        $descriptionKey = $taxonomy === 'tag'
            ? 'Browse all posts tagged with :tag.'
            : 'Browse all posts in :category category.';
        $descriptionReplacements = $taxonomy === 'tag'
            ? ['tag' => $term->name]
            : ['category' => $term->name];

        $params = [
            'title' => $prefix . $term->name,
            'description' => $term->description ?? __($descriptionKey, $descriptionReplacements),
        ];

        return $this->mergeDefaults($params);
    }

    /**
     * Build SEO params for search results page.
     */
    public function forSearch(string $query): array
    {
        $title = $query
            ? __('Search: :query', ['query' => $query])
            : __('Search');

        $params = [
            'title' => $title,
            'description' => __('Search our content for articles, tutorials, and more.'),
        ];

        return $this->mergeDefaults($params);
    }

    /**
     * Build SEO params for a blog listing page.
     */
    public function forBlogListing(?Post $blogPage = null): array
    {
        $params = [
            'title' => __('Posts'),
            'description' => __('Browse all our posts and articles.'),
            '_toolbarPost' => $blogPage,
        ];

        if ($blogPage) {
            $params['title'] = $blogPage->title;
            if ($blogPage->excerpt) {
                $params['description'] = Str::limit(strip_tags($blogPage->excerpt), 160);
            }
        }

        return $this->mergeDefaults($params);
    }

    /**
     * Build SEO params for the homepage.
     */
    public function forHomepage(?Post $page = null): array
    {
        $params = [
            'title' => __('Home'),
            'description' => __('Welcome to our website. Discover our latest posts and content.'),
            '_toolbarPost' => $page,
        ];

        if ($page) {
            $params['title'] = $page->title;
            if ($page->excerpt) {
                $params['description'] = Str::limit(strip_tags($page->excerpt), 160);
            }
            if ($page->featured_image) {
                $params['ogImage'] = $page->featured_image;
            }
        }

        return $this->mergeDefaults($params);
    }

    /**
     * Build custom SEO params.
     */
    public function forCustom(string $title, string $description = '', array $extra = []): array
    {
        $params = array_merge([
            'title' => $title,
            'description' => $description,
        ], $extra);

        return $this->mergeDefaults($params);
    }

    /**
     * Merge defaults into SEO params: append site name to title, apply filter hook.
     */
    public function mergeDefaults(array $params): array
    {
        if (isset($params['title']) && ! str_contains($params['title'], ' - ' . config('app.name'))) {
            $params['title'] .= ' - ' . config('app.name');
        }

        $filtered = Hook::applyFilters(FrontendFilterHook::SEO_PARAMS, $params);

        return is_array($filtered) ? $filtered : $params;
    }

    /**
     * Extract a description from a post's excerpt or content.
     */
    protected function extractDescription(Post $post): string
    {
        $seoDescription = trim((string) $post->getMeta(PostBuilderService::META_SEO_DESCRIPTION, ''));

        if ($seoDescription !== '') {
            return Str::limit(strip_tags($seoDescription), 160);
        }

        if ($post->excerpt) {
            return Str::limit(strip_tags($post->excerpt), 160);
        }

        return Str::limit(strip_tags($post->content ?? ''), 160);
    }

    /**
     * Apply advanced SEO meta overrides (Open Graph, robots, canonical).
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    protected function applyAdvancedSeoMeta(Post $post, array $params): array
    {
        $ogTitle = trim((string) $post->getMeta(PostBuilderService::META_SEO_OG_TITLE, ''));
        $ogDescription = trim((string) $post->getMeta(PostBuilderService::META_SEO_OG_DESCRIPTION, ''));
        $canonical = trim((string) $post->getMeta(PostBuilderService::META_SEO_CANONICAL, ''));
        $noindex = filter_var($post->getMeta(PostBuilderService::META_SEO_NOINDEX, false), FILTER_VALIDATE_BOOLEAN);
        $nofollow = filter_var($post->getMeta(PostBuilderService::META_SEO_NOFOLLOW, false), FILTER_VALIDATE_BOOLEAN);
        $schemaType = trim((string) $post->getMeta(PostBuilderService::META_SEO_SCHEMA_TYPE, ''));

        if ($ogTitle !== '') {
            $params['ogTitle'] = $ogTitle;
        }

        if ($ogDescription !== '') {
            $params['ogDescription'] = Str::limit(strip_tags($ogDescription), 200);
        }

        if ($canonical !== '') {
            $params['canonical'] = $canonical;
        }

        if ($noindex || $nofollow) {
            $directives = [];
            $directives[] = $noindex ? 'noindex' : 'index';
            $directives[] = $nofollow ? 'nofollow' : 'follow';
            $params['robots'] = implode(', ', $directives);
        }

        if ($schemaType !== '') {
            $params['schemaType'] = $schemaType;
        }

        return $params;
    }
}
