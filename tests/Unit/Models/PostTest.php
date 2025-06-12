<?php

namespace Tests\Unit\Models;

use App\Models\Post;
use App\Models\PostMeta;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_has_fillable_attributes()
    {
        $post = new Post();
        $this->assertEquals([
            'user_id',
            'post_type',
            'title',
            'slug',
            'excerpt',
            'content',
            'featured_image',
            'status',
            'meta',
            'parent_id',
            'published_at',
        ], $post->getFillable());
    }

    #[Test]
    public function it_has_casted_attributes()
    {
        $post = new Post();
        $casts = $post->getCasts();
        $this->assertArrayHasKey('meta', $casts);
        $this->assertEquals('array', $casts['meta']);
        $this->assertArrayHasKey('published_at', $casts);
        $this->assertEquals('datetime', $casts['published_at']);
    }

    #[Test]
    public function it_auto_generates_slug_when_creating()
    {
        $user = User::factory()->create();

        $post = Post::create([
            'title' => 'Test Post Title',
            'post_type' => 'post',
            'content' => 'Test content',
            'status' => 'publish',
            'user_id' => $user->id,
        ]);

        $this->assertEquals('test-post-title', $post->slug);
    }

    #[Test]
    public function it_sets_user_id_from_authenticated_user_when_creating()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $post = Post::create([
            'title' => 'Test Post Title',
            'post_type' => 'post',
            'content' => 'Test content',
            'status' => 'publish',
        ]);

        $this->assertEquals($user->id, $post->user_id);
    }

    #[Test]
    public function it_has_user_relationship()
    {
        $user = User::factory()->create();
        $post = Post::create([
            'title' => 'Test Post Title',
            'post_type' => 'post',
            'content' => 'Test content',
            'status' => 'publish',
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $post->user);
        $this->assertEquals($user->id, $post->user->id);
    }

    #[Test]
    public function it_has_parent_and_children_relationships()
    {
        $user = User::factory()->create();

        $parent = Post::create([
            'title' => 'Parent Post',
            'post_type' => 'post',
            'content' => 'Parent content',
            'status' => 'publish',
            'user_id' => $user->id,
        ]);

        $child = Post::create([
            'title' => 'Child Post',
            'post_type' => 'post',
            'content' => 'Child content',
            'status' => 'publish',
            'parent_id' => $parent->id,
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf(Post::class, $child->parent);
        $this->assertEquals($parent->id, $child->parent->id);

        $this->assertCount(1, $parent->children);
        $this->assertEquals($child->id, $parent->children->first()->id);
    }

    #[Test]
    public function it_has_terms_relationship()
    {
        $user = User::factory()->create();

        $post = Post::create([
            'title' => 'Test Post',
            'post_type' => 'post',
            'content' => 'Test content',
            'status' => 'publish',
            'user_id' => $user->id,
        ]);

        $category = Term::create([
            'name' => 'Test Category',
            'taxonomy' => 'category',
        ]);

        $tag = Term::create([
            'name' => 'Test Tag',
            'taxonomy' => 'tag',
        ]);

        // Use attach with explicit model ID to avoid undefined property error
        if ($post->id && $category->id && $tag->id) {
            $post->terms()->attach([$category->id, $tag->id]);
        }

        $this->assertCount(2, $post->terms);

        // Test categories relationship
        $categories = $post->terms()->where('taxonomy', 'category')->get();
        $this->assertCount(1, $categories);

        // Test tags relationship
        $tags = $post->terms()->where('taxonomy', 'tag')->get();
        $this->assertCount(1, $tags);
    }

    #[Test]
    public function it_can_manage_post_meta()
    {
        $user = User::factory()->create();

        $post = Post::create([
            'title' => 'Test Post',
            'post_type' => 'post',
            'content' => 'Test content',
            'status' => 'publish',
            'user_id' => $user->id,
        ]);

        // Ensure post was created successfully.
        $this->assertNotNull($post);
        $this->assertInstanceOf(Post::class, $post);

        // Get the ID safely.
        $postId = $post->getKey();
        $this->assertNotNull($postId);

        // Set meta
        $meta = $post->setMeta('test_key', 'test_value');
        $this->assertInstanceOf(PostMeta::class, $meta);

        // Get meta
        $this->assertEquals('test_value', $post->getMeta('test_key'));
        $this->assertNull($post->getMeta('non_existent_key'));
        $this->assertEquals('default', $post->getMeta('non_existent_key', 'default'));

        // Update meta
        $post->setMeta('test_key', 'updated_value');
        $this->assertEquals('updated_value', $post->getMeta('test_key'));

        // Get all meta
        $allMeta = $post->getAllMetaValues();
        $this->assertEquals('updated_value', $allMeta['test_key']);

        // Delete meta
        $this->assertTrue($post->deleteMeta('test_key'));
        $this->assertNull($post->getMeta('test_key'));
    }

    #[Test]
    public function it_can_filter_by_published_status()
    {
        $user = User::factory()->create();

        // Create published post
        Post::create([
            'title' => 'Published Post',
            'post_type' => 'post',
            'content' => 'Published content',
            'status' => 'publish',
            'user_id' => $user->id,
        ]);

        // Create draft post
        Post::create([
            'title' => 'Draft Post',
            'post_type' => 'post',
            'content' => 'Draft content',
            'status' => 'draft',
            'user_id' => $user->id,
        ]);

        // Create scheduled post
        Post::create([
            'title' => 'Scheduled Post',
            'post_type' => 'post',
            'content' => 'Scheduled content',
            'status' => 'publish',
            'published_at' => now()->addDays(1),
            'user_id' => $user->id,
        ]);

        $publishedPosts = Post::published()->get();

        $this->assertCount(1, $publishedPosts);
        $this->assertEquals('Published Post', $publishedPosts->first()->title);
    }

    #[Test]
    public function it_can_filter_by_post_type()
    {
        $user = User::factory()->create();

        // Create post
        Post::create([
            'title' => 'Blog Post',
            'post_type' => 'post',
            'content' => 'Blog content',
            'status' => 'publish',
            'user_id' => $user->id,
        ]);

        // Create page
        Post::create([
            'title' => 'About Page',
            'post_type' => 'page',
            'content' => 'About content',
            'status' => 'publish',
            'user_id' => $user->id,
        ]);

        $posts = Post::type('post')->get();
        $pages = Post::type('page')->get();

        $this->assertCount(1, $posts);
        $this->assertEquals('Blog Post', $posts->first()->title);

        $this->assertCount(1, $pages);
        $this->assertEquals('About Page', $pages->first()->title);
    }

    #[Test]
    public function it_can_filter_by_category_and_tag()
    {
        $user = User::factory()->create();

        // Create posts
        $post1 = Post::create([
            'title' => 'Post 1',
            'post_type' => 'post',
            'content' => 'Content 1',
            'status' => 'publish',
            'user_id' => $user->id,
        ]);

        $post2 = Post::create([
            'title' => 'Post 2',
            'post_type' => 'post',
            'content' => 'Content 2',
            'status' => 'publish',
            'user_id' => $user->id,
        ]);

        // Create category and tag
        $category = Term::create([
            'name' => 'Test Category',
            'taxonomy' => 'category',
        ]);

        $tag = Term::create([
            'name' => 'Test Tag',
            'taxonomy' => 'tag',
        ]);

        // Attach terms
        $post1->terms()->attach($category->id);
        $post2->terms()->attach($tag->id);

        // Filter by category
        $categoryPosts = Post::filterByCategory($category->id)->get();
        $this->assertCount(1, $categoryPosts);
        $this->assertEquals('Post 1', $categoryPosts->first()->title);

        // Filter by tag
        $tagPosts = Post::filterByTag($tag->id)->get();
        $this->assertCount(1, $tagPosts);
        $this->assertEquals('Post 2', $tagPosts->first()->title);
    }

    #[Test]
    public function it_has_searchable_columns()
    {
        $post = new Post();
        $reflection = new \ReflectionClass($post);
        $method = $reflection->getMethod('getSearchableColumns');
        $method->setAccessible(true);

        $searchableColumns = $method->invoke($post);
        $this->assertEquals(['title', 'excerpt', 'content'], $searchableColumns);
    }

    #[Test]
    public function it_has_excluded_sort_columns()
    {
        $post = new Post();
        $reflection = new \ReflectionClass($post);
        $method = $reflection->getMethod('getExcludedSortColumns');
        $method->setAccessible(true);

        $this->assertEquals(['content', 'excerpt', 'meta'], $method->invoke($post));
    }
}
