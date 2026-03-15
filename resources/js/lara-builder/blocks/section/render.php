<?php

/**
 * Section Block - Server-side Renderer
 *
 * Email: table-based wrapper with solid background.
 * Page: full-width section with gradient support.
 *
 * Uses DesignJsonRenderer for recursive child rendering.
 */

return function (array $props, string $context = 'page', ?string $blockId = null): string {
    $fullWidth = $props['fullWidth'] ?? true;
    $containerMaxWidth = $props['containerMaxWidth'] ?? '1280px';
    $contentAlign = $props['contentAlign'] ?? 'center';
    $backgroundType = $props['backgroundType'] ?? 'solid';
    $backgroundColor = $props['backgroundColor'] ?? '#ffffff';
    $gradientFrom = $props['gradientFrom'] ?? '#f9fafb';
    $gradientTo = $props['gradientTo'] ?? '#f3f4f6';
    $gradientDirection = $props['gradientDirection'] ?? 'to-br';
    $children = $props['children'] ?? [];
    $layoutStyles = $props['layoutStyles'] ?? [];
    $customClass = $props['customClass'] ?? '';

    $renderer = app(\App\Services\Builder\DesignJsonRenderer::class);

    // Render children - section uses wrapped structure: [[block1, block2, ...]]
    $childBlocks = $children[0] ?? [];
    $childrenHtml = '';
    foreach ($childBlocks as $child) {
        $childrenHtml .= $renderer->renderBlock($child, $context);
    }

    if ($context === 'email') {
        // For email, use solid color (gradients not well supported)
        $bgColor = $backgroundType === 'gradient' ? $gradientFrom : $backgroundColor;

        $styles = [
            "background-color: {$bgColor}",
            'font-family: Arial, Helvetica, sans-serif',
        ];

        $layoutCSS = \App\Helpers\EmailStyleHelper::buildLayoutStyles($layoutStyles);
        if ($layoutCSS) {
            $styles[] = $layoutCSS;
        }

        return sprintf(
            '<table width="100%%" cellpadding="0" cellspacing="0" border="0" style="%s"><tr><td style="padding: 32px 16px;">%s</td></tr></table>',
            e(implode('; ', $styles)),
            $childrenHtml
        );
    }

    // Page context
    $gradientDirectionMap = [
        'to-t' => 'to top',
        'to-tr' => 'to top right',
        'to-r' => 'to right',
        'to-br' => 'to bottom right',
        'to-b' => 'to bottom',
        'to-bl' => 'to bottom left',
        'to-l' => 'to left',
        'to-tl' => 'to top left',
    ];

    if ($backgroundType === 'gradient') {
        $direction = $gradientDirectionMap[$gradientDirection] ?? 'to bottom right';
        $backgroundStyle = "background: linear-gradient({$direction}, {$gradientFrom}, {$gradientTo})";
    } else {
        $backgroundStyle = "background-color: {$backgroundColor}";
    }

    $containerMargin = match ($contentAlign) {
        'left' => 'margin-left: 0',
        'right' => 'margin-right: 0',
        default => 'margin: 0 auto',
    };

    $sectionStyles = [
        $backgroundStyle,
        'padding: 48px 16px',
    ];

    if (! empty($layoutStyles['padding'])) {
        $padding = $layoutStyles['padding'];
        $paddingParts = [];
        foreach (['top', 'right', 'bottom', 'left'] as $side) {
            if (isset($padding[$side])) {
                $paddingParts[] = "padding-{$side}: {$padding[$side]}";
            }
        }
        if (! empty($paddingParts)) {
            $sectionStyles = array_merge([$backgroundStyle], $paddingParts);
        }
    }
    if (! empty($layoutStyles['margin'])) {
        foreach (['top', 'right', 'bottom', 'left'] as $side) {
            if (isset($layoutStyles['margin'][$side])) {
                $sectionStyles[] = "margin-{$side}: {$layoutStyles['margin'][$side]}";
            }
        }
    }

    $containerStyles = $fullWidth
        ? "max-width: {$containerMaxWidth}; {$containerMargin}; width: 100%"
        : 'width: 100%';

    $classes = trim("lb-block lb-section {$customClass}");

    return sprintf(
        '<section class="%s" style="%s"><div class="lb-section-container" style="%s">%s</div></section>',
        e($classes),
        e(implode('; ', $sectionStyles)),
        e($containerStyles),
        $childrenHtml
    );
};
