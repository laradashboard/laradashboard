<?php

/**
 * Text Editor Block - Server-side Renderer
 *
 * Rich HTML content from TinyMCE editor.
 */

return function (array $props, string $context = 'page', ?string $blockId = null): string {
    $content = $props['content'] ?? '';
    $align = $props['align'] ?? 'left';
    $color = $props['color'] ?? '#333333';
    $fontSize = $props['fontSize'] ?? '16px';
    $lineHeight = $props['lineHeight'] ?? '1.6';
    $layoutStyles = $props['layoutStyles'] ?? [];

    if ($context === 'email') {
        $typography = $layoutStyles['typography'] ?? [];

        $styles = [
            "text-align: {$align}",
            'color: ' . ($typography['color'] ?? $color),
            'font-size: ' . ($typography['fontSize'] ?? $fontSize),
            'line-height: ' . ($typography['lineHeight'] ?? $lineHeight),
            'font-family: Arial, Helvetica, sans-serif',
        ];

        $layoutCSS = \App\Helpers\EmailStyleHelper::buildLayoutStyles($layoutStyles);
        if ($layoutCSS) {
            $styles[] = $layoutCSS;
        }

        return sprintf('<div style="%s">%s</div>', e(implode('; ', $styles)), $content);
    }

    // Page context
    $customClass = $props['customClass'] ?? '';
    $blockClasses = 'lb-block lb-text-editor';
    if (! empty($customClass)) {
        $blockClasses .= ' ' . e($customClass);
    }

    $typography = $layoutStyles['typography'] ?? [];
    $styles = [];

    if ($align) {
        $styles[] = "text-align: {$align}";
    }
    if (empty($typography['color']) && $color) {
        $styles[] = "color: {$color}";
    }
    if (empty($typography['fontSize']) && $fontSize) {
        $styles[] = "font-size: {$fontSize}";
    }
    if (empty($typography['lineHeight']) && $lineHeight) {
        $styles[] = "line-height: {$lineHeight}";
    }

    // Add layout styles
    if (! empty($layoutStyles['margin'])) {
        foreach (['top', 'right', 'bottom', 'left'] as $side) {
            if (isset($layoutStyles['margin'][$side])) {
                $styles[] = "margin-{$side}: {$layoutStyles['margin'][$side]}";
            }
        }
    }
    if (! empty($layoutStyles['padding'])) {
        foreach (['top', 'right', 'bottom', 'left'] as $side) {
            if (isset($layoutStyles['padding'][$side])) {
                $styles[] = "padding-{$side}: {$layoutStyles['padding'][$side]}";
            }
        }
    }
    if (! empty($layoutStyles['background']['color'])) {
        $styles[] = "background-color: {$layoutStyles['background']['color']}";
    }

    return sprintf(
        '<div class="%s" style="%s">%s</div>',
        e($blockClasses),
        e(implode('; ', $styles)),
        $content
    );
};
