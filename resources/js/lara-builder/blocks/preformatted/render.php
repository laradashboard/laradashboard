<?php

/**
 * Preformatted Block - Server-side Renderer
 */

return function (array $props, string $context = 'page', ?string $blockId = null): string {
    $text = $props['text'] ?? '';
    $layoutStyles = $props['layoutStyles'] ?? [];

    // Strip HTML tags for safety (content should be plain text)
    $content = e(strip_tags($text));

    if ($context === 'email') {
        $typography = $layoutStyles['typography'] ?? [];
        $bgColor = $layoutStyles['background']['color'] ?? '#f3f4f6';
        $textColor = $typography['color'] ?? '#1f2937';
        $fontSize = $typography['fontSize'] ?? '14px';
        $lineHeight = $typography['lineHeight'] ?? '1.6';

        $styles = [
            'margin: 1em 0',
            "background-color: {$bgColor}",
            'border-radius: 4px',
            'padding: 16px',
            'overflow-x: auto',
            'white-space: pre-wrap',
            'word-wrap: break-word',
            'font-family: Courier New, Courier, monospace',
            "font-size: {$fontSize}",
            "line-height: {$lineHeight}",
            "color: {$textColor}",
            'border: 1px solid #e5e7eb',
        ];

        $layoutCSS = \App\Helpers\EmailStyleHelper::buildLayoutStyles($layoutStyles);
        if ($layoutCSS) {
            $styles[] = $layoutCSS;
        }

        return sprintf('<pre style="%s">%s</pre>', e(implode('; ', $styles)), $content);
    }

    // Page context
    $customClass = $props['customClass'] ?? '';
    $blockClasses = 'lb-block lb-preformatted';
    if (! empty($customClass)) {
        $blockClasses .= ' ' . e($customClass);
    }

    $styles = [
        'overflow-x: auto',
        'white-space: pre-wrap',
        'word-wrap: break-word',
    ];

    $typography = $layoutStyles['typography'] ?? [];
    if (empty($typography['fontFamily'])) {
        $styles[] = 'font-family: ui-monospace, SFMono-Regular, SF Mono, Menlo, Consolas, Liberation Mono, monospace';
    }
    if (empty($typography['fontSize'])) {
        $styles[] = 'font-size: 14px';
    }
    if (empty($typography['lineHeight'])) {
        $styles[] = 'line-height: 1.6';
    }
    if (empty($typography['color'])) {
        $styles[] = 'color: var(--color-gray-800, #1f2937)';
    }
    if (empty($layoutStyles['background']['color'])) {
        $styles[] = 'background-color: var(--color-gray-100, #f3f4f6)';
    }
    if (empty($layoutStyles['border']['width'])) {
        $styles[] = 'border: 1px solid var(--color-gray-200, #e5e7eb)';
    }
    if (empty($layoutStyles['border']['radius'])) {
        $styles[] = 'border-radius: 4px';
    }
    $styles[] = 'padding: 16px';
    $styles[] = 'margin: 1em 0';

    return sprintf('<pre class="%s" style="%s">%s</pre>', e($blockClasses), e(implode('; ', $styles)), $content);
};
