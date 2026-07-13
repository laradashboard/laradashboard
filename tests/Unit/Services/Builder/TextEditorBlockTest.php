<?php

declare(strict_types=1);

use App\Services\Builder\BlockRenderer;
use App\Services\Builder\BuilderService;
use App\Services\Builder\DesignJsonRenderer;

beforeEach(function () {
    $this->builderService = app(BuilderService::class);
    $this->blockRenderer = new BlockRenderer($this->builderService);
    $this->designJsonRenderer = app(DesignJsonRenderer::class);
});

describe('text-editor render.php via BlockRenderer', function () {
    test('processes page placeholder into lb-text-editor markup', function () {
        $props = htmlspecialchars(json_encode([
            'content' => '<p><strong>Hello</strong> world</p>',
            'align' => 'center',
            'color' => '#111111',
            'fontSize' => '18px',
            'lineHeight' => '1.6',
            'layoutStyles' => [],
        ]), ENT_QUOTES);

        $content = "<div data-lara-block=\"text-editor\" data-props='{$props}'></div>";
        $html = $this->blockRenderer->processContent($content, 'page');

        expect($html)
            ->toContain('lb-block lb-text-editor')
            ->toContain('<p><strong>Hello</strong> world</p>')
            ->toContain('text-align: center')
            ->toContain('color: #111111')
            ->toContain('font-size: 18px')
            ->not->toContain('data-lara-block="text-editor"');
    });

    test('renders email context with inline styles', function () {
        $props = htmlspecialchars(json_encode([
            'content' => '<p>Email body</p>',
            'align' => 'left',
            'color' => '#333333',
            'fontSize' => '16px',
            'lineHeight' => '1.6',
            'layoutStyles' => [],
        ]), ENT_QUOTES);

        $content = "<div data-lara-block=\"text-editor\" data-props='{$props}'></div>";
        $html = $this->blockRenderer->processContent($content, 'email');

        expect($html)
            ->toContain('<p>Email body</p>')
            ->toContain('text-align: left')
            ->toContain('font-family: Arial, Helvetica, sans-serif')
            ->not->toContain('lb-text-editor');
    });
});

describe('text-editor via DesignJsonRenderer', function () {
    test('renders text-editor block from design_json', function () {
        $html = $this->designJsonRenderer->render([
            [
                'id' => 'block-1',
                'type' => 'text-editor',
                'props' => [
                    'content' => '<p>Design json content</p>',
                    'align' => 'right',
                    'color' => '#222222',
                    'fontSize' => '20px',
                    'lineHeight' => '1.8',
                ],
            ],
        ], 'page');

        expect($html)
            ->toContain('lb-text-editor')
            ->toContain('<p>Design json content</p>')
            ->toContain('text-align: right')
            ->toContain('font-size: 20px');
    });

    test('includes list marker styles so builder and frontend show bullets and numbers', function () {
        $html = $this->designJsonRenderer->render([
            [
                'id' => 'block-1',
                'type' => 'text-editor',
                'props' => [
                    'content' => '<ul><li>One</li></ul><ol><li>Two</li></ol>',
                    'align' => 'left',
                    'color' => '#333333',
                    'fontSize' => '16px',
                    'lineHeight' => '1.6',
                ],
            ],
        ], 'page');

        expect($html)
            ->toContain('<ul><li>One</li></ul>')
            ->toContain('<ol><li>Two</li></ol>')
            ->toContain('list-style-type: disc')
            ->toContain('list-style-type: decimal')
            ->toContain('data-lb-content-styles');
    });
});
