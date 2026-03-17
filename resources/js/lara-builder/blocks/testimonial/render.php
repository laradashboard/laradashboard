<?php

/**
 * Testimonial Block - Server-side Renderer
 *
 * Renders a testimonial card with avatar, name, role, star rating, and quote.
 */

return function (array $props, string $context = 'page', ?string $blockId = null): string {
    $quote = $props['quote'] ?? 'This product has completely transformed our workflow. Highly recommended!';
    $authorName = $props['authorName'] ?? 'John Doe';
    $authorRole = $props['authorRole'] ?? 'CEO, Company';
    $avatarUrl = $props['avatarUrl'] ?? '';
    $rating = (int) ($props['rating'] ?? 5);
    $showRating = $props['showRating'] ?? true;
    $cardStyle = $props['cardStyle'] ?? 'shadow';
    $backgroundColor = $props['backgroundColor'] ?? '#ffffff';
    $textColor = $props['textColor'] ?? '#374151';
    $nameColor = $props['nameColor'] ?? '#111827';
    $ratingColor = $props['ratingColor'] ?? '#fbbf24';
    $borderColor = $props['borderColor'] ?? '#e5e7eb';
    $customClass = $props['customClass'] ?? '';
    $customCSS = $props['customCSS'] ?? '';

    // Card style
    $cardStyles = [
        "background-color: {$backgroundColor}",
        'padding: 24px',
        'border-radius: 12px',
    ];

    switch ($cardStyle) {
        case 'bordered':
            $cardStyles[] = "border: 1px solid {$borderColor}";
            break;
        case 'shadow':
            $cardStyles[] = 'box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -2px rgba(0,0,0,0.1)';
            break;
        case 'minimal':
            // No border or shadow
            break;
        default:
            $cardStyles[] = 'box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1)';
            break;
    }

    if ($customCSS) {
        $cardStyles[] = $customCSS;
    }

    $classes = trim("lb-block lb-testimonial {$customClass}");

    // Quote icon SVG
    $quoteIconHtml = sprintf(
        '<div style="margin-bottom: 16px;"><svg width="32" height="32" viewBox="0 0 24 24" fill="%s" opacity="0.2" aria-hidden="true"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/></svg></div>',
        e($textColor)
    );

    // Star rating HTML
    $ratingHtml = '';
    if ($showRating) {
        $starsHtml = '';
        for ($i = 1; $i <= 5; $i++) {
            $fill = $i <= $rating ? e($ratingColor) : 'none';
            $starsHtml .= sprintf(
                '<svg width="18" height="18" viewBox="0 0 24 24" fill="%s" stroke="%s" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
                $fill,
                e($ratingColor)
            );
        }
        $ratingHtml = sprintf(
            '<div style="display: flex; gap: 2px; margin-bottom: 12px;" role="img" aria-label="%s">%s</div>',
            e("{$rating} out of 5 stars"),
            $starsHtml
        );
    }

    // Quote text
    $quoteHtml = sprintf(
        '<p style="color: %s; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0; font-style: italic;">%s</p>',
        e($textColor),
        e($quote)
    );

    // Avatar
    $avatarHtml = '';
    if ($avatarUrl) {
        $avatarHtml = sprintf(
            '<img src="%s" alt="%s" style="width: 48px; height: 48px; border-radius: 50%%; object-fit: cover; flex-shrink: 0;" />',
            e($avatarUrl),
            e($authorName . ' avatar')
        );
    } else {
        // Generate initials
        $nameParts = preg_split('/\s+/', trim($authorName));
        $initials = '';
        if (count($nameParts) >= 2) {
            $initials = mb_strtoupper(mb_substr($nameParts[0], 0, 1) . mb_substr(end($nameParts), 0, 1));
        } else {
            $initials = mb_strtoupper(mb_substr($authorName, 0, 1));
        }

        $avatarHtml = sprintf(
            '<div style="width: 48px; height: 48px; border-radius: 50%%; background-color: %s; color: %s; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 16px; flex-shrink: 0;">%s</div>',
            e($nameColor),
            e($backgroundColor),
            e($initials)
        );
    }

    // Author info
    $authorHtml = sprintf(
        '<div style="display: flex; align-items: center; gap: 12px;">%s<div><div style="color: %s; font-weight: 600; font-size: 15px; line-height: 1.3;">%s</div><div style="color: %s; font-size: 13px; opacity: 0.7; line-height: 1.3;">%s</div></div></div>',
        $avatarHtml,
        e($nameColor),
        e($authorName),
        e($textColor),
        e($authorRole)
    );

    return sprintf(
        '<div class="%s" style="%s">%s%s%s%s</div>',
        e($classes),
        e(implode('; ', $cardStyles)),
        $quoteIconHtml,
        $ratingHtml,
        $quoteHtml,
        $authorHtml
    );
};
