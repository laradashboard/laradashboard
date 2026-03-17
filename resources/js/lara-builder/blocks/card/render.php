<?php

/**
 * Card Block - Server-side Renderer
 *
 * Renders a styled card container with nested child blocks.
 * Uses DesignJsonRenderer for recursive child rendering.
 */

return function (array $props, string $context = 'page', ?string $blockId = null): string {
    $backgroundColor = $props['backgroundColor'] ?? '#ffffff';
    $borderColor = $props['borderColor'] ?? '#e5e7eb';
    $borderWidth = $props['borderWidth'] ?? '1px';
    $borderRadius = $props['borderRadius'] ?? '12px';
    $shadow = $props['shadow'] ?? 'sm';
    $hoverShadow = $props['hoverShadow'] ?? 'md';
    $hoverScale = $props['hoverScale'] ?? 'none';
    $padding = $props['padding'] ?? '24px';
    $children = $props['children'] ?? [];
    $customClass = $props['customClass'] ?? '';
    $layoutStyles = $props['layoutStyles'] ?? [];

    $renderer = app(\App\Services\Builder\DesignJsonRenderer::class);

    // Render children - card uses wrapped structure: [[block1, block2, ...]]
    $childBlocks = $children[0] ?? [];
    $childrenHtml = '';
    foreach ($childBlocks as $child) {
        $childrenHtml .= $renderer->renderBlock($child, $context);
    }

    // Shadow mapping
    $shadowMap = [
        'none' => 'none',
        'sm' => '0 1px 2px rgba(0,0,0,0.05)',
        'md' => '0 4px 6px -1px rgba(0,0,0,0.1)',
        'lg' => '0 10px 15px -3px rgba(0,0,0,0.1)',
        'xl' => '0 20px 25px -5px rgba(0,0,0,0.1)',
    ];

    $boxShadow = $shadowMap[$shadow] ?? $shadowMap['sm'];

    // Build card styles
    $cardStyles = [
        "background-color: {$backgroundColor}",
        "border: {$borderWidth} solid {$borderColor}",
        "border-radius: {$borderRadius}",
        "box-shadow: {$boxShadow}",
        "padding: {$padding}",
        'transition: all 0.2s ease',
    ];

    // Apply layout styles (margin/padding overrides)
    if (! empty($layoutStyles['padding'])) {
        $paddingOverride = $layoutStyles['padding'];
        $paddingParts = [];
        foreach (['top', 'right', 'bottom', 'left'] as $side) {
            if (isset($paddingOverride[$side])) {
                $paddingParts[] = "padding-{$side}: {$paddingOverride[$side]}";
            }
        }
        if (! empty($paddingParts)) {
            // Remove the default padding and add specific sides
            $cardStyles = array_filter($cardStyles, fn ($s) => ! str_starts_with($s, 'padding:'));
            $cardStyles = array_merge($cardStyles, $paddingParts);
        }
    }
    if (! empty($layoutStyles['margin'])) {
        foreach (['top', 'right', 'bottom', 'left'] as $side) {
            if (isset($layoutStyles['margin'][$side])) {
                $cardStyles[] = "margin-{$side}: {$layoutStyles['margin'][$side]}";
            }
        }
    }

    $classes = trim("lb-block lb-card {$customClass}");

    // Generate unique card class for hover styles
    $cardId = $blockId ?? uniqid('card-');
    $cardClass = "lb-card-{$cardId}";
    $classes .= " {$cardClass}";

    // Build hover style tag
    $hoverStyleTag = '';
    $hoverRules = [];

    if ($hoverShadow && $hoverShadow !== 'none') {
        $hoverBoxShadow = $shadowMap[$hoverShadow] ?? $shadowMap['md'];
        $hoverRules[] = "box-shadow: {$hoverBoxShadow}";
    }
    if ($hoverScale && $hoverScale !== 'none') {
        $hoverRules[] = "transform: scale({$hoverScale})";
    }

    if (! empty($hoverRules)) {
        $hoverCSS = implode('; ', $hoverRules);
        $hoverStyleTag = sprintf(
            '<style>.%s:hover { %s }</style>',
            e($cardClass),
            e($hoverCSS)
        );
    }

    return sprintf(
        '%s<div class="%s" style="%s">%s</div>',
        $hoverStyleTag,
        e($classes),
        e(implode('; ', $cardStyles)),
        $childrenHtml
    );
};
