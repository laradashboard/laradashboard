<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Hooks\AdminFilterHook;
use App\Models\Post;
use App\Support\Facades\Hook;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostFrontendUrlTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_fallback_url(): void
    {
        $post = Post::factory()->create([
            'post_type' => 'page',
            'slug' => 'about-us',
        ]);

        $url = $post->getFrontendUrl();

        $this->assertEquals(url('/about-us'), $url);
    }

    public function test_post_frontend_url(): void
    {
        $post = Post::factory()->create([
            'post_type' => 'post',
            'slug' => 'hello-world',
        ]);

        $url = $post->getFrontendUrl();

        // Starter26 module registers a filter that maps posts to /blog/{slug}
        // If no module filter, core fallback would be /post/{slug}
        $this->assertNotEmpty($url);
        $this->assertStringContainsString('hello-world', $url);
    }

    public function test_custom_post_type_fallback_url(): void
    {
        $post = Post::factory()->create([
            'post_type' => 'product',
            'slug' => 'widget',
        ]);

        $url = $post->getFrontendUrl();

        // When no theme filter handles this post type, the model's
        // default match falls through to the 'default' case
        $this->assertNotNull($url);
    }

    public function test_hook_overrides_frontend_url(): void
    {
        $post = Post::factory()->create([
            'post_type' => 'page',
            'slug' => 'test-page',
        ]);

        Hook::addFilter(AdminFilterHook::POST_FRONTEND_URL, function (?string $url, $post) {
            return '/custom/' . $post->slug;
        }, 10, 2);

        $url = $post->getFrontendUrl();

        $this->assertEquals('/custom/test-page', $url);
    }

    public function test_hook_returning_null_falls_back_to_default(): void
    {
        $post = Post::factory()->create([
            'post_type' => 'page',
            'slug' => 'my-page',
        ]);

        Hook::addFilter(AdminFilterHook::POST_FRONTEND_URL, function (?string $url, $post) {
            return null;
        }, 10, 2);

        $url = $post->getFrontendUrl();

        $this->assertEquals(url('/my-page'), $url);
    }
}
