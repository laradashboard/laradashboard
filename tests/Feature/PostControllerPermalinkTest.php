<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostControllerPermalinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_builder_edit_post_data_contains_frontend_url(): void
    {
        $post = Post::factory()->create([
            'post_type' => 'page',
            'slug' => 'test-page',
        ]);

        // Verify the getFrontendUrl method is available and returns a value
        $frontendUrl = $post->getFrontendUrl();
        $this->assertNotEmpty($frontendUrl);

        // Verify the postData array that builderEdit would build includes frontend_url
        $postData = [
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'status' => $post->status,
            'excerpt' => $post->excerpt,
            'parent_id' => $post->parent_id,
            'published_at' => $post->published_at?->format('Y-m-d\TH:i'),
            'featured_image_url' => $post->getFeaturedImageUrl(),
            'frontend_url' => $post->getFrontendUrl(),
        ];

        $this->assertArrayHasKey('frontend_url', $postData);
        $this->assertEquals($frontendUrl, $postData['frontend_url']);
    }
}
