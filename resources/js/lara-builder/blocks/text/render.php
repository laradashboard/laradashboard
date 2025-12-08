<?php

/**
 * Text Block - Server-side Renderer
 *
 * This callback is invoked by the BlockRenderer when processing content.
 * It generates text paragraphs with proper styling.
 *
 * Benefits of server-side rendering:
 * - Shortcode/variable replacement (e.g., {{user.name}})
 * - Link sanitization within content
 * - Future-proof: update rendering without migrating stored content
 */

return function (array $props, string $context = 'page', ?string $blockId = null): string {
    $content = $props['content'] ?? '';
    $align = $props['align'] ?? 'left';
    $color = $props['color'] ?? '#666666';
    $fontSize = $props['fontSize'] ?? '16px';
    $lineHeight = $props['lineHeight'] ?? '1.6';
    $layoutStyles = $props['layoutStyles'] ?? [];
    $customCSS = $props['customCSS'] ?? '';
    $customClass = $props['customClass'] ?? '';

    // Build block classes
    $blockClasses = 'lb-block lb-text';
    if (! empty($customClass)) {
        $blockClasses .= ' ' . e($customClass);
    }

    // Build inline styles
    $styles = [];

    // Alignment
    if ($align) {
        $styles[] = "text-align: {$align}";
    }

    // Typography - check layoutStyles first, fallback to direct props
    $typography = $layoutStyles['typography'] ?? [];

    if (! empty($typography['color'])) {
        $styles[] = "color: {$typography['color']}";
    } elseif ($color) {
        $styles[] = "color: {$color}";
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

    if (! empty($typography['fontWeight'])) {
        $styles[] = "font-weight: {$typography['fontWeight']}";
    }

    if (! empty($typography['letterSpacing'])) {
        $styles[] = "letter-spacing: {$typography['letterSpacing']}";
    }

    // Layout styles (margin, padding, width, etc.)
    if (! empty($layoutStyles['margin'])) {
        $margin = $layoutStyles['margin'];
        foreach (['top', 'right', 'bottom', 'left'] as $side) {
            if (isset($margin[$side])) {
                $styles[] = "margin-{$side}: {$margin[$side]}";
            }
        }
    }

    if (! empty($layoutStyles['padding'])) {
        $padding = $layoutStyles['padding'];
        foreach (['top', 'right', 'bottom', 'left'] as $side) {
            if (isset($padding[$side])) {
                $styles[] = "padding-{$side}: {$padding[$side]}";
            }
        }
    }

    // Width/height
    foreach (['width', 'minWidth', 'maxWidth', 'height', 'minHeight', 'maxHeight'] as $dimension) {
        if (! empty($layoutStyles[$dimension])) {
            $cssProp = strtolower(preg_replace('/([A-Z])/', '-$1', $dimension));
            $styles[] = "{$cssProp}: {$layoutStyles[$dimension]}";
        }
    }

    // Background
    if (! empty($layoutStyles['background']['color'])) {
        $styles[] = "background-color: {$layoutStyles['background']['color']}";
    }

    // Custom CSS
    if (! empty($customCSS)) {
        $styles[] = $customCSS;
    }

    $styleAttr = implode('; ', $styles);

    // The content may contain HTML formatting (bold, italic, links, etc.)
    // We allow this as it's user-controlled content in the builder

    return sprintf(
        '<div class="%s" style="%s">%s</div>',
        e($blockClasses),
        e($styleAttr),
        $content
    );
};
