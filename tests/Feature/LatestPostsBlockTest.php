<?php

declare(strict_types=1);

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

pest()->use(RefreshDatabase::class);

test('latest-posts block render.php exists and returns callable', function () {
    $renderPath = resource_path('js/lara-builder/blocks/latest-posts/render.php');

    expect(file_exists($renderPath))->toBeTrue();

    $callback = require $renderPath;

    expect($callback)->toBeCallable();
});

test('latest-posts block renders posts in grid', function () {
    $user = User::create(['first_name' => 'Test', 'last_name' => 'User', 'email' => 'test' . uniqid() . '@example.com', 'username' => 'testuser_' . uniqid(), 'password' => Hash::make('password')]);
    for ($i = 1; $i <= 3; $i++) {
        Post::create([
            'title' => "Grid Post {$i}",
            'slug' => "grid-post-{$i}",
            'post_type' => 'post',
            'status' => 'published',
            'published_at' => now(),
            'user_id' => $user->id,
            'content' => "Content for grid post {$i}.",
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    $callback = require resource_path('js/lara-builder/blocks/latest-posts/render.php');

    $html = $callback([
        'postsCount' => 3,
        'columns' => 3,
        'showExcerpt' => true,
        'showImage' => true,
        'showDate' => true,
        'layout' => 'grid',
    ], 'page');

    expect($html)
        ->toContain('<section')
        ->toContain('<article')
        ->toContain('aria-label');
});

test('latest-posts block returns empty when no posts exist', function () {
    $callback = require resource_path('js/lara-builder/blocks/latest-posts/render.php');

    $html = $callback([
        'postsCount' => 6,
    ], 'page');

    expect($html)->toBe('');
});

test('latest-posts block renders with heading text', function () {
    Post::create([
        'title' => 'Heading Test Post',
        'slug' => 'heading-test-post',
        'post_type' => 'post',
        'status' => 'published',
        'published_at' => now(),
        'user_id' => User::create(['first_name' => 'Test', 'last_name' => 'User', 'email' => 'test' . uniqid() . '@example.com', 'username' => 'testuser_' . uniqid(), 'password' => Hash::make('password')])->id,
        'content' => 'Content for heading test.',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $callback = require resource_path('js/lara-builder/blocks/latest-posts/render.php');

    $html = $callback([
        'postsCount' => 3,
        'headingText' => 'Featured Articles',
        'layout' => 'grid',
    ], 'page');

    expect($html)
        ->toContain('Featured Articles')
        ->toContain('<h2');
});

test('latest-posts block supports list layout', function () {
    Post::create([
        'title' => 'List Layout Post',
        'slug' => 'list-layout-post',
        'post_type' => 'post',
        'status' => 'published',
        'published_at' => now(),
        'user_id' => User::create(['first_name' => 'Test', 'last_name' => 'User', 'email' => 'test' . uniqid() . '@example.com', 'username' => 'testuser_' . uniqid(), 'password' => Hash::make('password')])->id,
        'content' => 'Content for list layout test.',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $callback = require resource_path('js/lara-builder/blocks/latest-posts/render.php');

    $html = $callback([
        'postsCount' => 3,
        'layout' => 'list',
    ], 'page');

    expect($html)->toContain('st:space-y-6');
});

test('latest-posts block respects post count limit', function () {
    $user = User::create(['first_name' => 'Test', 'last_name' => 'User', 'email' => 'test' . uniqid() . '@example.com', 'username' => 'testuser_' . uniqid(), 'password' => Hash::make('password')]);
    for ($i = 1; $i <= 5; $i++) {
        Post::create([
            'title' => "Limit Post {$i}",
            'slug' => "limit-post-{$i}",
            'post_type' => 'post',
            'status' => 'published',
            'published_at' => now(),
            'user_id' => $user->id,
            'content' => "Content for limit post {$i}.",
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    $callback = require resource_path('js/lara-builder/blocks/latest-posts/render.php');

    $html = $callback([
        'postsCount' => 2,
        'layout' => 'grid',
    ], 'page');

    expect(substr_count($html, '<article'))->toBe(2);
});
