<?php

/**
 * Logo Carousel Block - Server-side Renderer
 *
 * Renders an infinite CSS marquee of logos/images.
 * Uses pure CSS animation with duplicated track for seamless looping.
 */

return function (array $props, string $context = 'page', ?string $blockId = null): string {
    $images = $props['images'] ?? [];
    $speed = (int) ($props['speed'] ?? 30);
    $direction = $props['direction'] ?? 'left';
    $pauseOnHover = $props['pauseOnHover'] ?? true;
    $gap = $props['gap'] ?? '48px';
    $imageHeight = $props['imageHeight'] ?? '40px';
    $grayscale = $props['grayscale'] ?? true;
    $headingText = $props['headingText'] ?? '';
    $headingColor = $props['headingColor'] ?? '#6b7280';
    $headingSize = $props['headingSize'] ?? '14px';
    $customClass = $props['customClass'] ?? '';
    $customCSS = $props['customCSS'] ?? '';

    if (empty($images)) {
        return '';
    }

    $uid = $blockId ?? uniqid('lc-');
    $classes = trim("lb-block lb-logo-carousel {$customClass}");

    // Build logo HTML (single set)
    $logosHtml = '';
    foreach ($images as $image) {
        $src = e($image['src'] ?? '');
        $alt = e($image['alt'] ?? '');
        $link = $image['link'] ?? '';

        if (empty($src)) {
            continue;
        }

        $imgStyle = "height: " . e($imageHeight) . "; width: auto; object-fit: contain; flex-shrink: 0;";
        if ($grayscale) {
            $imgStyle .= " filter: grayscale(100%) opacity(0.6); transition: filter 0.3s;";
        }

        $imgTag = sprintf(
            '<img src="%s" alt="%s" style="%s" loading="lazy" />',
            $src,
            $alt,
            $imgStyle
        );

        if (! empty($link)) {
            $logosHtml .= sprintf(
                '<a href="%s" target="_blank" rel="noopener noreferrer" style="flex-shrink: 0; display: inline-flex;">%s</a>',
                e($link),
                $imgTag
            );
        } else {
            $logosHtml .= $imgTag;
        }
    }

    // Duplicate the logos for seamless infinite loop
    $trackHtml = $logosHtml . $logosHtml;

    // Heading HTML
    $headingHtml = '';
    if (! empty($headingText)) {
        $headingHtml = sprintf(
            '<p style="color: %s; font-size: %s; text-align: center; margin: 0 0 20px 0; font-weight: 500; letter-spacing: 0.025em; text-transform: uppercase;">%s</p>',
            e($headingColor),
            e($headingSize),
            e($headingText)
        );
    }

    // Animation direction
    $animDirection = $direction === 'right' ? 'reverse' : 'normal';

    // Pause on hover CSS
    $pauseCSS = $pauseOnHover
        ? sprintf('.lb-carousel-track-%s:hover { animation-play-state: paused; }', e($uid))
        : '';

    // Grayscale hover CSS
    $grayscaleHoverCSS = $grayscale
        ? sprintf(
            '.lb-carousel-track-%s img:hover { filter: grayscale(0%%) opacity(1) !important; }',
            e($uid)
        )
        : '';

    // Build the style block
    $styleBlock = sprintf(
        '<style>
@keyframes lb-marquee-%1$s {
    0%% { transform: translateX(0); }
    100%% { transform: translateX(-50%%); }
}
.lb-carousel-track-%1$s {
    display: flex;
    align-items: center;
    gap: %2$s;
    animation: lb-marquee-%1$s %3$ds linear infinite;
    animation-direction: %4$s;
    width: max-content;
}
%5$s
%6$s
</style>',
        e($uid),
        e($gap),
        $speed,
        $animDirection,
        $pauseCSS,
        $grayscaleHoverCSS
    );

    // Container styles
    $containerStyles = [
        'overflow: hidden',
        'padding: 24px 16px',
    ];

    if ($customCSS) {
        $containerStyles[] = $customCSS;
    }

    return sprintf(
        '%s<div class="%s" style="%s">%s<div class="lb-carousel-track-%s">%s</div></div>',
        $styleBlock,
        e($classes),
        e(implode('; ', $containerStyles)),
        $headingHtml,
        e($uid),
        $trackHtml
    );
};
