<?php

namespace Tests\Feature\Admin;

use App\Models\Post;
use App\Models\Term;
use App\Models\Taxonomy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Support\Facades\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ContentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $postType = 'post';

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user with permissions
        $this->admin = User::factory()->create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@example.com',
            'username' => 'admin',
        ]);

        $adminRole = Role::create(['name' => 'admin']);

        // Create necessary permissions
        Permission::create(['name' => 'post.view']);
        Permission::create(['name' => 'post.create']);
        Permission::create(['name' => 'post.edit']);
        Permission::create(['name' => 'post.delete']);
        Permission::create(['name' => 'term.view']);
        Permission::create(['name' => 'term.create']);
        Permission::create(['name' => 'term.edit']);
        Permission::create(['name' => 'term.delete']);

        $adminRole->syncPermissions([
            'post.view', 'post.create', 'post.edit', 'post.delete',
            'term.view', 'term.create', 'term.edit', 'term.delete',
        ]);

        $this->admin->assignRole($adminRole);

        // Register post type for testing
        $contentService = app(\App\Services\Content\ContentService::class);
        $contentService->registerPostType([
            'name' => 'post',
            'label' => 'Posts',
            'label_singular' => 'Post',
            'taxonomies' => ['category', 'tag'],
        ]);

        // Register taxonomies by creating taxonomy records directly
        Taxonomy::create([
            'name' => 'category',
            'label' => 'Categories',
            'label_singular' => 'Category',
            'hierarchical' => true,
            'show_in_menu' => true,
            'post_types' => ['post'],
        ]);

        Taxonomy::create([
            'name' => 'tag',
            'label' => 'Tags',
            'label_singular' => 'Tag',
            'hierarchical' => false,
            'show_in_menu' => true,
            'post_types' => ['post'],
        ]);

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
    }

    #[Test]
    public function admin_can_view_posts_list(): void
    {
        $response = $this->actingAs($this->admin)
            ->get("/admin/posts/{$this->postType}");

        $response->assertStatus(200)
            ->assertViewIs('backend.pages.posts.index');
    }

    #[Test]
    public function admin_can_create_post(): void
    {
        // Create taxonomy terms
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

        $response = $this->actingAs($this->admin)
            ->post("/admin/posts/{$this->postType}", [
                'title' => 'Test Post Title',
                'slug' => 'test-post-title',
                'content' => 'Test post content',
                'excerpt' => 'Test excerpt',
                'status' => 'publish',
                'taxonomy_category' => [$category->id],
                'taxonomy_tag' => [$tag->id],
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('posts', [
            'title' => 'Test Post Title',
            'slug' => 'test-post-title',
            'content' => 'Test post content',
            'post_type' => $this->postType,
            'status' => 'publish',
        ]);

        $post = Post::where('title', 'Test Post Title')->first();
        $this->assertNotNull($post);

        // Check if the post has the category and tag attached
        $this->assertTrue($post->terms()->where('taxonomy', 'category')->exists());
        $this->assertTrue($post->terms()->where('taxonomy', 'tag')->exists());
    }

    #[Test]
    public function admin_can_update_post(): void
    {
        $post = Post::create([
            'title' => 'Original Title',
            'slug' => 'original-title',
            'content' => 'Original content',
            'post_type' => $this->postType,
            'status' => 'draft',
            'user_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->put("/admin/posts/{$this->postType}/{$post->id}", [
                'title' => 'Updated Title',
                'slug' => 'updated-title',
                'content' => 'Updated content',
                'excerpt' => 'Updated excerpt',
                'status' => 'publish',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated Title',
            'slug' => 'updated-title',
            'content' => 'Updated content',
            'status' => 'publish',
        ]);
    }

    #[Test]
    public function admin_can_delete_post(): void
    {
        $post = Post::create([
            'title' => 'Post to Delete',
            'slug' => 'post-to-delete',
            'content' => 'Content to delete',
            'post_type' => $this->postType,
            'status' => 'publish',
            'user_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->delete("/admin/posts/{$this->postType}/{$post->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    #[Test]
    public function admin_can_view_categories(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/terms/category');

        $response->assertStatus(200)
            ->assertViewIs('backend.pages.terms.index');
    }

    #[Test]
    public function admin_can_create_category(): void
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/terms/category', [
                'name' => 'New Category',
                'slug' => 'new-category',
                'description' => 'New category description',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('terms', [
            'name' => 'New Category',
            'slug' => 'new-category',
            'description' => 'New category description',
            'taxonomy' => 'category',
        ]);
    }

    #[Test]
    public function admin_can_update_category(): void
    {
        $category = Term::create([
            'name' => 'Original Category',
            'slug' => 'original-category',
            'taxonomy' => 'category',
            'description' => 'Original description',
        ]);

        $response = $this->actingAs($this->admin)
            ->put("/admin/terms/category/{$category->id}", [
                'name' => 'Updated Category',
                'slug' => 'updated-category',
                'description' => 'Updated description',
                'taxonomy' => 'category', // Explicitly provide taxonomy
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('terms', [
            'id' => $category->id,
            'name' => 'Updated Category',
            'slug' => 'updated-category',
            'description' => 'Updated description',
        ]);
    }

    #[Test]
    public function admin_can_delete_category(): void
    {
        $category = Term::create([
            'name' => 'Category to Delete',
            'slug' => 'category-to-delete',
            'taxonomy' => 'category',
        ]);

        $response = $this->actingAs($this->admin)
            ->delete("/admin/terms/category/{$category->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('terms', ['id' => $category->id]);
    }

    #[Test]
    public function user_without_permission_cannot_manage_content(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get("/admin/posts/{$this->postType}")
            ->assertStatus(403);

        $this->actingAs($user)
            ->post("/admin/posts/{$this->postType}", [
                'title' => 'Unauthorized Post',
                'content' => 'Unauthorized content',
                'status' => 'publish',
            ])
            ->assertStatus(403);

        $this->actingAs($user)
            ->get('/admin/terms/category')
            ->assertStatus(403);
    }
}
