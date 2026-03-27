<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Post;
use App\Models\Term;
use App\Services\Frontend\SeoHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoHelperTest extends TestCase
{
    use RefreshDatabase;

    private SeoHelper $seo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seo = app(SeoHelper::class);
    }

    public function test_for_post_returns_article_seo_params(): void
    {
        $post = Post::factory()->create([
            'title' => 'My Test Post',
            'content' => 'This is a great post about testing.',
            'excerpt' => 'Short excerpt.',
            'status' => 'published',
            'post_type' => 'post',
        ]);

        $params = $this->seo->forPost($post);

        $this->assertStringContainsString('My Test Post', $params['title']);
        $this->assertStringContainsString(config('app.name'), $params['title']);
        $this->assertEquals('article', $params['ogType']);
        $this->assertEquals('Short excerpt.', $params['description']);
        $this->assertSame($post, $params['_toolbarPost']);
    }

    public function test_for_post_uses_content_when_no_excerpt(): void
    {
        $post = Post::factory()->create([
            'title' => 'No Excerpt Post',
            'content' => 'This content should be used as description.',
            'excerpt' => null,
            'status' => 'published',
        ]);

        $params = $this->seo->forPost($post);

        $this->assertStringContainsString('This content should be used', $params['description']);
    }

    public function test_for_page_returns_page_seo_params(): void
    {
        $page = Post::factory()->create([
            'title' => 'About Us',
            'content' => 'About page content.',
            'status' => 'published',
            'post_type' => 'page',
        ]);

        $params = $this->seo->forPage($page);

        $this->assertStringContainsString('About Us', $params['title']);
        $this->assertArrayNotHasKey('ogType', $params);
        $this->assertSame($page, $params['_toolbarPost']);
    }

    public function test_for_term_category(): void
    {
        $term = Term::factory()->category()->create([
            'name' => 'Technology',
            'description' => 'Tech category description.',
        ]);

        $params = $this->seo->forTerm($term, 'category');

        $this->assertStringContainsString('Technology', $params['title']);
        $this->assertEquals('Tech category description.', $params['description']);
    }

    public function test_for_term_tag_has_hash_prefix(): void
    {
        $term = Term::factory()->tag()->create([
            'name' => 'PHP',
            'description' => null,
        ]);

        $params = $this->seo->forTerm($term, 'tag');

        $this->assertStringContainsString('#PHP', $params['title']);
    }

    public function test_for_search_with_query(): void
    {
        $params = $this->seo->forSearch('laravel');

        $this->assertStringContainsString('laravel', $params['title']);
        $this->assertStringContainsString(config('app.name'), $params['title']);
    }

    public function test_for_search_without_query(): void
    {
        $params = $this->seo->forSearch('');

        $this->assertStringContainsString('Search', $params['title']);
    }

    public function test_for_blog_listing_with_blog_page(): void
    {
        $blogPage = Post::factory()->create([
            'title' => 'Our Blog',
            'excerpt' => 'Read our latest articles.',
            'status' => 'published',
            'post_type' => 'page',
        ]);

        $params = $this->seo->forBlogListing($blogPage);

        $this->assertStringContainsString('Our Blog', $params['title']);
        $this->assertEquals('Read our latest articles.', $params['description']);
    }

    public function test_for_blog_listing_without_blog_page(): void
    {
        $params = $this->seo->forBlogListing();

        $this->assertStringContainsString('Posts', $params['title']);
    }

    public function test_for_homepage_with_page(): void
    {
        $page = Post::factory()->create([
            'title' => 'Welcome Home',
            'excerpt' => 'Welcome to our site.',
            'status' => 'published',
            'post_type' => 'page',
        ]);

        $params = $this->seo->forHomepage($page);

        $this->assertStringContainsString('Welcome Home', $params['title']);
        $this->assertEquals('Welcome to our site.', $params['description']);
    }

    public function test_for_homepage_without_page(): void
    {
        $params = $this->seo->forHomepage();

        $this->assertStringContainsString('Home', $params['title']);
    }

    public function test_merge_defaults_appends_app_name(): void
    {
        $params = $this->seo->mergeDefaults(['title' => 'Test']);

        $this->assertEquals('Test - ' . config('app.name'), $params['title']);
    }

    public function test_merge_defaults_does_not_double_append(): void
    {
        $appName = config('app.name');
        $params = $this->seo->mergeDefaults(['title' => 'Test - ' . $appName]);

        $this->assertEquals('Test - ' . $appName, $params['title']);
    }

    public function test_for_custom(): void
    {
        $params = $this->seo->forCustom('Custom Title', 'Custom desc', ['ogType' => 'profile']);

        $this->assertStringContainsString('Custom Title', $params['title']);
        $this->assertEquals('Custom desc', $params['description']);
        $this->assertEquals('profile', $params['ogType']);
    }
}
