<?php

declare(strict_types=1);

// The builder has no JS test runner, so this guards the fix at source level:
// converting a block from the "/" menu must not carry the typed "/heading" command into the new block
// (it used to reappear after the cursor and get pushed into the next block on Enter).
test('slash commands are not carried over when a block is converted', function () {
    $source = file_get_contents(resource_path('js/lara-builder/core/hooks/useBlockOperations.js'));

    expect($source)->toContain('plain.startsWith("/") ? "" : value')
        ->and($source)->toContain('transferredProps.text = keepText(currentBlock.props.content)')
        ->and($source)->toContain('transferredProps.content = keepText(currentBlock.props.text)')
        ->and($source)->not->toContain('transferredProps.text = currentBlock.props.content || ""');
});

test('choosing from the slash menu with Enter does not also create an empty block', function () {
    $source = file_get_contents(resource_path('js/lara-builder/components/SlashCommandMenu.jsx'));

    expect($source)->toMatch('/case "Enter":\s*e\.preventDefault\(\);[^}]*e\.stopPropagation\(\);/s')
        ->and($source)->toContain('document.addEventListener("keydown", handleKeyDown, true)');
});
