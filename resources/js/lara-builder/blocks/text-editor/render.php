<?php

declare(strict_types=1);

use App\Support\Builder\ContentTokens;

/**
 * Text Editor Block - Server-side Renderer
 *
 * Rich HTML content from TinyMCE editor.
 */

return function (array $props, string $context = 'page', ?string $blockId = null): string {
    $content = $props['content'] ?? '';
    $align = $props['align'] ?? 'left';
    $color = $props['color'] ?? '';
    $fontSize = $props['fontSize'] ?? '16px';
    $lineHeight = $props['lineHeight'] ?? '1.6';
    $layoutStyles = $props['layoutStyles'] ?? [];
    $customCSS = $props['customCSS'] ?? '';
    $customClass = $props['customClass'] ?? '';

    if ($context === 'email') {
        $typography = $layoutStyles['typography'] ?? [];

        $styles = [
            "text-align: {$align}",
            'color: '.ContentTokens::resolveEmailTextColor($color, $typography['color'] ?? null),
            'font-size: '.($typography['fontSize'] ?? $fontSize),
            'line-height: '.($typography['lineHeight'] ?? $lineHeight),
            'font-family: Arial, Helvetica, sans-serif',
        ];

        $layoutCSS = \App\Helpers\EmailStyleHelper::buildLayoutStyles($layoutStyles);
        if ($layoutCSS) {
            $styles[] = $layoutCSS;
        }

        return sprintf('<div style="%s">%s</div>', e(implode('; ', $styles)), $content);
    }

    $blockClasses = 'lb-block lb-text-editor';
    if (! empty($customClass)) {
        $blockClasses .= ' '.e($customClass);
    }

    $typography = $layoutStyles['typography'] ?? [];
    $styles = [];

    if ($align) {
        $styles[] = "text-align: {$align}";
    }

    $resolvedColor = ContentTokens::resolvePageTextColor(
        $color,
        $typography['color'] ?? null
    );

    if ($resolvedColor !== null) {
        $styles[] = "color: {$resolvedColor}";
    }

    if (! empty($typography['fontSize'])) {
        $styles[] = "font-size: {$typography['fontSize']}";
    } elseif ($fontSize) {
        $styles[] = "font-size: {$fontSize}";
    }

    if (! empty($typography['lineHeight'])) {
        $styles[] = "line-height: {$typography['lineHeight']}";
    } elseif ($lineHeight) {
        $styles[] = "line-height: {$lineHeight}";
    }

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

    if (! empty($customCSS)) {
        $styles[] = $customCSS;
    }

    return sprintf(
        '<div class="%s" style="%s">%s</div>',
        e($blockClasses),
        e(implode('; ', $styles)),
        $content
    );
};
