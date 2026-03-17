<?php

/**
 * Badge Block - Server-side Renderer
 *
 * Renders a small colored badge/pill label.
 * Supports solid, outline, and soft variants with optional icon.
 */

return function (array $props, string $context = 'page', ?string $blockId = null): string {
    $text = $props['text'] ?? 'NEW';
    $variant = $props['variant'] ?? 'soft';
    $size = $props['size'] ?? 'sm';
    $color = $props['color'] ?? '#3b82f6';
    $textColor = $props['textColor'] ?? '#1e40af';
    $icon = $props['icon'] ?? '';
    $borderRadius = $props['borderRadius'] ?? 'pill';
    $align = $props['align'] ?? 'center';
    $customClass = $props['customClass'] ?? '';
    $layoutStyles = $props['layoutStyles'] ?? [];

    // Size mapping
    $sizeMap = [
        'sm' => ['fontSize' => '12px', 'padding' => '2px 8px'],
        'md' => ['fontSize' => '14px', 'padding' => '4px 12px'],
        'lg' => ['fontSize' => '16px', 'padding' => '6px 16px'],
    ];
    $currentSize = $sizeMap[$size] ?? $sizeMap['sm'];

    // Border radius mapping
    $radiusMap = [
        'rounded' => '6px',
        'pill' => '9999px',
    ];
    $radius = $radiusMap[$borderRadius] ?? '9999px';

    // Variant styles
    switch ($variant) {
        case 'solid':
            $bgColor = $color;
            $fgColor = '#ffffff';
            $border = 'none';
            break;
        case 'outline':
            $bgColor = 'transparent';
            $fgColor = $textColor;
            $border = "1.5px solid {$color}";
            break;
        case 'soft':
        default:
            // Convert hex to rgba with 15% opacity
            $r = hexdec(substr($color, 1, 2));
            $g = hexdec(substr($color, 3, 2));
            $b = hexdec(substr($color, 5, 2));
            $bgColor = "rgba({$r}, {$g}, {$b}, 0.15)";
            $fgColor = $textColor;
            $border = 'none';
            break;
    }

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
        'padding: 8px 0',
    ];

    // Apply layout styles
    if (! empty($layoutStyles['margin'])) {
        foreach (['top', 'right', 'bottom', 'left'] as $side) {
            if (! empty($layoutStyles['margin'][$side])) {
                $containerStyles[] = "margin-{$side}: {$layoutStyles['margin'][$side]}";
            }
        }
    }
    if (! empty($layoutStyles['padding'])) {
        foreach (['top', 'right', 'bottom', 'left'] as $side) {
            if (! empty($layoutStyles['padding'][$side])) {
                $containerStyles[] = "padding-{$side}: {$layoutStyles['padding'][$side]}";
            }
        }
    }

    // Build badge styles
    $badgeStyles = [
        'display: inline-flex',
        'align-items: center',
        'gap: 4px',
        'font-weight: 600',
        'line-height: 1.4',
        'white-space: nowrap',
        'letter-spacing: 0.025em',
        "font-size: {$currentSize['fontSize']}",
        "padding: {$currentSize['padding']}",
        "border-radius: {$radius}",
        "background-color: {$bgColor}",
        "color: {$fgColor}",
        "border: {$border}",
    ];

    // Icon HTML
    $iconHtml = '';
    if (! empty($icon)) {
        $iconSize = $currentSize['fontSize'];
        $iconHtml = sprintf(
            '<iconify-icon icon="%s" width="%s" height="%s" aria-hidden="true"></iconify-icon>',
            e($icon),
            e($iconSize),
            e($iconSize)
        );
    }

    $blockClasses = 'lb-block lb-badge';
    if (! empty($customClass)) {
        $blockClasses .= ' ' . e($customClass);
    }

    if ($context === 'email') {
        // Email: inline styles only, no iconify (use text only)
        $emailBadgeStyles = [
            'display: inline-block',
            'font-weight: 600',
            'line-height: 1.4',
            'white-space: nowrap',
            'letter-spacing: 0.025em',
            "font-size: {$currentSize['fontSize']}",
            "padding: {$currentSize['padding']}",
            "border-radius: {$radius}",
            "background-color: {$bgColor}",
            "color: {$fgColor}",
            "border: {$border}",
            'font-family: Arial, Helvetica, sans-serif',
        ];

        $emailContainerStyles = [
            "text-align: {$align}",
            'padding: 8px 0',
        ];

        $emailLayoutCSS = \App\Helpers\EmailStyleHelper::buildLayoutStyles($layoutStyles);
        if ($emailLayoutCSS) {
            $emailContainerStyles[] = $emailLayoutCSS;
        }

        return sprintf(
            '<div style="%s"><span style="%s">%s</span></div>',
            e(implode('; ', $emailContainerStyles)),
            e(implode('; ', $emailBadgeStyles)),
            e($text)
        );
    }

    return sprintf(
        '<div class="%s" style="%s"><span style="%s">%s%s</span></div>',
        e($blockClasses),
        e(implode('; ', $containerStyles)),
        e(implode('; ', $badgeStyles)),
        $iconHtml,
        e($text)
    );
};
