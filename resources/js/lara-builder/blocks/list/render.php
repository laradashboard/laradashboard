<?php

/**
 * List Block - Server-side Renderer
 *
 * This callback is invoked by the BlockRenderer when processing content.
 * It generates semantic list elements (ul/ol) with proper styling.
 *
 * Benefits of server-side rendering:
 * - Semantic HTML (ul, ol, li)
 * - Shortcode/variable replacement in list items
 * - Content sanitization
 * - Future-proof: update rendering without migrating stored content
 */

return function (array $props, string $context = 'page', ?string $blockId = null): string {
    $items = $props['items'] ?? [];
    $listType = $props['listType'] ?? 'bullet';
    $color = $props['color'] ?? '#333333';
    $fontSize = $props['fontSize'] ?? '16px';
    $iconColor = $props['iconColor'] ?? '#635bff';
    $layoutStyles = $props['layoutStyles'] ?? [];
    $customCSS = $props['customCSS'] ?? '';
    $customClass = $props['customClass'] ?? '';

    // Build block classes
    $blockClasses = "lb-block lb-list lb-list-{$listType}";
    if (! empty($customClass)) {
        $blockClasses .= ' ' . e($customClass);
    }

    // Determine list tag
    $listTag = $listType === 'number' ? 'ol' : 'ul';

    // Build block styles
    $blockStyles = [
        'line-height: 1.8',
        'margin: 0',
    ];

    // Padding based on list type
    if ($listType === 'check') {
        $blockStyles[] = 'list-style: none';
        $blockStyles[] = 'padding-left: 0';
    } else {
        $blockStyles[] = 'padding-left: 24px';
    }

    // Typography - check layoutStyles first
    $typography = $layoutStyles['typography'] ?? [];

    if (! empty($typography['color'])) {
        $blockStyles[] = "color: {$typography['color']}";
    } else {
        $blockStyles[] = "color: {$color}";
    }

    if (! empty($typography['fontSize'])) {
        $blockStyles[] = "font-size: {$typography['fontSize']}";
    } else {
        $blockStyles[] = "font-size: {$fontSize}";
    }

    if (! empty($typography['fontWeight'])) {
        $blockStyles[] = "font-weight: {$typography['fontWeight']}";
    }

    if (! empty($typography['lineHeight'])) {
        $blockStyles[] = "line-height: {$typography['lineHeight']}";
    }

    // Layout styles (margin, padding overrides)
    if (! empty($layoutStyles['margin'])) {
        $margin = $layoutStyles['margin'];
        foreach (['top', 'right', 'bottom', 'left'] as $side) {
            if (isset($margin[$side])) {
                $blockStyles[] = "margin-{$side}: {$margin[$side]}";
            }
        }
    }

    if (! empty($layoutStyles['padding'])) {
        $padding = $layoutStyles['padding'];
        foreach (['top', 'right', 'bottom', 'left'] as $side) {
            if (isset($padding[$side])) {
                $blockStyles[] = "padding-{$side}: {$padding[$side]}";
            }
        }
    }

    // Custom CSS
    if (! empty($customCSS)) {
        $blockStyles[] = $customCSS;
    }

    $styleAttr = implode('; ', $blockStyles);

    // Build list items
    $itemsHtml = '';
    foreach ($items as $item) {
        if ($listType === 'check') {
            // Check list with icon
            $itemsHtml .= sprintf(
                '<li style="display: flex; align-items: flex-start; gap: 8px; margin-bottom: 8px;"><span style="color: %s; flex-shrink: 0;">✓</span><span>%s</span></li>',
                e($iconColor),
                $item // Allow HTML formatting in list items
            );
        } else {
            // Regular bullet or numbered list
            $itemsHtml .= sprintf(
                '<li style="margin-bottom: 8px;">%s</li>',
                $item // Allow HTML formatting in list items
            );
        }
    }

    return sprintf(
        '<%s class="%s" style="%s">%s</%s>',
        $listTag,
        e($blockClasses),
        e($styleAttr),
        $itemsHtml,
        $listTag
    );
};
