<?php

/**
 * Feature Box Block - Server-side Renderer
 *
 * Renders a feature box with icon, title, and description.
 */

return function (array $props, string $context = 'page', ?string $blockId = null): string {
    $icon = $props['icon'] ?? 'lucide:star';
    $iconSize = $props['iconSize'] ?? '32px';
    $iconColor = $props['iconColor'] ?? '#3b82f6';
    $iconBackgroundColor = $props['iconBackgroundColor'] ?? '#dbeafe';
    $iconBackgroundShape = $props['iconBackgroundShape'] ?? 'circle';
    $title = $props['title'] ?? 'Feature Title';
    $titleColor = $props['titleColor'] ?? '#111827';
    $titleSize = $props['titleSize'] ?? '18px';
    $description = $props['description'] ?? '';
    $descriptionColor = $props['descriptionColor'] ?? '#6b7280';
    $descriptionSize = $props['descriptionSize'] ?? '14px';
    $align = $props['align'] ?? 'center';
    $gap = $props['gap'] ?? '16px';
    $customClass = $props['customClass'] ?? '';
    $customCSS = $props['customCSS'] ?? '';

    // Alignment mapping
    $alignMap = [
        'left' => 'flex-start',
        'center' => 'center',
        'right' => 'flex-end',
    ];

    $textAlignMap = [
        'left' => 'left',
        'center' => 'center',
        'right' => 'right',
    ];

    $flexAlign = $alignMap[$align] ?? 'center';
    $textAlign = $textAlignMap[$align] ?? 'center';

    // Build container styles
    $containerStyles = [
        "display: flex",
        "flex-direction: column",
        "align-items: {$flexAlign}",
        "text-align: {$textAlign}",
        "padding: 16px",
    ];

    if ($customCSS) {
        $containerStyles[] = $customCSS;
    }

    $containerStyleStr = implode('; ', $containerStyles);

    // Build icon HTML
    $iconHtml = sprintf(
        '<iconify-icon icon="%s" width="%s" height="%s" style="color: %s;"></iconify-icon>',
        htmlspecialchars($icon),
        htmlspecialchars($iconSize),
        htmlspecialchars($iconSize),
        htmlspecialchars($iconColor)
    );

    // Add icon background if needed
    if ($iconBackgroundColor && $iconBackgroundShape !== 'none') {
        $borderRadius = match ($iconBackgroundShape) {
            'circle' => '50%',
            'rounded' => '12px',
            'square' => '0',
            default => '0',
        };

        $iconWrapperStyles = [
            "background-color: {$iconBackgroundColor}",
            "padding: 16px",
            "border-radius: {$borderRadius}",
            "display: inline-flex",
            "align-items: center",
            "justify-content: center",
            "margin-bottom: {$gap}",
        ];

        $iconHtml = sprintf(
            '<div style="%s">%s</div>',
            implode('; ', $iconWrapperStyles),
            $iconHtml
        );
    } else {
        $iconHtml = sprintf(
            '<div style="margin-bottom: %s;">%s</div>',
            htmlspecialchars($gap),
            $iconHtml
        );
    }

    // Build title HTML
    $titleHtml = sprintf(
        '<h3 style="color: %s; font-size: %s; font-weight: 600; margin: 0 0 8px 0;">%s</h3>',
        htmlspecialchars($titleColor),
        htmlspecialchars($titleSize),
        htmlspecialchars($title)
    );

    // Build description HTML
    $descriptionHtml = sprintf(
        '<p style="color: %s; font-size: %s; margin: 0; line-height: 1.5;">%s</p>',
        htmlspecialchars($descriptionColor),
        htmlspecialchars($descriptionSize),
        htmlspecialchars($description)
    );

    $classes = trim("lb-block lb-feature-box {$customClass}");

    return sprintf(
        '<div class="%s" style="%s">%s%s%s</div>',
        htmlspecialchars($classes),
        htmlspecialchars($containerStyleStr),
        $iconHtml,
        $titleHtml,
        $descriptionHtml
    );
};
