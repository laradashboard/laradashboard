<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Post;
use App\Models\Term;
use App\Services\FrontendQueryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrontendQueryServiceTest extends TestCase
{
    use RefreshDatabase;

    private FrontendQueryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(FrontendQueryService::class);
    }

    public function test_find_homepage_by_settings_id(): void
    {
        $page = Post::factory()->create([
            'post_type' => 'page',
            'status' => 'published',
            'slug' => 'my-homepage',
        ]);

        config(['settings.homepage_id' => $page->id]);

        $result = $this->service->findHomepage();

        $this->assertNotNull($result);
        $this->assertEquals($page->id, $result->id);
    }

    public function test_find_homepage_falls_back_to_home_slug(): void
    {
        $page = Post::factory()->create([
            'post_type' => 'page',
            'status' => 'published',
            'slug' => 'home',
        ]);

        config(['settings.homepage_id' => null]);

        $result = $this->service->findHomepage();

        $this->assertNotNull($result);
        $this->assertEquals($page->id, $result->id);
    }

    public function test_find_homepage_returns_null_when_no_page(): void
    {
        config(['settings.homepage_id' => null]);

        $result = $this->service->findHomepage();

        $this->assertNull($result);
    }

    public function test_find_blog_page_by_settings_id(): void
    {
        $page = Post::factory()->create([
            'post_type' => 'page',
            'status' => 'published',
            'slug' => 'my-blog',
        ]);

        config(['settings.blog_page_id' => $page->id]);

        $result = $this->service->findBlogPage();

        $this->assertNotNull($result);
        $this->assertEquals($page->id, $result->id);
    }

    public function test_find_blog_page_falls_back_to_blog_slug(): void
    {
        $page = Post::factory()->create([
            'post_type' => 'page',
            'status' => 'published',
            'slug' => 'blog',
        ]);

        config(['settings.blog_page_id' => null]);

        $result = $this->service->findBlogPage();

        $this->assertNotNull($result);
        $this->assertEquals($page->id, $result->id);
    }

    public function test_find_published_post_by_slug(): void
    {
        $post = Post::factory()->create([
            'status' => 'published',
            'slug' => 'test-post',
        ]);

        $result = $this->service->findPublishedPostBySlug('test-post');

        $this->assertNotNull($result);
        $this->assertEquals($post->id, $result->id);
    }

    public function test_find_published_page_by_slug(): void
    {
        $page = Post::factory()->create([
            'post_type' => 'page',
            'status' => 'published',
            'slug' => 'about',
        ]);

        $result = $this->service->findPublishedPageBySlug('about');

        $this->assertNotNull($result);
        $this->assertEquals($page->id, $result->id);
    }

    public function test_related_posts_returns_posts_in_same_category(): void
    {
        $category = Term::factory()->category()->create();

        $post = Post::factory()->create([
            'status' => 'published',
            'post_type' => 'post',
        ]);
        $post->terms()->attach($category);

        $related = Post::factory()->create([
            'status' => 'published',
            'post_type' => 'post',
        ]);
        $related->terms()->attach($category);

        $unrelated = Post::factory()->create([
            'status' => 'published',
            'post_type' => 'post',
        ]);

        $results = $this->service->relatedPosts($post);

        $this->assertTrue($results->contains('id', $related->id));
        $this->assertFalse($results->contains('id', $post->id));
    }

    public function test_adjacent_posts(): void
    {
        $older = Post::factory()->create([
            'status' => 'published',
            'created_at' => now()->subDays(2),
        ]);

        $current = Post::factory()->create([
            'status' => 'published',
            'created_at' => now()->subDay(),
        ]);

        $newer = Post::factory()->create([
            'status' => 'published',
            'created_at' => now(),
        ]);

        $result = $this->service->adjacentPosts($current);

        $this->assertEquals($older->id, $result['previous']->id);
        $this->assertEquals($newer->id, $result['next']->id);
    }

    public function test_find_term_by_slug(): void
    {
        $category = Term::factory()->category()->create(['slug' => 'tech']);

        $result = $this->service->findTermBySlug('tech', 'category');

        $this->assertNotNull($result);
        $this->assertEquals($category->id, $result->id);
    }

    public function test_published_posts_query_only_returns_published_posts(): void
    {
        Post::factory()->create(['status' => 'published', 'post_type' => 'post']);
        Post::factory()->create(['status' => 'draft', 'post_type' => 'post']);
        Post::factory()->create(['status' => 'published', 'post_type' => 'page']);

        $results = $this->service->publishedPostsQuery()->get();

        $this->assertCount(1, $results);
        $this->assertEquals('published', $results->first()->status);
        $this->assertEquals('post', $results->first()->post_type);
    }

    public function test_posts_for_term(): void
    {
        $category = Term::factory()->category()->create();

        $post = Post::factory()->create([
            'status' => 'published',
            'post_type' => 'post',
        ]);
        $post->terms()->attach($category);

        $unrelated = Post::factory()->create([
            'status' => 'published',
            'post_type' => 'post',
        ]);

        $results = $this->service->postsForTerm($category)->get();

        $this->assertCount(1, $results);
        $this->assertEquals($post->id, $results->first()->id);
    }

    public function test_search_posts(): void
    {
        Post::factory()->create([
            'status' => 'published',
            'title' => 'Laravel Tutorial Guide',
            'content' => 'Learn Laravel basics.',
        ]);

        Post::factory()->create([
            'status' => 'published',
            'title' => 'React Guide',
            'content' => 'Learn React basics.',
        ]);

        $results = $this->service->searchPosts('Laravel')->get();

        $this->assertCount(1, $results);
        $this->assertStringContainsString('Laravel', $results->first()->title);
    }

    public function test_get_categories(): void
    {
        Term::factory()->category()->create(['name' => 'Technology', 'slug' => 'technology']);
        Term::factory()->category()->create(['name' => 'Business', 'slug' => 'business']);
        Term::factory()->tag()->create(['name' => 'PHP', 'slug' => 'php']);

        $categories = $this->service->getCategories();

        $this->assertCount(2, $categories);
        $this->assertArrayHasKey('technology', $categories);
        $this->assertArrayHasKey('business', $categories);
    }

    public function test_apply_search_filter(): void
    {
        $query = $this->service->publishedPostsQuery();
        $filtered = $this->service->applySearchFilter($query, 'test');

        $this->assertStringContainsString('like', $filtered->toSql());
    }

    public function test_apply_category_filter(): void
    {
        $query = $this->service->publishedPostsQuery();
        $filtered = $this->service->applyCategoryFilter($query, 'tech');

        $this->assertStringContainsString('exists', $filtered->toSql());
    }

    public function test_apply_sort_latest(): void
    {
        Post::factory()->create(['status' => 'published', 'post_type' => 'post', 'created_at' => now()->subDay()]);
        Post::factory()->create(['status' => 'published', 'post_type' => 'post', 'created_at' => now()]);

        $query = $this->service->publishedPostsQuery();
        $sorted = $this->service->applySort($query, 'latest');
        $results = $sorted->get();

        $this->assertTrue($results->first()->created_at >= $results->last()->created_at);
    }

    public function test_paginate_posts(): void
    {
        Post::factory()->count(5)->create(['status' => 'published', 'post_type' => 'post']);

        $query = $this->service->publishedPostsQuery();
        $paginated = $this->service->paginatePosts($query, 2);

        $this->assertEquals(2, $paginated->perPage());
        $this->assertEquals(5, $paginated->total());
    }
}
