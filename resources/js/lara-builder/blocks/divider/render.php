<?php

/**
 * Divider Block - Server-side Renderer
 */

return function (array $props, string $context = 'page', ?string $blockId = null): string {
    $color = $props['color'] ?? '#e5e7eb';
    $thickness = $props['thickness'] ?? '1px';
    $width = $props['width'] ?? '100%';
    $margin = $props['margin'] ?? '20px auto';
    $style = $props['style'] ?? 'solid';

    if ($context === 'email') {
        return sprintf(
            '<hr style="border: none; border-top: %s %s %s; width: %s; margin: %s;" />',
            e($thickness),
            e($style),
            e($color),
            e($width),
            e($margin)
        );
    }

    // Page context
    $layoutStyles = $props['layoutStyles'] ?? [];
    $customClass = $props['customClass'] ?? '';

    $blockClasses = 'lb-block lb-divider';
    if (! empty($customClass)) {
        $blockClasses .= ' ' . e($customClass);
    }

    $styles = [
        'border: none',
        "border-top: {$thickness} {$style} {$color}",
        "width: {$width}",
        "margin: {$margin}",
    ];

    if (! empty($layoutStyles['margin'])) {
        $m = $layoutStyles['margin'];
        foreach (['top', 'right', 'bottom', 'left'] as $side) {
            if (isset($m[$side])) {
                $styles[] = "margin-{$side}: {$m[$side]}";
            }
        }
    }

    return sprintf(
        '<hr class="%s" style="%s" />',
        e($blockClasses),
        e(implode('; ', $styles))
    );
};
