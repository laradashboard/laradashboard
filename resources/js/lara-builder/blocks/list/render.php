<?php

declare(strict_types=1);

use App\Support\Builder\ContentTokens;

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
    $color = $props['color'] ?? '';
    $fontSize = $props['fontSize'] ?? '16px';
    $iconColor = $props['iconColor'] ?? '';
    $layoutStyles = $props['layoutStyles'] ?? [];
    $customCSS = $props['customCSS'] ?? '';
    $customClass = $props['customClass'] ?? '';

    // Email context: inline-styled list
    if ($context === 'email') {
        if (empty($items)) {
            return '';
        }

        $typography = $layoutStyles['typography'] ?? [];
        $listColor = ContentTokens::resolveEmailTextColor(
            $color,
            $typography['color'] ?? null
        );
        $listFontSize = $typography['fontSize'] ?? $fontSize;

        $tag = $listType === 'number' ? 'ol' : 'ul';
        $itemsHtml = '';
        foreach ($items as $item) {
            $itemsHtml .= sprintf('<li style="margin-bottom: 8px;">%s</li>', $item);
        }

        $styles = [
            "color: {$listColor}",
            "font-size: {$listFontSize}",
            'line-height: 1.8',
            'font-family: Arial, Helvetica, sans-serif',
            'margin: 0',
            'padding-left: 24px',
        ];

        $layoutCSS = \App\Helpers\EmailStyleHelper::buildLayoutStyles($layoutStyles);
        if ($layoutCSS) {
            $styles[] = $layoutCSS;
        }

        return sprintf('<%s style="%s">%s</%s>', $tag, e(implode('; ', $styles)), $itemsHtml, $tag);
    }

    // Build block classes
    $blockClasses = "lb-block lb-list lb-list-{$listType}";
    if (! empty($customClass)) {
        $blockClasses .= ' ' . e($customClass);
    }

    // Determine list tag
    $listTag = $listType === 'number' ? 'ol' : 'ul';

    // Build block styles (dynamic typography only; structure in style.css)
    $blockStyles = [];

    // Typography - check layoutStyles first
    $typography = $layoutStyles['typography'] ?? [];

    $resolvedColor = ContentTokens::resolvePageTextColor(
        $color,
        $typography['color'] ?? null
    );

    if ($resolvedColor !== null) {
        $blockStyles[] = "color: {$resolvedColor}";
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

    if ($listType === 'check') {
        $normalizedIcon = strtolower(trim((string) $iconColor));
        // Legacy primary default (#635bff) inherits list text color via currentColor.
        $isLegacyIcon = $normalizedIcon === ''
            || $normalizedIcon === 'inherit'
            || $normalizedIcon === 'currentcolor'
            || $normalizedIcon === '#635bff';

        if (! $isLegacyIcon) {
            $resolvedIconColor = ContentTokens::resolvePageTextColor(
                $iconColor,
                $typography['color'] ?? null
            ) ?? $iconColor;

            if ($resolvedIconColor !== null && $resolvedIconColor !== '') {
                $blockStyles[] = '--lb-list-icon-color: ' . $resolvedIconColor;
            }
        }
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
            $itemsHtml .= sprintf(
                '<li class="lb-list-check-item"><span class="lb-list-check-icon">✓</span><span>%s</span></li>',
                $item // Allow HTML formatting in list items
            );
        } else {
            $itemsHtml .= sprintf(
                '<li>%s</li>',
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
