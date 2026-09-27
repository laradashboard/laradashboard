<?php

declare(strict_types=1);

use App\Enums\PostStatus;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Post;
use App\Models\User;
use App\Services\Content\ContentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    test()->withoutMiddleware(VerifyCsrfToken::class);

    Permission::firstOrCreate(['name' => 'post.view', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'post.create', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'post.edit', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'post.publish', 'guard_name' => 'web']);

    $authorRole = Role::firstOrCreate(['name' => 'author', 'guard_name' => 'web']);
    $authorRole->syncPermissions(['post.view', 'post.create', 'post.edit']);

    test()->author = User::factory()->create();
    test()->author->assignRole($authorRole);

    $publisherRole = Role::firstOrCreate(['name' => 'publisher', 'guard_name' => 'web']);
    $publisherRole->syncPermissions(['post.view', 'post.create', 'post.edit', 'post.publish']);

    test()->publisher = User::factory()->create();
    test()->publisher->assignRole($publisherRole);

    app(ContentService::class)->registerPostType([
        'name' => 'post',
        'label' => 'Posts',
        'label_singular' => 'Post',
        'taxonomies' => ['category', 'tag'],
    ]);
});

test('author cannot self publish via builder store', function () {
    $response = test()->actingAs(test()->author)
        ->postJson('/admin/posts/post', [
            'title' => 'Self published',
            'content' => '<p>Content</p>',
            'status' => PostStatus::PUBLISHED->value,
        ]);

    $response->assertOk()
        ->assertJson(['success' => true]);

    test()->assertDatabaseHas('posts', [
        'title' => 'Self published',
        'status' => PostStatus::PENDING->value,
    ]);

    test()->assertDatabaseMissing('posts', [
        'title' => 'Self published',
        'status' => PostStatus::PUBLISHED->value,
    ]);
});

test('author cannot self publish via builder update', function () {
    $post = Post::factory()->create([
        'title' => 'Draft post',
        'post_type' => 'post',
        'status' => PostStatus::DRAFT->value,
        'user_id' => test()->author->id,
    ]);

    $response = test()->actingAs(test()->author)
        ->putJson("/admin/posts/post/{$post->id}", [
            'title' => 'Draft post',
            'content' => '<p>Updated</p>',
            'status' => PostStatus::PUBLISHED->value,
        ]);

    $response->assertOk();

    expect($post->fresh()->status)->toBe(PostStatus::PENDING->value);
});

test('author cannot unpublish live content via builder update', function () {
    $post = Post::factory()->create([
        'title' => 'Live post',
        'post_type' => 'post',
        'status' => PostStatus::PUBLISHED->value,
        'user_id' => test()->author->id,
        'published_at' => now(),
    ]);

    test()->actingAs(test()->author)
        ->putJson("/admin/posts/post/{$post->id}", [
            'title' => 'Live post',
            'content' => '<p>Edited body</p>',
            'status' => PostStatus::DRAFT->value,
        ])
        ->assertOk();

    $post->refresh();

    expect($post->status)->toBe(PostStatus::PUBLISHED->value)
        ->and($post->content)->toBe('<p>Edited body</p>');
});

test('publisher can publish via builder update', function () {
    $post = Post::factory()->create([
        'title' => 'Review post',
        'post_type' => 'post',
        'status' => PostStatus::PENDING->value,
        'user_id' => test()->author->id,
    ]);

    test()->actingAs(test()->publisher)
        ->putJson("/admin/posts/post/{$post->id}", [
            'title' => 'Review post',
            'content' => '<p>Approved</p>',
            'status' => PostStatus::PUBLISHED->value,
        ])
        ->assertOk();

    $post->refresh();

    expect($post->status)->toBe(PostStatus::PUBLISHED->value)
        ->and($post->published_at)->not->toBeNull();
});
