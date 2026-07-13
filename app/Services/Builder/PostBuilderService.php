<?php

declare(strict_types=1);

namespace App\Services\Builder;

use App\Enums\Builder\BuilderFilterHook;
use App\Enums\Hooks\PostFilterHook;
use App\Models\Post;
use App\Support\Facades\Hook;
use Illuminate\Support\Str;

class PostBuilderService
{
    public const META_SEO_TITLE = 'seo_title';

    public const META_SEO_DESCRIPTION = 'seo_description';

    public const META_SEO_KEYWORDS = 'seo_keywords';

    public const META_SEO_OG_TITLE = 'seo_og_title';

    public const META_SEO_OG_DESCRIPTION = 'seo_og_description';

    public const META_SEO_CANONICAL = 'seo_canonical';

    public const META_SEO_NOINDEX = 'seo_noindex';

    public const META_SEO_NOFOLLOW = 'seo_nofollow';

    public const META_SEO_SCHEMA_TYPE = 'seo_schema_type';

    public const META_FEATURED_MEDIA_ID = 'featured_media_id';

    /**
     * Resolve excerpt from builder input.
     *
     * When the excerpt field is empty, auto-generate from content instead of
     * keeping a stale value from a previous save.
     */
    public function resolveExcerpt(?string $excerpt, ?string $content): string
    {
        if ($excerpt !== null && trim($excerpt) !== '') {
            return $excerpt;
        }

        return Str::limit(strip_tags($content ?? ''), 200);
    }

    /**
     * Get SEO meta values for the builder sidebar.
     *
     * @return array<string, mixed>
     */
    public function getSeoMetaForBuilder(Post $post): array
    {
        $meta = [
            'seo_title' => (string) $post->getMeta(self::META_SEO_TITLE, ''),
            'seo_description' => (string) $post->getMeta(self::META_SEO_DESCRIPTION, ''),
            'seo_keywords' => (string) $post->getMeta(self::META_SEO_KEYWORDS, ''),
            'seo_og_title' => (string) $post->getMeta(self::META_SEO_OG_TITLE, ''),
            'seo_og_description' => (string) $post->getMeta(self::META_SEO_OG_DESCRIPTION, ''),
            'seo_canonical' => (string) $post->getMeta(self::META_SEO_CANONICAL, ''),
            'seo_noindex' => filter_var($post->getMeta(self::META_SEO_NOINDEX, false), FILTER_VALIDATE_BOOLEAN),
            'seo_nofollow' => filter_var($post->getMeta(self::META_SEO_NOFOLLOW, false), FILTER_VALIDATE_BOOLEAN),
            'seo_schema_type' => (string) $post->getMeta(self::META_SEO_SCHEMA_TYPE, ''),
        ];

        $filtered = Hook::applyFilters(PostFilterHook::POST_BUILDER_SEO_META->value, $meta, $post);

        return is_array($filtered) ? $filtered : $meta;
    }

    /**
     * Persist SEO meta from builder save payload.
     *
     * @param  array<string, mixed>  $data
     */
    public function saveSeoMeta(Post $post, array $data): void
    {
        $seoData = [
            self::META_SEO_TITLE => trim((string) ($data['seo_title'] ?? '')),
            self::META_SEO_DESCRIPTION => trim((string) ($data['seo_description'] ?? '')),
            self::META_SEO_KEYWORDS => trim((string) ($data['seo_keywords'] ?? '')),
            self::META_SEO_OG_TITLE => trim((string) ($data['seo_og_title'] ?? '')),
            self::META_SEO_OG_DESCRIPTION => trim((string) ($data['seo_og_description'] ?? '')),
            self::META_SEO_CANONICAL => trim((string) ($data['seo_canonical'] ?? '')),
            self::META_SEO_SCHEMA_TYPE => trim((string) ($data['seo_schema_type'] ?? '')),
        ];

        $booleanMeta = [
            self::META_SEO_NOINDEX => filter_var($data['seo_noindex'] ?? false, FILTER_VALIDATE_BOOLEAN),
            self::META_SEO_NOFOLLOW => filter_var($data['seo_nofollow'] ?? false, FILTER_VALIDATE_BOOLEAN),
        ];

        $seoData = Hook::applyFilters(BuilderFilterHook::BUILDER_SEO_META_SAVE->value, $seoData, $post, $data);

        if (! is_array($seoData)) {
            return;
        }

        foreach ($seoData as $key => $value) {
            if ($value === '') {
                $post->deleteMeta($key);

                continue;
            }

            $post->setMeta($key, $value);
        }

        foreach ($booleanMeta as $key => $enabled) {
            if ($enabled) {
                $post->setMeta($key, '1');

                continue;
            }

            $post->deleteMeta($key);
        }
    }

    /**
     * Get a human-readable title, falling back to the first heading block.
     */
    public function getDisplayTitle(Post $post): string
    {
        if (trim((string) $post->title) !== '') {
            return (string) $post->title;
        }

        $headingText = $this->extractFirstHeadingFromDesignJson($post->design_json);

        if ($headingText !== '') {
            return $headingText;
        }

        return __('Untitled');
    }

    /**
     * @param  array<string, mixed>|null  $designJson
     */
    protected function extractFirstHeadingFromDesignJson(?array $designJson): string
    {
        if (empty($designJson)) {
            return '';
        }

        $blocks = $designJson['blocks'] ?? $designJson;

        return $this->findFirstHeadingText($blocks);
    }

    /**
     * @param  mixed  $blocks
     */
    protected function findFirstHeadingText($blocks): string
    {
        if (! is_array($blocks)) {
            return '';
        }

        foreach ($blocks as $block) {
            if (is_array($block) && ($block['type'] ?? '') === 'heading') {
                $text = strip_tags((string) ($block['props']['text'] ?? ''));

                if (trim($text) !== '') {
                    return trim($text);
                }
            }

            if (is_array($block) && ! empty($block['props']['children']) && is_array($block['props']['children'])) {
                $nested = $this->findFirstHeadingText($block['props']['children']);

                if ($nested !== '') {
                    return $nested;
                }
            }
        }

        return '';
    }
}
