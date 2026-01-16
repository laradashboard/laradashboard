<?php

/**
 * Icon Block - Server-side Renderer
 *
 * Renders Iconify icons with background styling options.
 */

return function (array $props, string $context = 'page', ?string $blockId = null): string {
    $icon = $props['icon'] ?? 'lucide:star';
    $size = $props['size'] ?? '48px';
    $color = $props['color'] ?? '#3b82f6';
    $align = $props['align'] ?? 'center';
    $backgroundColor = $props['backgroundColor'] ?? '';
    $backgroundShape = $props['backgroundShape'] ?? 'none';
    $backgroundPadding = $props['backgroundPadding'] ?? '16px';
    $customClass = $props['customClass'] ?? '';
    $customCSS = $props['customCSS'] ?? '';

    // Alignment mapping
    $alignMap = [
        'left' => 'flex-start',
        'center' => 'center',
        'right' => 'flex-end',
    ];

    $justifyContent = $alignMap[$align] ?? 'center';

    // Build container styles
    $containerStyles = [
        "display: flex",
        "justify-content: {$justifyContent}",
        "padding: 8px 0",
    ];

    if ($customCSS) {
        $containerStyles[] = $customCSS;
    }

    $containerStyleStr = implode('; ', $containerStyles);

    // Build background wrapper if needed
    $iconHtml = sprintf(
        '<iconify-icon icon="%s" width="%s" height="%s" style="color: %s;"></iconify-icon>',
        htmlspecialchars($icon),
        htmlspecialchars($size),
        htmlspecialchars($size),
        htmlspecialchars($color)
    );

    if ($backgroundColor && $backgroundShape !== 'none') {
        $borderRadius = match ($backgroundShape) {
            'circle' => '50%',
            'rounded' => '12px',
            'square' => '0',
            default => '0',
        };

        $wrapperStyles = [
            "background-color: {$backgroundColor}",
            "padding: {$backgroundPadding}",
            "border-radius: {$borderRadius}",
            "display: inline-flex",
            "align-items: center",
            "justify-content: center",
        ];

        $iconHtml = sprintf(
            '<div style="%s">%s</div>',
            implode('; ', $wrapperStyles),
            $iconHtml
        );
    }

    $classes = trim("lb-block lb-icon {$customClass}");

    return sprintf(
        '<div class="%s" style="%s">%s</div>',
        htmlspecialchars($classes),
        htmlspecialchars($containerStyleStr),
        $iconHtml
    );
};
