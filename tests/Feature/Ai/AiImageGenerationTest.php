<?php

declare(strict_types=1);

use App\Services\AiContentGeneratorService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/*
 * DALL-E 3 was retired from the OpenAI API on 2026-05-12. Image generation now uses
 * the GPT Image models, which return base64 instead of a temporary URL.
 */

beforeEach(function () {
    config(['settings.ai_openai_api_key' => 'test-key', 'settings.ai_openai_image_model' => null]);
    Storage::fake('public');
});

function fakePng(): string
{
    $image = imagecreatetruecolor(4, 4);
    ob_start();
    imagepng($image);

    return base64_encode((string) ob_get_clean());
}

test('generateImage uses the gpt image model and maps legacy sizes', function () {
    Http::fake(['api.openai.com/*' => Http::response(['data' => [['b64_json' => fakePng()]]])]);

    $result = app(AiContentGeneratorService::class)->generateImage('A dashboard', '1792x1024');

    expect($result['b64_json'])->not->toBeEmpty()->and($result['format'])->toBe('webp');

    Http::assertSent(fn (Request $request) => $request['model'] === 'gpt-image-2.5-flare'
        && $request['size'] === '1536x1024'
        && $request['output_format'] === 'webp'
        && ! str_contains(json_encode($request->data()), 'dall-e'));
});

test('the image model can be changed from settings', function () {
    config(['settings.ai_openai_image_model' => 'gpt-image-2.5-sunburst']);
    Http::fake(['api.openai.com/*' => Http::response(['data' => [['b64_json' => fakePng()]]])]);

    app(AiContentGeneratorService::class)->generateImage('A dashboard');

    Http::assertSent(fn (Request $request) => $request['model'] === 'gpt-image-2.5-sunburst');
});

test('a base64 result is stored on the public disk', function () {
    $url = app(AiContentGeneratorService::class)->storeGeneratedImage(['b64_json' => fakePng(), 'format' => 'png']);

    expect($url)->toContain('/storage/posts/images/ai_')->toEndWith('.png');
    expect(Storage::disk('public')->allFiles('posts/images'))->toHaveCount(1);
});

test('invalid base64 is not stored', function () {
    expect(app(AiContentGeneratorService::class)->storeGeneratedImage(['b64_json' => base64_encode('not an image')]))->toBeNull();
    expect(Storage::disk('public')->allFiles('posts/images'))->toBeEmpty();
});

test('a url result is still downloaded and stored', function () {
    Http::fake(['images.example.com/*' => Http::response(base64_decode(fakePng()))]);

    $url = app(AiContentGeneratorService::class)->storeGeneratedImage(['url' => 'https://images.example.com/a.png']);

    expect($url)->toContain('/storage/posts/images/');
});

test('a failed api call returns null', function () {
    Http::fake(['api.openai.com/*' => Http::response(['error' => ['message' => 'The model does not exist']], 404)]);

    expect(app(AiContentGeneratorService::class)->generateImage('A dashboard'))->toBeNull();
});
