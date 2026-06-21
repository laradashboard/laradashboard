<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Post;
use App\Services\Builder\PostBuilderService;
use App\Services\MediaLibraryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;
use Tests\TestCase;

class MediaLibraryServiceTest extends TestCase
{
    use RefreshDatabase;

    private MediaLibraryService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(MediaLibraryService::class);
    }

    protected function tearDown(): void
    {
        @unlink(storage_path('app/public/media/test-image.png'));
        @unlink(storage_path('app/public/media/first-image.png'));
        @unlink(storage_path('app/public/media/second-image.png'));
        @unlink(storage_path('app/public/media/shared-image.png'));
        @unlink(storage_path('app/public/media/existing-feature.png'));
        @unlink(storage_path('app/public/media/new-feature.png'));
        @unlink(storage_path('app/public/media/legacy-feature.png'));

        parent::tearDown();
    }

    private function createStandaloneMedia(string $fileName, string $name): SpatieMedia
    {
        $directory = storage_path('app/public/media');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($directory . '/' . $fileName, 'fake-image-content');

        return SpatieMedia::create([
            'model_type' => '',
            'model_id' => 0,
            'uuid' => (string) Str::uuid(),
            'collection_name' => 'uploads',
            'name' => $name,
            'file_name' => $fileName,
            'mime_type' => 'image/png',
            'disk' => 'public',
            'conversions_disk' => 'public',
            'size' => 18,
            'manipulations' => [],
            'custom_properties' => [],
            'generated_conversions' => [],
            'responsive_images' => [],
            'order_column' => null,
        ]);
    }

    public function test_associate_existing_media_links_post_featured_image_without_duplicating(): void
    {
        $standaloneMedia = $this->createStandaloneMedia('test-image.png', 'Test Image');

        $post = Post::factory()->create([
            'post_type' => 'post',
        ]);

        $associated = $this->service->associateExistingMedia(
            $post,
            (string) $standaloneMedia->id,
            'featured'
        );

        $post->refresh();
        $standaloneMedia->refresh();

        $this->assertNotNull($associated);
        $this->assertSame(1, SpatieMedia::count());
        $this->assertTrue($post->hasFeaturedImage());
        $this->assertSame((string) $standaloneMedia->id, $post->getMeta(PostBuilderService::META_FEATURED_MEDIA_ID));
        $this->assertSame(0, (int) $standaloneMedia->model_id);
        $this->assertNotNull($post->getFeaturedImageUrl());
        $this->assertNotNull($post->getFeaturedImageUrl('thumb'));

        $this->service->associateExistingMedia(
            $post->fresh(),
            (string) $standaloneMedia->id,
            'featured'
        );

        $this->assertSame(1, SpatieMedia::count());
    }

    public function test_associate_existing_media_can_switch_featured_image_without_extra_library_entries(): void
    {
        $firstMedia = $this->createStandaloneMedia('first-image.png', 'First Image');
        $secondMedia = $this->createStandaloneMedia('second-image.png', 'Second Image');

        $post = Post::factory()->create([
            'post_type' => 'post',
        ]);

        $this->service->associateExistingMedia($post, (string) $firstMedia->id, 'featured');
        $this->service->associateExistingMedia($post->fresh(), (string) $secondMedia->id, 'featured');

        $post->refresh();

        $this->assertSame((string) $secondMedia->id, $post->getMeta(PostBuilderService::META_FEATURED_MEDIA_ID));
        $this->assertSame(0, (int) $firstMedia->fresh()->model_id);
        $this->assertSame(0, (int) $secondMedia->fresh()->model_id);
        $this->assertSame(2, SpatieMedia::count());
    }

    public function test_same_library_image_can_be_used_on_multiple_posts_without_duplicating(): void
    {
        $sharedMedia = $this->createStandaloneMedia('shared-image.png', 'Shared Image');

        $firstPost = Post::factory()->create(['post_type' => 'post']);
        $secondPost = Post::factory()->create(['post_type' => 'post']);

        $this->service->associateExistingMedia($firstPost, (string) $sharedMedia->id, 'featured');
        $this->service->associateExistingMedia($secondPost, (string) $sharedMedia->id, 'featured');

        $firstPost->refresh();
        $secondPost->refresh();
        $sharedMedia->refresh();

        $this->assertSame((string) $sharedMedia->id, $firstPost->getMeta(PostBuilderService::META_FEATURED_MEDIA_ID));
        $this->assertSame((string) $sharedMedia->id, $secondPost->getMeta(PostBuilderService::META_FEATURED_MEDIA_ID));
        $this->assertSame(1, SpatieMedia::count());
        $this->assertSame(0, (int) $sharedMedia->model_id);
        $this->assertNotNull($firstPost->getFeaturedImageUrl('thumb'));
        $this->assertNotNull($secondPost->getFeaturedImageUrl('thumb'));
    }

    public function test_resolve_media_urls_returns_working_url_for_legacy_attached_files(): void
    {
        $media = $this->createStandaloneMedia('legacy-feature.png', 'Legacy Feature');

        $post = Post::factory()->create([
            'post_type' => 'post',
        ]);

        $media->model_type = $post->getMorphClass();
        $media->model_id = $post->getKey();
        $media->collection_name = 'featured';
        $media->save();

        $urls = $this->service->resolveMediaUrls($media->fresh());

        $this->assertStringContainsString('legacy-feature.png', $urls['url']);
        $this->assertStringContainsString('legacy-feature.png', $urls['thumbnail_url']);
    }

    public function test_associate_existing_media_can_replace_legacy_featured_image_with_string_json(): void
    {
        $existingFeatured = $this->createStandaloneMedia('existing-feature.png', 'Existing Feature');
        $newFeatured = $this->createStandaloneMedia('new-feature.png', 'New Feature');

        $post = Post::factory()->create([
            'post_type' => 'post',
        ]);

        $this->service->associateExistingMedia($post, (string) $existingFeatured->id, 'featured');

        SpatieMedia::query()
            ->whereKey($existingFeatured->id)
            ->update([
                'manipulations' => '[]',
                'custom_properties' => '[]',
                'generated_conversions' => '[]',
                'responsive_images' => '[]',
            ]);

        $associated = $this->service->associateExistingMedia(
            $post->fresh(),
            (string) $newFeatured->id,
            'featured'
        );

        $post->refresh();

        $this->assertNotNull($associated);
        $this->assertSame((string) $newFeatured->id, $post->getMeta(PostBuilderService::META_FEATURED_MEDIA_ID));
        $this->assertSame(2, SpatieMedia::count());
        $this->assertSame(0, (int) $existingFeatured->fresh()->model_id);
    }
}
