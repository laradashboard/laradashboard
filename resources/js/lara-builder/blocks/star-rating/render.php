<?php

/**
 * Star Rating Block - Server-side Renderer
 *
 * Page: renders iconify-icon star elements with accessibility attributes.
 * Email: renders unicode stars for maximum compatibility.
 */

return function (array $props, string $context = 'page', ?string $blockId = null): string {
    $rating = (float) ($props['rating'] ?? 5);
    $maxStars = (int) ($props['maxStars'] ?? 5);
    $size = $props['size'] ?? 'md';
    $filledColor = $props['filledColor'] ?? '#fbbf24';
    $emptyColor = $props['emptyColor'] ?? '#d1d5db';
    $showLabel = (bool) ($props['showLabel'] ?? false);
    $labelText = $props['labelText'] ?? '';
    $align = $props['align'] ?? 'center';
    $customClass = $props['customClass'] ?? '';
    $layoutStyles = $props['layoutStyles'] ?? [];

    $sizeMap = [
        'sm' => '16px',
        'md' => '24px',
        'lg' => '32px',
    ];
    $iconSize = $sizeMap[$size] ?? '24px';

    $alignMap = [
        'left' => 'flex-start',
        'center' => 'center',
        'right' => 'flex-end',
    ];
    $justifyContent = $alignMap[$align] ?? 'center';

    $blockClasses = trim("lb-block lb-star-rating {$customClass}");
    $ariaLabel = sprintf('Rating: %s out of %s stars', $rating, $maxStars);

    if ($context === 'email') {
        // Email context: use unicode stars
        $starsHtml = '';
        for ($i = 1; $i <= $maxStars; $i++) {
            $isFull = $i <= floor($rating);
            $isHalf = ! $isFull && $i === ceil($rating) && fmod($rating, 1) >= 0.5;

            if ($isFull || $isHalf) {
                $starsHtml .= sprintf(
                    '<span style="color: %s; font-size: %s; line-height: 1;">&#9733;</span>',
                    e($filledColor),
                    e($iconSize)
                );
            } else {
                $starsHtml .= sprintf(
                    '<span style="color: %s; font-size: %s; line-height: 1;">&#9734;</span>',
                    e($emptyColor),
                    e($iconSize)
                );
            }
        }

        $labelHtml = '';
        if ($showLabel && ! empty($labelText)) {
            $labelFontSize = match ($size) {
                'sm' => '12px',
                'lg' => '18px',
                default => '14px',
            };
            $labelHtml = sprintf(
                '<span style="margin-left: 8px; font-size: %s; color: #374151; font-weight: 500;">%s</span>',
                e($labelFontSize),
                e($labelText)
            );
        }

        $emailStyles = [
            "text-align: {$align}",
            'font-family: Arial, Helvetica, sans-serif',
        ];

        $emailLayoutCSS = \App\Helpers\EmailStyleHelper::buildLayoutStyles($layoutStyles);
        if ($emailLayoutCSS) {
            $emailStyles[] = $emailLayoutCSS;
        }

        return sprintf(
            '<div role="img" aria-label="%s" style="%s">%s%s</div>',
            e($ariaLabel),
            e(implode('; ', $emailStyles)),
            $starsHtml,
            $labelHtml
        );
    }

    // Page context: use iconify-icon elements
    $starsHtml = '';
    for ($i = 1; $i <= $maxStars; $i++) {
        $isFull = $i <= floor($rating);
        $isHalf = ! $isFull && $i === ceil($rating) && fmod($rating, 1) >= 0.5;

        if ($isFull) {
            $icon = 'mdi:star';
            $color = $filledColor;
        } elseif ($isHalf) {
            $icon = 'mdi:star-half-full';
            $color = $filledColor;
        } else {
            $icon = 'mdi:star-outline';
            $color = $emptyColor;
        }

        $starsHtml .= sprintf(
            '<iconify-icon icon="%s" width="%s" height="%s" style="color: %s; display: inline-block;" aria-hidden="true"></iconify-icon>',
            e($icon),
            e($iconSize),
            e($iconSize),
            e($color)
        );
    }

    $labelHtml = '';
    if ($showLabel && ! empty($labelText)) {
        $labelFontSize = match ($size) {
            'sm' => '12px',
            'lg' => '18px',
            default => '14px',
        };
        $labelHtml = sprintf(
            '<span style="margin-left: 8px; font-size: %s; color: #374151; font-weight: 500;">%s</span>',
            e($labelFontSize),
            e($labelText)
        );
    }

    return sprintf(
        '<div class="%s" role="img" aria-label="%s" style="display: flex; align-items: center; justify-content: %s; gap: 4px; padding: 8px;"><div style="display: flex; align-items: center; gap: 2px;">%s</div>%s</div>',
        e($blockClasses),
        e($ariaLabel),
        $justifyContent,
        $starsHtml,
        $labelHtml
    );
};
