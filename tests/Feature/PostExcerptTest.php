<?php

declare(strict_types=1);

use App\Models\Post;
use App\Models\User;
use App\Services\Builder\PostBuilderService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('an empty excerpt is generated from builder content, not left blank', function () {
    $content = '<div class="lb-content"><div data-lara-block="text" data-props=\'{"content":"Kitecraft ships a new release every Friday."}\'></div></div>';

    expect(app(PostBuilderService::class)->resolveExcerpt('', $content))->toBe('Kitecraft ships a new release every Friday.');
});

test('plain text drops style and script blocks', function () {
    $html = '<style>/* Lara Builder content typography tokens */ .lb{color:red}</style><p>Hello&nbsp;<b>world</b></p><script>alert(1)</script><p>Again</p>';

    expect(app(PostBuilderService::class)->plainText($html))->toBe('Hello world Again');
});

test('listing excerpts fall back to readable text from the rendered post', function () {
    $post = Post::create([
        'title' => 'Builder post', 'slug' => 'builder-post', 'post_type' => 'post', 'status' => 'published',
        'user_id' => User::factory()->create()->id, 'excerpt' => '', 'content' => '',
        'design_json' => ['blocks' => [['id' => 't1', 'type' => 'text', 'props' => ['content' => 'Plan on Monday, ship on Friday.']]]],
    ]);

    expect($post->plainExcerpt())->toBe('Plan on Monday, ship on Friday.')
        ->and($post->plainExcerpt())->not->toContain('typography');
});
