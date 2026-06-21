<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Builder;

use App\Models\Post;
use App\Services\Builder\PostBuilderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostBuilderServiceTest extends TestCase
{
    use RefreshDatabase;

    private PostBuilderService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PostBuilderService::class);
    }

    public function test_resolve_excerpt_uses_provided_value(): void
    {
        $excerpt = $this->service->resolveExcerpt('Custom excerpt', '<p>Content</p>');

        $this->assertSame('Custom excerpt', $excerpt);
    }

    public function test_resolve_excerpt_auto_generates_when_empty(): void
    {
        $excerpt = $this->service->resolveExcerpt('', '<p>Generated from content body.</p>');

        $this->assertSame('Generated from content body.', $excerpt);
    }

    public function test_resolve_excerpt_auto_generates_when_null(): void
    {
        $excerpt = $this->service->resolveExcerpt(null, '<p>Generated from content body.</p>');

        $this->assertSame('Generated from content body.', $excerpt);
    }

    public function test_get_display_title_falls_back_to_first_heading(): void
    {
        $post = Post::factory()->create([
            'title' => '',
            'design_json' => [
                'blocks' => [
                    [
                        'type' => 'text',
                        'props' => ['content' => 'Intro paragraph'],
                    ],
                    [
                        'type' => 'heading',
                        'props' => ['text' => 'Laravel vs WordPress'],
                    ],
                ],
            ],
        ]);

        $this->assertSame('Laravel vs WordPress', $this->service->getDisplayTitle($post));
    }

    public function test_get_display_title_returns_untitled_when_empty(): void
    {
        $post = Post::factory()->create([
            'title' => '',
            'design_json' => null,
        ]);

        $this->assertSame(__('Untitled'), $this->service->getDisplayTitle($post));
    }

    public function test_save_and_load_seo_meta(): void
    {
        $post = Post::factory()->create();

        $this->service->saveSeoMeta($post, [
            'seo_title' => 'Custom SEO Title',
            'seo_description' => 'Custom SEO description.',
            'seo_keywords' => 'laravel, wordpress',
        ]);

        $meta = $this->service->getSeoMetaForBuilder($post->fresh());

        $this->assertSame('Custom SEO Title', $meta['seo_title']);
        $this->assertSame('Custom SEO description.', $meta['seo_description']);
        $this->assertSame('laravel, wordpress', $meta['seo_keywords']);
    }

    public function test_save_seo_meta_clears_empty_values(): void
    {
        $post = Post::factory()->create();
        $post->setMeta(PostBuilderService::META_SEO_TITLE, 'Old title');

        $this->service->saveSeoMeta($post, [
            'seo_title' => '',
            'seo_description' => '',
            'seo_keywords' => '',
        ]);

        $this->assertNull($post->fresh()->getMeta(PostBuilderService::META_SEO_TITLE));
    }
}
