<?php

declare(strict_types=1);

use App\Services\Builder\DesignJsonRenderer;

function renderVideoBlock(array $props, string $context = 'page'): string
{
    $callback = require resource_path('js/lara-builder/blocks/video/render.php');

    return $callback($props, $context);
}

test('page context embeds a youtube player instead of a thumbnail link', function () {
    $html = renderVideoBlock(['videoUrl' => 'https://www.youtube.com/watch?v=7oGezg4uIos', 'alt' => 'Tutorial']);

    expect($html)
        ->toContain('<iframe src="https://www.youtube.com/embed/7oGezg4uIos?rel=0"')
        ->toContain('title="Tutorial"')
        ->toContain('loading="lazy"')
        ->toContain('allowfullscreen')
        ->not->toContain('<a href=');
});

test('page context embeds youtu.be, vimeo and dailymotion urls', function (string $url, string $embed) {
    expect(renderVideoBlock(['videoUrl' => $url]))->toContain('<iframe src="'.$embed.'"');
})->with([
    'youtu.be' => ['https://youtu.be/7oGezg4uIos', 'https://www.youtube.com/embed/7oGezg4uIos?rel=0'],
    'shorts' => ['https://www.youtube.com/shorts/7oGezg4uIos', 'https://www.youtube.com/embed/7oGezg4uIos?rel=0'],
    'vimeo' => ['https://vimeo.com/123456789', 'https://player.vimeo.com/video/123456789'],
    'dailymotion' => ['https://www.dailymotion.com/video/x8abc12', 'https://www.dailymotion.com/embed/video/x8abc12'],
]);

test('page context uses a native player for direct video files', function () {
    $html = renderVideoBlock(['videoUrl' => 'https://example.com/clip.mp4', 'thumbnailUrl' => 'https://example.com/poster.jpg']);

    expect($html)
        ->toContain('<video src="https://example.com/clip.mp4" poster="https://example.com/poster.jpg" controls')
        ->not->toContain('<iframe');
});

test('page context falls back to a linked thumbnail for unknown urls', function () {
    $html = renderVideoBlock(['videoUrl' => 'https://example.com/watch/42', 'thumbnailUrl' => 'https://example.com/thumb.jpg']);

    expect($html)
        ->toContain('<a href="https://example.com/watch/42"')
        ->toContain('<img src="https://example.com/thumb.jpg"')
        ->not->toContain('<iframe');
});

test('email context keeps the thumbnail link for youtube', function () {
    $html = renderVideoBlock(['videoUrl' => 'https://www.youtube.com/watch?v=7oGezg4uIos'], 'email');

    expect($html)
        ->toContain('https://img.youtube.com/vi/7oGezg4uIos/maxresdefault.jpg')
        ->not->toContain('<iframe');
});

test('design json renderer keeps the embedded iframe through sanitizing', function () {
    $html = app(DesignJsonRenderer::class)->render([
        ['id' => 'v1', 'type' => 'video', 'props' => ['videoUrl' => 'https://www.youtube.com/watch?v=7oGezg4uIos']],
    ]);

    expect($html)->toContain('<iframe src="https://www.youtube.com/embed/7oGezg4uIos?rel=0"');
});

test('page context escapes attribute values', function () {
    $html = renderVideoBlock(['videoUrl' => 'https://youtu.be/7oGezg4uIos', 'alt' => '"><script>alert(1)</script>']);

    expect($html)->not->toContain('<script>')->toContain('&quot;&gt;&lt;script&gt;');
});
