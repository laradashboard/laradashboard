<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Post;
use App\Models\User;
use App\Services\Builder\PostBuilderService;
use App\Services\Content\ContentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;
use Tests\TestCase;

class PostBuilderImprovementsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(VerifyCsrfToken::class);

        $this->admin = User::factory()->create();

        $role = Role::firstOrCreate(['name' => 'content-admin', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'post.view', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'post.create', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'post.edit', 'guard_name' => 'web']);
        $role->givePermissionTo(['post.view', 'post.create', 'post.edit']);
        $this->admin->assignRole($role);

        app(ContentService::class)->registerPostType([
            'name' => 'post',
            'label' => 'Posts',
            'label_singular' => 'Post',
            'description' => 'Default post type for blog entries',
            'taxonomies' => ['category', 'tag'],
        ]);
    }

    public function test_builder_update_regenerates_excerpt_when_cleared(): void
    {
        $post = Post::factory()->create([
            'title' => 'Existing Post',
            'post_type' => 'post',
            'excerpt' => 'Old excerpt that should not remain',
            'content' => '<p>Old content</p>',
            'status' => PostStatus::DRAFT->value,
            'user_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->putJson("/admin/posts/post/{$post->id}", [
                'title' => 'Existing Post',
                'content' => '<p>Fresh builder content for excerpt generation.</p>',
                'excerpt' => '',
                'status' => PostStatus::DRAFT->value,
            ]);

        $response->assertOk();

        $post->refresh();

        $this->assertSame(
            'Fresh builder content for excerpt generation.',
            $post->excerpt
        );
    }

    public function test_builder_update_persists_seo_meta(): void
    {
        $post = Post::factory()->create([
            'title' => 'SEO Post',
            'post_type' => 'post',
            'status' => PostStatus::DRAFT->value,
            'user_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->putJson("/admin/posts/post/{$post->id}", [
                'title' => 'SEO Post',
                'content' => '<p>Content</p>',
                'excerpt' => 'Summary',
                'status' => PostStatus::DRAFT->value,
                'seo_title' => 'Custom SEO Title',
                'seo_description' => 'Custom meta description for search.',
                'seo_keywords' => 'laravel, cms',
            ]);

        $response->assertOk();

        $post->refresh();

        $this->assertSame('Custom SEO Title', $post->getMeta(PostBuilderService::META_SEO_TITLE));
        $this->assertSame(
            'Custom meta description for search.',
            $post->getMeta(PostBuilderService::META_SEO_DESCRIPTION)
        );
        $this->assertSame('laravel, cms', $post->getMeta(PostBuilderService::META_SEO_KEYWORDS));
    }

    public function test_builder_update_persists_advanced_seo_meta(): void
    {
        $post = Post::factory()->create([
            'title' => 'Advanced SEO Post',
            'post_type' => 'post',
            'status' => PostStatus::DRAFT->value,
            'user_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->putJson("/admin/posts/post/{$post->id}", [
                'title' => 'Advanced SEO Post',
                'content' => '<p>Content</p>',
                'excerpt' => 'Summary',
                'status' => PostStatus::DRAFT->value,
                'seo_title' => 'Advanced SEO Title',
                'seo_description' => 'Advanced description.',
                'seo_keywords' => 'seo, advanced',
                'seo_og_title' => 'Social Title',
                'seo_og_description' => 'Social description.',
                'seo_canonical' => 'https://example.com/advanced-seo',
                'seo_noindex' => true,
                'seo_nofollow' => false,
                'seo_schema_type' => 'BlogPosting',
            ]);

        $response->assertOk();

        $post->refresh();

        $this->assertSame('Social Title', $post->getMeta(PostBuilderService::META_SEO_OG_TITLE));
        $this->assertSame(
            'https://example.com/advanced-seo',
            $post->getMeta(PostBuilderService::META_SEO_CANONICAL)
        );
        $this->assertSame('1', $post->getMeta(PostBuilderService::META_SEO_NOINDEX));
        $this->assertNull($post->getMeta(PostBuilderService::META_SEO_NOFOLLOW));
        $this->assertSame('BlogPosting', $post->getMeta(PostBuilderService::META_SEO_SCHEMA_TYPE));
    }

    public function test_ai_generate_seo_returns_metadata_without_api_key(): void
    {
        config(['settings.ai_openai_api_key' => null]);
        config(['settings.ai_claude_api_key' => null]);

        $response = $this->actingAs($this->admin)
            ->postJson('/admin/ai/generate-seo', [
                'title' => 'Laravel CMS Comparison Guide',
                'content' => '<p>Laravel offers a modern approach to building content management systems.</p>',
                'excerpt' => 'Compare Laravel CMS options.',
                'post_type' => 'post',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'seo_title',
                    'seo_description',
                    'seo_keywords',
                    'seo_og_title',
                    'seo_og_description',
                    'seo_schema_type',
                ],
            ]);
    }

    public function test_ai_generate_seo_requires_title_or_content(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/admin/ai/generate-seo', [
                'title' => '',
                'content' => '',
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_builder_store_creates_draft_post_with_title(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/admin/posts/post', [
                'title' => 'Auto-saved Draft Post',
                'content' => '<p>Builder content</p>',
                'status' => PostStatus::DRAFT->value,
                'design_json' => [
                    'blocks' => [],
                ],
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['id', 'redirect']);

        $post = Post::query()->findOrFail($response->json('id'));

        $this->assertSame('Auto-saved Draft Post', $post->title);
        $this->assertSame(PostStatus::DRAFT->value, $post->status);
        $this->assertSame('<p>Builder content</p>', $post->content);
    }

    public function test_builder_update_preserves_published_status_for_background_save(): void
    {
        $post = Post::factory()->create([
            'title' => 'Published Post',
            'post_type' => 'post',
            'status' => PostStatus::PUBLISHED->value,
            'content' => '<p>Original</p>',
            'user_id' => $this->admin->id,
            'published_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->putJson("/admin/posts/post/{$post->id}", [
                'title' => 'Published Post',
                'content' => '<p>Updated in background</p>',
                'status' => PostStatus::PUBLISHED->value,
            ]);

        $response->assertOk();

        $post->refresh();

        $this->assertSame(PostStatus::PUBLISHED->value, $post->status);
        $this->assertSame('<p>Updated in background</p>', $post->content);
        $this->assertNotNull($post->published_at);
    }

    public function test_builder_update_reuses_existing_media_for_featured_image(): void
    {
        $directory = storage_path('app/public/media');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($directory . '/builder-feature.png', 'featured-image');

        $media = SpatieMedia::create([
            'model_type' => '',
            'model_id' => 0,
            'uuid' => (string) Str::uuid(),
            'collection_name' => 'uploads',
            'name' => 'Builder Feature',
            'file_name' => 'builder-feature.png',
            'mime_type' => 'image/png',
            'disk' => 'public',
            'conversions_disk' => 'public',
            'size' => 14,
            'manipulations' => [],
            'custom_properties' => [],
            'generated_conversions' => [],
            'responsive_images' => [],
            'order_column' => null,
        ]);

        $post = Post::factory()->create([
            'title' => 'Featured Builder Post',
            'post_type' => 'post',
            'status' => PostStatus::DRAFT->value,
            'user_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->putJson("/admin/posts/post/{$post->id}", [
                'title' => 'Featured Builder Post',
                'content' => '<p>Content</p>',
                'status' => PostStatus::DRAFT->value,
                'featured_image_id' => $media->id,
            ]);

        $response->assertOk();

        $post->refresh();

        $this->assertTrue($post->hasFeaturedImage());
        $this->assertSame((string) $media->id, $post->getMeta(PostBuilderService::META_FEATURED_MEDIA_ID));
        $this->assertSame(1, SpatieMedia::count());
        $this->assertNotNull($post->getFeaturedImageUrl());
        $this->assertNotNull($post->getFeaturedImageUrl('thumb'));
        $this->assertSame(0, (int) $media->fresh()->model_id);

        $resaveResponse = $this->actingAs($this->admin)
            ->putJson("/admin/posts/post/{$post->id}", [
                'title' => 'Featured Builder Post',
                'content' => '<p>Content</p>',
                'status' => PostStatus::DRAFT->value,
                'featured_image_id' => $media->id,
            ]);

        $resaveResponse->assertOk();
        $this->assertSame(1, SpatieMedia::count());
    }
}
