<?php

declare(strict_types=1);

use App\Services\Builder\BlockRenderer;
use App\Services\Builder\BuilderService;

beforeEach(function () {
    $this->builderService = Mockery::mock(BuilderService::class);
    $this->builderService->shouldReceive('hasBlockRenderCallback')->andReturn(false);
    $this->blockRenderer = new BlockRenderer($this->builderService);
});

describe('time-to-read block render.php', function () {
    test('renders time-to-read block with default props', function () {
        $props = [
            'wordsPerMinute' => 200,
            'displayAsRange' => true,
            'prefix' => '',
            'suffix' => '',
            'align' => 'left',
            'color' => '#666666',
            'fontSize' => '14px',
            'iconColor' => '#666666',
            'showIcon' => true,
            '_wordCount' => 400, // 2 minutes at 200 WPM
        ];

        $render = require resource_path('js/lara-builder/blocks/time-to-read/render.php');
        $html = $render($props, 'page', null);

        expect($html)->toContain('lb-time-to-read')
            ->and($html)->toContain('1-2 minutes') // Range display
            ->and($html)->toContain('svg') // Clock icon
            ->and($html)->toContain('justify-content: flex-start'); // Left align
    });

    test('renders time-to-read block without range display', function () {
        $props = [
            'wordsPerMinute' => 200,
            'displayAsRange' => false,
            'prefix' => '',
            'suffix' => '',
            'align' => 'center',
            'color' => '#333333',
            'fontSize' => '16px',
            'iconColor' => '#333333',
            'showIcon' => true,
            '_wordCount' => 400, // 2 minutes at 200 WPM
        ];

        $render = require resource_path('js/lara-builder/blocks/time-to-read/render.php');
        $html = $render($props, 'page', null);

        expect($html)->toContain('2 minutes')
            ->and($html)->not->toContain('1-2')
            ->and($html)->toContain('justify-content: center');
    });

    test('renders time-to-read block with prefix and suffix', function () {
        $props = [
            'wordsPerMinute' => 200,
            'displayAsRange' => false,
            'prefix' => 'Reading time: ',
            'suffix' => ' read',
            'align' => 'left',
            'color' => '#666666',
            'fontSize' => '14px',
            'iconColor' => '#666666',
            'showIcon' => false,
            '_wordCount' => 200, // 1 minute
        ];

        $render = require resource_path('js/lara-builder/blocks/time-to-read/render.php');
        $html = $render($props, 'page', null);

        expect($html)->toContain('Reading time:')
            ->and($html)->toContain('1 minute')
            ->and($html)->toContain('read')
            ->and($html)->not->toContain('svg'); // No icon
    });

    test('renders time-to-read block without icon when showIcon is false', function () {
        $props = [
            'wordsPerMinute' => 200,
            'displayAsRange' => true,
            'showIcon' => false,
            '_wordCount' => 600,
        ];

        $render = require resource_path('js/lara-builder/blocks/time-to-read/render.php');
        $html = $render($props, 'page', null);

        expect($html)->not->toContain('svg');
    });

    test('calculates reading time correctly based on words per minute', function () {
        // Test with 100 WPM (slower reader)
        $props = [
            'wordsPerMinute' => 100,
            'displayAsRange' => false,
            '_wordCount' => 500, // Should be 5 minutes at 100 WPM
        ];

        $render = require resource_path('js/lara-builder/blocks/time-to-read/render.php');
        $html = $render($props, 'page', null);

        expect($html)->toContain('5 minutes');
    });

    test('shows minimum 1 minute for short content', function () {
        $props = [
            'wordsPerMinute' => 200,
            'displayAsRange' => false,
            '_wordCount' => 50, // Less than 1 minute worth
        ];

        $render = require resource_path('js/lara-builder/blocks/time-to-read/render.php');
        $html = $render($props, 'page', null);

        expect($html)->toContain('1 minute');
    });
});

describe('BlockRenderer word count calculation', function () {
    test('calculates word count from HTML content', function () {
        $content = '<p>This is a simple paragraph with ten words total here.</p>';

        $method = new ReflectionMethod(BlockRenderer::class, 'calculateWordCount');
        $method->setAccessible(true);
        $wordCount = $method->invoke($this->blockRenderer, $content);

        expect($wordCount)->toBe(10);
    });

    test('strips HTML tags when counting words', function () {
        $content = '<div class="wrapper"><strong>Bold</strong> and <em>italic</em> text</div>';

        $method = new ReflectionMethod(BlockRenderer::class, 'calculateWordCount');
        $method->setAccessible(true);
        $wordCount = $method->invoke($this->blockRenderer, $content);

        expect($wordCount)->toBe(4); // Bold and italic text
    });

    test('returns zero for empty content', function () {
        $method = new ReflectionMethod(BlockRenderer::class, 'calculateWordCount');
        $method->setAccessible(true);

        expect($method->invoke($this->blockRenderer, ''))->toBe(0)
            ->and($method->invoke($this->blockRenderer, '   '))->toBe(0)
            ->and($method->invoke($this->blockRenderer, '<div></div>'))->toBe(0);
    });

    test('injects word count into time-to-read block props', function () {
        $content = '<p>This is some sample text for testing the word count.</p>' .
                   '<div data-lara-block="time-to-read" data-props=\'{"wordsPerMinute":200,"displayAsRange":false}\'></div>';

        $result = $this->blockRenderer->processContent($content, 'page');

        // The rendered output should contain the calculated reading time
        expect($result)->toContain('lb-time-to-read')
            ->and($result)->toContain('1 minute');
    });
});
