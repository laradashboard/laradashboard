<?php

declare(strict_types=1);

use App\Enums\PostStatus;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Post;
use App\Models\Term;
use App\Models\Taxonomy;
use App\Models\User;
use App\Services\Content\ContentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    // Disable CSRF protection for tests.
    $this->withoutMiddleware(VerifyCsrfToken::class);

    // Create admin user with permissions
    $admin = User::factory()->create([
        'first_name' => 'Admin',
        'last_name' => 'User',
        'email' => 'adminuser@example.com',
        'username' => 'admin_user',
    ]);

    $adminRole = Role::firstOrCreate(['name' => 'content-admin', 'guard_name' => 'web']);

    // Create necessary permissions.
    Permission::firstOrCreate(['name' => 'post.view', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'post.create', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'post.edit', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'post.delete', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'term.view', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'term.create', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'term.edit', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'term.delete', 'guard_name' => 'web']);

    $adminRole->syncPermissions([
        'post.view', 'post.create', 'post.edit', 'post.delete',
        'term.view', 'term.create', 'term.edit', 'term.delete',
    ]);

    $admin->assignRole($adminRole);
    test()->admin = $admin;
    test()->postType = 'post';

    // Register post type for testing
    $contentService = app(ContentService::class);
    $contentService->registerPostType([
        'name' => 'post',
        'label' => 'Posts',
        'label_singular' => 'Post',
        'taxonomies' => ['category', 'tag'],
    ]);

    // Register taxonomies by creating taxonomy records directly
    Taxonomy::firstOrCreate(
        ['name' => 'category'],
        [
            'label' => 'Categories',
            'label_singular' => 'Category',
            'hierarchical' => true,
            'show_in_menu' => true,
            'post_types' => ['post'],
        ]
    );

    Taxonomy::firstOrCreate(
        ['name' => 'tag'],
        [
            'label' => 'Tags',
            'label_singular' => 'Tag',
            'hierarchical' => false,
            'show_in_menu' => true,
            'post_types' => ['post'],
        ]
    );

    // Create a more comprehensive mock view for terms.index
    View::addNamespace('backend', resource_path('views/backend'));

    // Add a fake implementation of the view with all required variables
    // Use a LengthAwarePaginator instead of a Collection for 'terms'
    View::composer('backend.pages.terms.index', function ($view) {
        // Create a paginator with an empty array
        $paginator = new LengthAwarePaginator(
            [], // Items array (empty for this test)
            0,  // Total items
            10, // Per page
            1   // Current page
        );

        // Set path for proper link generation
        $paginator->setPath(request()->url());

        $view->with([
            'term' => null,
            'terms' => $paginator, // Use paginator instead of collection
            'taxonomy' => 'category',
            'taxonomyInfo' => new Taxonomy([
                'name' => 'category',
                'label' => 'Categories',
                'label_singular' => 'Category',
                'hierarchical' => true,
            ]),
            'taxonomyModel' => new Taxonomy([
                'name' => 'category',
                'label' => 'Categories',
                'label_singular' => 'Category',
                'hierarchical' => true,
            ]),
            'parentTerms' => [],
            'breadcrumbs' => [
                'title' => 'Categories',
            ],
        ]);
    });
});

test('admin can view posts list', function () {
    $response = test()->actingAs(test()->admin)
        ->get("/admin/posts/" . test()->postType);

    $response->assertStatus(200)
        ->assertViewIs('backend.pages.posts.index');
});

test('admin can create post', function () {
    // Create taxonomy terms.
    $category = Term::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'taxonomy' => 'category',
    ]);

    $tag = Term::create([
        'name' => 'Test Tag',
        'slug' => 'test-tag',
        'taxonomy' => 'tag',
    ]);

    // Now using LaraBuilder JSON API
    $response = test()->actingAs(test()->admin)
        ->postJson("/admin/posts/" . test()->postType, [
            'title' => 'Test Post Title',
            'slug' => 'test-post-title',
            'content' => 'Test post content',
            'excerpt' => 'Test excerpt',
            'status' => PostStatus::PUBLISHED->value,
            'taxonomy_category' => [$category->id],
            'taxonomy_tag' => [$tag->id],
        ]);

    $response->assertOk()
        ->assertJson(['success' => true]);

    test()->assertDatabaseHas('posts', [
        'title' => 'Test Post Title',
        'slug' => 'test-post-title',
        'content' => 'Test post content',
        'post_type' => test()->postType,
        'status' => PostStatus::PUBLISHED->value,
    ]);

    $post = Post::where('title', 'Test Post Title')->first();
    expect($post)->not->toBeNull();

    // Check if the post has the category and tag attached.
    expect($post->terms()->where('taxonomy', 'category')->exists())->toBeTrue();
    expect($post->terms()->where('taxonomy', 'tag')->exists())->toBeTrue();
});

test('admin can update post', function () {
    $post = Post::create([
        'title' => 'Original Title',
        'slug' => 'original-title',
        'content' => 'Original content',
        'post_type' => test()->postType,
        'status' => PostStatus::DRAFT->value,
        'user_id' => test()->admin->id,
    ]);

    // Now using LaraBuilder JSON API
    $response = test()->actingAs(test()->admin)
        ->putJson("/admin/posts/" . test()->postType . "/{$post->id}", [
            'title' => 'Updated Title',
            'slug' => 'updated-title',
            'content' => 'Updated content',
            'excerpt' => 'Updated excerpt',
            'status' => PostStatus::PUBLISHED->value,
        ]);

    $response->assertOk()
        ->assertJson(['success' => true]);

    test()->assertDatabaseHas('posts', [
        'id' => $post->id,
        'title' => 'Updated Title',
        'slug' => 'updated-title',
        'content' => 'Updated content',
        'status' => PostStatus::PUBLISHED->value,
    ]);
});

test('admin can delete post', function () {
    $post = Post::create([
        'title' => 'Post to Delete',
        'slug' => 'post-to-delete',
        'content' => 'Content to delete',
        'post_type' => test()->postType,
        'status' => PostStatus::PUBLISHED->value,
        'user_id' => test()->admin->id,
    ]);

    $response = test()->actingAs(test()->admin)
        ->delete("/admin/posts/" . test()->postType . "/{$post->id}", [
            '_token' => csrf_token(),
        ]);

    $response->assertRedirect();
    test()->assertDatabaseMissing('posts', ['id' => $post->id]);
});

test('admin can view categories', function () {
    $response = test()->actingAs(test()->admin)
        ->get('/admin/terms/category');

    $response->assertStatus(200)
        ->assertViewIs('backend.pages.terms.index');
});

test('admin can create category', function () {
    $response = test()->actingAs(test()->admin)
        ->post('/admin/terms/category', [
            'name' => 'New Category',
            'slug' => 'new-category',
            'description' => 'New category description',
            '_token' => csrf_token(),
        ]);

    $response->assertRedirect();
    test()->assertDatabaseHas('terms', [
        'name' => 'New Category',
        'slug' => 'new-category',
        'description' => 'New category description',
        'taxonomy' => 'category',
    ]);
});

test('admin can update category', function () {
    $category = Term::create([
        'name' => 'Original Category',
        'slug' => 'original-category',
        'taxonomy' => 'category',
        'description' => 'Original description',
    ]);

    $response = test()->actingAs(test()->admin)
        ->put("/admin/terms/category/{$category->id}", [
            'name' => 'Updated Category',
            'slug' => 'updated-category',
            'description' => 'Updated description',
            'taxonomy' => 'category', // Explicitly provide taxonomy
            '_token' => csrf_token(),
        ]);

    $response->assertRedirect();
    test()->assertDatabaseHas('terms', [
        'id' => $category->id,
        'name' => 'Updated Category',
        'slug' => 'updated-category',
        'description' => 'Updated description',
    ]);
});

test('admin can delete category', function () {
    $category = Term::create([
        'name' => 'Category to Delete',
        'slug' => 'category-to-delete',
        'taxonomy' => 'category',
    ]);

    $response = test()->actingAs(test()->admin)
        ->delete("/admin/terms/category/{$category->id}", [
            '_token' => csrf_token(),
        ]);

    $response->assertRedirect();
    test()->assertDatabaseMissing('terms', ['id' => $category->id]);
});

test('user without permission cannot manage content', function () {
    $user = User::factory()->create();

    test()->actingAs($user)
        ->get("/admin/posts/" . test()->postType)
        ->assertStatus(403);

    // Now using LaraBuilder JSON API
    test()->actingAs($user)
        ->postJson("/admin/posts/" . test()->postType, [
            'title' => 'Unauthorized Post',
            'content' => 'Unauthorized content',
            'status' => PostStatus::PUBLISHED->value,
        ])
        ->assertStatus(403);

    test()->actingAs($user)
        ->get('/admin/terms/category')
        ->assertStatus(403);
});
