<?php

declare(strict_types=1);

use App\Services\Builder\DesignJsonRenderer;

test('columns that stack on mobile ship the css that stacks them', function () {
    $html = app(DesignJsonRenderer::class)->render([
        ['id' => 'c', 'type' => 'columns', 'props' => ['columns' => 2, 'stackOnMobile' => true, 'children' => [
            [['id' => 'a', 'type' => 'text', 'props' => ['content' => 'Left']]],
            [['id' => 'b', 'type' => 'text', 'props' => ['content' => 'Right']]],
        ]]],
    ], 'page');

    expect($html)->toContain('lb-columns-stack-mobile')
        ->and($html)->toMatch('/@media \(max-width: 640px\)\s*\{\s*\.lb-columns-stack-mobile\s*\{\s*flex-direction: column;/');
});

test('large headings scale down on small screens but keep their desktop size', function () {
    $render = fn (string $size) => app(DesignJsonRenderer::class)->render([
        ['id' => 'h', 'type' => 'heading', 'props' => ['text' => 'Hi', 'level' => 'h1', 'fontSize' => $size]],
    ], 'page');

    expect($render('58px'))->toContain('font-size: min(58px, max(35px, 9vw))')
        ->and($render('24px'))->toContain('font-size: 24px')
        ->and($render('2rem'))->toContain('font-size: 2rem');
});
