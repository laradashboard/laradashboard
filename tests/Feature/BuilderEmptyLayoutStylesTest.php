<?php

declare(strict_types=1);

use App\Services\Builder\DesignJsonRenderer;

$emptyLayout = [
    'margin' => ['top' => '', 'right' => '', 'bottom' => '', 'left' => ''],
    'padding' => ['top' => '', 'right' => '', 'bottom' => '', 'left' => ''],
];

test('blocks with empty spacing values do not output invalid css', function (string $type, array $props) use ($emptyLayout) {
    $html = app(DesignJsonRenderer::class)->render([
        ['id' => 'b1', 'type' => $type, 'props' => $props + ['layoutStyles' => $emptyLayout]],
    ], 'page');

    expect($html)->not->toBeEmpty()
        ->and($html)->not->toMatch('/[a-z-]+:\s*;/');
})->with([
    'heading' => ['heading', ['text' => 'Hello', 'level' => 'h2']],
    'text' => ['text', ['content' => 'Body copy']],
    'button' => ['button', ['text' => 'Go', 'link' => '#']],
    'list' => ['list', ['items' => ['One', 'Two']]],
    'quote' => ['quote', ['text' => 'Quoted', 'author' => 'Ada']],
    'section' => ['section', ['children' => [[['id' => 'c1', 'type' => 'text', 'props' => ['content' => 'Inside']]]]]],
    'columns' => ['columns', ['columns' => 2, 'children' => [[['id' => 'c2', 'type' => 'text', 'props' => ['content' => 'Left']]], []]]],
]);

test('real spacing values are still applied', function () {
    $html = app(DesignJsonRenderer::class)->render([
        ['id' => 'b1', 'type' => 'heading', 'props' => ['text' => 'Hi', 'level' => 'h2', 'layoutStyles' => ['margin' => ['top' => '24px']]]],
    ], 'page');

    expect($html)->toContain('margin-top: 24px');
});
