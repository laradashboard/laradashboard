<?php

/**
 * Stats Item Block - Server-side Renderer
 *
 * Renders a statistic with value and label.
 */

return function (array $props, string $context = 'page', ?string $blockId = null): string {
    $value = $props['value'] ?? '100+';
    $valueColor = $props['valueColor'] ?? '#3b82f6';
    $valueSize = $props['valueSize'] ?? '48px';
    $label = $props['label'] ?? 'Happy Customers';
    $labelColor = $props['labelColor'] ?? '#6b7280';
    $labelSize = $props['labelSize'] ?? '14px';
    $align = $props['align'] ?? 'center';
    $prefix = $props['prefix'] ?? '';
    $suffix = $props['suffix'] ?? '';
    $customClass = $props['customClass'] ?? '';
    $customCSS = $props['customCSS'] ?? '';

    // Build container styles
    $containerStyles = [
        "text-align: {$align}",
        "padding: 16px",
    ];

    if ($customCSS) {
        $containerStyles[] = $customCSS;
    }

    $containerStyleStr = implode('; ', $containerStyles);

    // Build display value
    $displayValue = htmlspecialchars($prefix . $value . $suffix);

    // Build value HTML
    $valueStyles = [
        "color: {$valueColor}",
        "font-size: {$valueSize}",
        "font-weight: bold",
        "line-height: 1.2",
        "margin-bottom: 8px",
    ];

    $valueHtml = sprintf(
        '<div style="%s">%s</div>',
        implode('; ', $valueStyles),
        $displayValue
    );

    // Build label HTML
    $labelStyles = [
        "color: {$labelColor}",
        "font-size: {$labelSize}",
    ];

    $labelHtml = sprintf(
        '<div style="%s">%s</div>',
        implode('; ', $labelStyles),
        htmlspecialchars($label)
    );

    $classes = trim("lb-block lb-stats-item {$customClass}");

    return sprintf(
        '<div class="%s" style="%s">%s%s</div>',
        htmlspecialchars($classes),
        htmlspecialchars($containerStyleStr),
        $valueHtml,
        $labelHtml
    );
};
