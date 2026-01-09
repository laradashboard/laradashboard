<?php

declare(strict_types=1);

use App\Http\Middleware\VerifyCsrfToken;
use App\Livewire\Dashboard\RecentPosts;
use App\Models\Permission;
use App\Models\Post;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware(VerifyCsrfToken::class);

    // Create admin user with permissions
    $this->admin = User::factory()->create();
    $adminRole = Role::firstOrCreate(['name' => 'Superadmin', 'guard_name' => 'web']);

    // Create necessary permissions
    Permission::firstOrCreate(['name' => 'post.create', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'post.view', 'guard_name' => 'web']);

    $adminRole->syncPermissions(['post.create', 'post.view']);
    $this->admin->assignRole($adminRole);
});

it('renders recent posts component successfully', function () {
    $this->actingAs($this->admin);

    Livewire::test(RecentPosts::class)
        ->assertStatus(200)
        ->assertSee(__('Recent Posts'));
});

it('displays empty state when no posts exist', function () {
    $this->actingAs($this->admin);

    Livewire::test(RecentPosts::class)
        ->assertSee(__('No posts yet'));
});

it('displays recent posts', function () {
    $this->actingAs($this->admin);

    Post::factory()->create([
        'title' => 'My First Test Post',
        'post_type' => 'post',
        'user_id' => $this->admin->id,
    ]);

    Livewire::test(RecentPosts::class)
        ->assertSee('My First Test Post');
});

it('limits posts based on limit parameter', function () {
    $this->actingAs($this->admin);

    Post::factory()->count(10)->create([
        'post_type' => 'post',
        'user_id' => $this->admin->id,
    ]);

    $component = Livewire::test(RecentPosts::class, ['limit' => 3]);

    // The component should only load 3 posts
    expect($component->instance()->posts)->toHaveCount(3);
});

it('shows only posts not pages', function () {
    $this->actingAs($this->admin);

    Post::factory()->create([
        'title' => 'This is a post',
        'post_type' => 'post',
        'user_id' => $this->admin->id,
    ]);

    Post::factory()->create([
        'title' => 'This is a page',
        'post_type' => 'page',
        'user_id' => $this->admin->id,
    ]);

    Livewire::test(RecentPosts::class)
        ->assertSee('This is a post')
        ->assertDontSee('This is a page');
});

it('shows posts ordered by latest first', function () {
    $this->actingAs($this->admin);

    $oldPost = Post::factory()->create([
        'title' => 'Old Post',
        'post_type' => 'post',
        'user_id' => $this->admin->id,
        'created_at' => now()->subDays(5),
    ]);

    $newPost = Post::factory()->create([
        'title' => 'New Post',
        'post_type' => 'post',
        'user_id' => $this->admin->id,
        'created_at' => now(),
    ]);

    $component = Livewire::test(RecentPosts::class);
    $posts = $component->instance()->posts;

    expect($posts->first()->title)->toBe('New Post');
    expect($posts->last()->title)->toBe('Old Post');
});

it('refreshes posts when draft-created event is dispatched', function () {
    $this->actingAs($this->admin);

    $component = Livewire::test(RecentPosts::class);

    // Initially no posts
    expect($component->instance()->posts)->toHaveCount(0);

    // Create a post
    Post::factory()->create([
        'title' => 'Newly Created Post',
        'post_type' => 'post',
        'user_id' => $this->admin->id,
    ]);

    // Dispatch the event to refresh
    $component->dispatch('draft-created');

    // Now should have the post
    expect($component->instance()->posts)->toHaveCount(1);
});

it('displays post status badge', function () {
    $this->actingAs($this->admin);

    Post::factory()->create([
        'title' => 'Draft Post',
        'post_type' => 'post',
        'status' => 'draft',
        'user_id' => $this->admin->id,
    ]);

    Livewire::test(RecentPosts::class)
        ->assertSee(__('Draft'));
});

it('displays published posts badge', function () {
    $this->actingAs($this->admin);

    Post::factory()->create([
        'title' => 'Published Post',
        'post_type' => 'post',
        'status' => 'published',
        'user_id' => $this->admin->id,
    ]);

    Livewire::test(RecentPosts::class)
        ->assertSee(__('Published'));
});
