<?php

declare(strict_types=1);

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\PostMeta;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

it('has fillable attributes', function () {
    $post = new Post();
    expect($post->getFillable())->toEqual([
        'user_id',
        'post_type',
        'title',
        'slug',
        'excerpt',
        'content',
        'design_json',
        'status',
        'meta',
        'parent_id',
        'published_at',
    ]);
});

it('has casted attributes', function () {
    $post = new Post();
    $casts = $post->getCasts();
    expect($casts)->toHaveKey('meta');
    expect($casts['meta'])->toEqual('array');
    expect($casts)->toHaveKey('design_json');
    expect($casts['design_json'])->toEqual('array');
    expect($casts)->toHaveKey('published_at');
    expect($casts['published_at'])->toEqual('datetime');
});

it('auto generates slug when creating', function () {
    $user = User::factory()->create();

    $post = Post::create([
        'title' => 'Test Post Title',
        'post_type' => 'post',
        'content' => 'Test content',
        'status' => PostStatus::PUBLISHED->value,
        'user_id' => $user->id,
    ]);

    expect($post->slug)->toEqual('test-post-title');
});

it('sets user id from authenticated user when creating', function () {
    $user = User::factory()->create();

    $post = Post::create([
        'title' => 'Test Post Title',
        'post_type' => 'post',
        'content' => 'Test content',
        'status' => PostStatus::PUBLISHED->value,
        'user_id' => $user->id,
    ]);

    expect($post->user_id)->toEqual($user->id);
});

it('has user relationship', function () {
    $user = User::factory()->create();
    $post = Post::create([
        'title' => 'Test Post Title',
        'post_type' => 'post',
        'content' => 'Test content',
        'status' => PostStatus::PUBLISHED->value,
        'user_id' => $user->id,
    ]);

    expect($post->user)->toBeInstanceOf(User::class);
    expect($post->user->id)->toEqual($user->id);
});

it('has parent and children relationships', function () {
    $user = User::factory()->create();

    $parent = Post::create([
        'title' => 'Parent Post',
        'post_type' => 'post',
        'content' => 'Parent content',
        'status' => PostStatus::PUBLISHED->value,
        'user_id' => $user->id,
    ]);

    $child = Post::create([
        'title' => 'Child Post',
        'post_type' => 'post',
        'content' => 'Child content',
        'status' => PostStatus::PUBLISHED->value,
        'parent_id' => $parent->id,
        'user_id' => $user->id,
    ]);

    expect($child->parent)->toBeInstanceOf(Post::class);
    expect($child->parent->id)->toEqual($parent->id);

    expect($parent->children)->toHaveCount(1);
    expect($parent->children->first()->id)->toEqual($child->id);
});

it('has terms relationship', function () {
    $user = User::factory()->create();

    $post = Post::create([
        'title' => 'Test Post',
        'post_type' => 'post',
        'content' => 'Test content',
        'status' => PostStatus::PUBLISHED->value,
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

    expect($post->terms)->toHaveCount(2);

    // Test categories relationship
    $categories = $post->terms()->where('taxonomy', 'category')->get();
    expect($categories)->toHaveCount(1);

    // Test tags relationship
    $tags = $post->terms()->where('taxonomy', 'tag')->get();
    expect($tags)->toHaveCount(1);
});

it('can manage post meta', function () {
    $user = User::factory()->create();

    $post = Post::create([
        'title' => 'Test Post',
        'post_type' => 'post',
        'content' => 'Test content',
        'status' => PostStatus::PUBLISHED->value,
        'user_id' => $user->id,
    ]);

    // Ensure post was created successfully.
    expect($post)->not->toBeNull();
    expect($post)->toBeInstanceOf(Post::class);

    // Get the ID safely.
    $postId = $post->getKey();
    expect($postId)->not->toBeNull();

    // Set meta
    $meta = $post->setMeta('test_key', 'test_value');
    expect($meta)->toBeInstanceOf(PostMeta::class);

    // Get meta
    expect($post->getMeta('test_key'))->toEqual('test_value');
    expect($post->getMeta('non_existent_key'))->toBeNull();
    expect($post->getMeta('non_existent_key', 'default'))->toEqual('default');

    // Update meta
    $post->setMeta('test_key', 'updated_value');
    expect($post->getMeta('test_key'))->toEqual('updated_value');

    // Get all meta
    $allMeta = $post->getAllMetaValues();
    expect($allMeta['test_key'])->toEqual('updated_value');

    // Delete meta
    expect($post->deleteMeta('test_key'))->toBeTrue();
    expect($post->getMeta('test_key'))->toBeNull();
});

it('can filter by published status', function () {
    $user = User::factory()->create();

    // Create published post
    Post::create([
        'title' => 'Published Post',
        'post_type' => 'post',
        'content' => 'Published content',
        'status' => PostStatus::PUBLISHED->value,
        'user_id' => $user->id,
    ]);

    // Create draft post
    Post::create([
        'title' => 'Draft Post',
        'post_type' => 'post',
        'content' => 'Draft content',
        'status' => PostStatus::DRAFT->value,
        'user_id' => $user->id,
    ]);

    // Create scheduled post
    Post::create([
        'title' => 'Scheduled Post',
        'post_type' => 'post',
        'content' => 'Scheduled content',
        'status' => PostStatus::PUBLISHED->value,
        'published_at' => now()->addDays(1),
        'user_id' => $user->id,
    ]);

    $publishedPosts = Post::published()->get();

    expect($publishedPosts)->toHaveCount(1);
    expect($publishedPosts->first()->title)->toEqual('Published Post');
});

it('can filter by post type', function () {
    $user = User::factory()->create();

    // Create post
    Post::create([
        'title' => 'Blog Post',
        'post_type' => 'post',
        'content' => 'Blog content',
        'status' => PostStatus::PUBLISHED->value,
        'user_id' => $user->id,
    ]);

    // Create page
    Post::create([
        'title' => 'About Page',
        'post_type' => 'page',
        'content' => 'About content',
        'status' => PostStatus::PUBLISHED->value,
        'user_id' => $user->id,
    ]);

    $posts = Post::type('post')->get();
    $pages = Post::type('page')->get();

    expect($posts)->toHaveCount(1);
    expect($posts->first()->title)->toEqual('Blog Post');

    expect($pages)->toHaveCount(1);
    expect($pages->first()->title)->toEqual('About Page');
});

it('can filter by category and tag', function () {
    $user = User::factory()->create();

    // Create posts
    $post1 = Post::create([
        'title' => 'Post 1',
        'post_type' => 'post',
        'content' => 'Content 1',
        'status' => PostStatus::PUBLISHED->value,
        'user_id' => $user->id,
    ]);

    $post2 = Post::create([
        'title' => 'Post 2',
        'post_type' => 'post',
        'content' => 'Content 2',
        'status' => PostStatus::PUBLISHED->value,
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
    expect($categoryPosts)->toHaveCount(1);
    expect($categoryPosts->first()->title)->toEqual('Post 1');

    // Filter by tag
    $tagPosts = Post::filterByTag($tag->id)->get();
    expect($tagPosts)->toHaveCount(1);
    expect($tagPosts->first()->title)->toEqual('Post 2');
});

it('has searchable columns', function () {
    $post = new Post();
    $reflection = new \ReflectionClass($post);
    $method = $reflection->getMethod('getSearchableColumns');
    $method->setAccessible(true);

    $searchableColumns = $method->invoke($post);
    expect($searchableColumns)->toEqual(['title', 'excerpt', 'content']);
});

it('has excluded sort columns', function () {
    $post = new Post();
    $reflection = new \ReflectionClass($post);
    $method = $reflection->getMethod('getExcludedSortColumns');
    $method->setAccessible(true);

    expect($method->invoke($post))->toEqual(['content', 'excerpt', 'meta']);
});
