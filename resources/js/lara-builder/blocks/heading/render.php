<?php

/**
 * Heading Block - Server-side Renderer
 *
 * This callback is invoked by the BlockRenderer when processing content.
 * It generates semantic headings with proper IDs for TOC anchor linking.
 *
 * Benefits of server-side rendering:
 * - Consistent anchor ID generation with TOC block
 * - SEO-friendly heading structure
 * - Security: proper escaping of user content
 * - Future-proof: update rendering without migrating stored content
 */

return function (array $props, string $context = 'page', ?string $blockId = null): string {
    $text = $props['text'] ?? '';
    $level = $props['level'] ?? 'h2';
    $align = $props['align'] ?? 'left';
    $color = $props['color'] ?? '#333333';
    $fontSize = $props['fontSize'] ?? '32px';
    $fontWeight = $props['fontWeight'] ?? 'bold';
    $lineHeight = $props['lineHeight'] ?? '1.2';
    $letterSpacing = $props['letterSpacing'] ?? '0';
    $layoutStyles = $props['layoutStyles'] ?? [];
    $customCSS = $props['customCSS'] ?? '';
    $customClass = $props['customClass'] ?? '';

    // Strip HTML tags for ID generation but keep them for display
    $plainText = strip_tags($text);

    // Generate anchor ID for TOC linking - format: toc-{slug}-{blockId}
    $headingId = '';
    if ($blockId && ! empty($plainText)) {
        $slug = \Illuminate\Support\Str::slug($plainText);
        $headingId = "toc-{$slug}-{$blockId}";
    }

    // Build block classes
    $blockClasses = 'lb-block lb-heading';
    if (! empty($customClass)) {
        $blockClasses .= ' ' . e($customClass);
    }

    // Build inline styles
    $styles = [];

    // Block-specific styles
    if ($align) {
        $styles[] = "text-align: {$align}";
    }

    // Check if typography styles exist in layoutStyles, otherwise use direct props
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

    if (! empty($typography['fontWeight'])) {
        $styles[] = "font-weight: {$typography['fontWeight']}";
    } elseif ($fontWeight) {
        $styles[] = "font-weight: {$fontWeight}";
    }

    if (! empty($typography['lineHeight'])) {
        $styles[] = "line-height: {$typography['lineHeight']}";
    } elseif ($lineHeight) {
        $styles[] = "line-height: {$lineHeight}";
    }

    if (! empty($typography['letterSpacing'])) {
        $styles[] = "letter-spacing: {$typography['letterSpacing']}";
    } elseif ($letterSpacing && $letterSpacing !== '0') {
        $styles[] = "letter-spacing: {$letterSpacing}";
    }

    // Layout styles (margin, padding, width, etc.)
    if (! empty($layoutStyles['margin'])) {
        $margin = $layoutStyles['margin'];
        if (isset($margin['top'])) {
            $styles[] = "margin-top: {$margin['top']}";
        }
        if (isset($margin['right'])) {
            $styles[] = "margin-right: {$margin['right']}";
        }
        if (isset($margin['bottom'])) {
            $styles[] = "margin-bottom: {$margin['bottom']}";
        }
        if (isset($margin['left'])) {
            $styles[] = "margin-left: {$margin['left']}";
        }
    }

    if (! empty($layoutStyles['padding'])) {
        $padding = $layoutStyles['padding'];
        if (isset($padding['top'])) {
            $styles[] = "padding-top: {$padding['top']}";
        }
        if (isset($padding['right'])) {
            $styles[] = "padding-right: {$padding['right']}";
        }
        if (isset($padding['bottom'])) {
            $styles[] = "padding-bottom: {$padding['bottom']}";
        }
        if (isset($padding['left'])) {
            $styles[] = "padding-left: {$padding['left']}";
        }
    }

    // Width/height
    foreach (['width', 'minWidth', 'maxWidth', 'height', 'minHeight', 'maxHeight'] as $dimension) {
        if (! empty($layoutStyles[$dimension])) {
            $cssProp = strtolower(preg_replace('/([A-Z])/', '-$1', $dimension));
            $styles[] = "{$cssProp}: {$layoutStyles[$dimension]}";
        }
    }

    // Custom CSS
    if (! empty($customCSS)) {
        $styles[] = $customCSS;
    }

    $styleAttr = implode('; ', $styles);
    $idAttr = $headingId ? sprintf(' id="%s"', e($headingId)) : '';

    // Validate heading level
    $validLevels = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
    if (! in_array($level, $validLevels, true)) {
        $level = 'h2';
    }

    return sprintf(
        '<%s%s class="%s" style="%s">%s</%s>',
        $level,
        $idAttr,
        e($blockClasses),
        e($styleAttr),
        $text, // Keep HTML formatting in text (bold, italic, etc.)
        $level
    );
};
