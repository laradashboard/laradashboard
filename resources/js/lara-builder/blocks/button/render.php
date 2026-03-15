<?php

/**
 * Button Block - Server-side Renderer
 *
 * This callback is invoked by the BlockRenderer when processing content.
 * It generates accessible, secure button/link elements.
 *
 * Benefits of server-side rendering:
 * - Security: URL validation and sanitization
 * - SEO: proper rel attributes
 * - Accessibility: proper role and aria attributes
 * - Future-proof: update rendering without migrating stored content
 */

return function (array $props, string $context = 'page', ?string $blockId = null): string {
    $text = $props['text'] ?? 'Click Here';
    $link = $props['link'] ?? '';
    $target = $props['target'] ?? '_self';
    $align = $props['align'] ?? 'center';
    $backgroundColor = $props['backgroundColor'] ?? '#635bff';
    $textColor = $props['textColor'] ?? '#ffffff';
    $borderRadius = $props['borderRadius'] ?? '6px';
    $padding = $props['padding'] ?? '12px 24px';
    $fontSize = $props['fontSize'] ?? '16px';
    $fontWeight = $props['fontWeight'] ?? '600';
    $nofollow = $props['nofollow'] ?? false;
    $sponsored = $props['sponsored'] ?? false;
    $layoutStyles = $props['layoutStyles'] ?? [];
    $customCSS = $props['customCSS'] ?? '';
    $customClass = $props['customClass'] ?? '';

    // Email context: MSO-compatible button
    if ($context === 'email') {
        $background = $layoutStyles['background'] ?? [];
        $typography = $layoutStyles['typography'] ?? [];
        $border = $layoutStyles['border'] ?? [];

        $bgColor = $background['color'] ?? $backgroundColor;
        $txtColor = $typography['color'] ?? $textColor;
        $fSize = $typography['fontSize'] ?? $fontSize;
        $fWeight = $typography['fontWeight'] ?? $fontWeight;
        $bRadius = ! empty($border['radius']) ? $border['radius'] : $borderRadius;
        $btnLink = ! empty($link) ? $link : '#';

        // Parse padding for MSO
        $vPad = (int) $padding;

        $buttonHtml = sprintf(
            '<a href="%s" target="_blank" style="display: inline-block; background-color: %s; color: %s; font-size: %s; font-weight: %s; font-family: Arial, Helvetica, sans-serif; text-decoration: none; padding: %s; border-radius: %s; text-align: center;">%s</a>',
            e($btnLink),
            e($bgColor),
            e($txtColor),
            e($fSize),
            e($fWeight),
            e($padding),
            e($bRadius),
            $text
        );

        $wrapperStyles = ["text-align: {$align}", 'padding: 10px 0'];

        $layoutCSS = \App\Helpers\EmailStyleHelper::buildLayoutStyles($layoutStyles);
        if ($layoutCSS) {
            $wrapperStyles[] = $layoutCSS;
        }

        return sprintf(
            '<div style="%s">%s</div>',
            e(implode('; ', $wrapperStyles)),
            $buttonHtml
        );
    }

    // Build block classes
    $blockClasses = 'lb-block lb-button';
    if (! empty($customClass)) {
        $blockClasses .= ' ' . e($customClass);
    }

    // Build button element styles
    $buttonStyles = [
        'display: inline-block',
        'text-decoration: none',
        'border: none',
        'cursor: pointer',
        'transition: opacity 0.2s ease',
    ];

    // Apply layout styles if present, otherwise use direct props
    $background = $layoutStyles['background'] ?? [];
    $typography = $layoutStyles['typography'] ?? [];
    $border = $layoutStyles['border'] ?? [];

    // Background color
    if (! empty($background['color'])) {
        $buttonStyles[] = "background-color: {$background['color']}";
    } else {
        $buttonStyles[] = "background-color: {$backgroundColor}";
    }

    // Text color
    if (! empty($typography['color'])) {
        $buttonStyles[] = "color: {$typography['color']}";
    } else {
        $buttonStyles[] = "color: {$textColor}";
    }

    // Font size
    if (! empty($typography['fontSize'])) {
        $buttonStyles[] = "font-size: {$typography['fontSize']}";
    } else {
        $buttonStyles[] = "font-size: {$fontSize}";
    }

    // Font weight
    if (! empty($typography['fontWeight'])) {
        $buttonStyles[] = "font-weight: {$typography['fontWeight']}";
    } else {
        $buttonStyles[] = "font-weight: {$fontWeight}";
    }

    // Border radius
    if (! empty($border['radius'])) {
        $buttonStyles[] = "border-radius: {$border['radius']}";
    } else {
        $buttonStyles[] = "border-radius: {$borderRadius}";
    }

    // Padding
    $buttonStyles[] = "padding: {$padding}";

    // Add layout styles (margin, width, etc.)
    if (! empty($layoutStyles['margin'])) {
        $margin = $layoutStyles['margin'];
        foreach (['top', 'right', 'bottom', 'left'] as $side) {
            if (isset($margin[$side])) {
                $buttonStyles[] = "margin-{$side}: {$margin[$side]}";
            }
        }
    }

    // Custom CSS
    if (! empty($customCSS)) {
        $buttonStyles[] = $customCSS;
    }

    $styleAttr = implode('; ', $buttonStyles);

    // Build the button/link element
    if (! empty($link)) {
        // Sanitize URL - allow http, https, mailto, tel, and anchor links
        $sanitizedLink = filter_var($link, FILTER_SANITIZE_URL);

        // Validate URL protocol
        $allowedProtocols = ['http://', 'https://', 'mailto:', 'tel:', '#', '/'];
        $isValidLink = false;
        foreach ($allowedProtocols as $protocol) {
            if (str_starts_with($sanitizedLink, $protocol)) {
                $isValidLink = true;
                break;
            }
        }

        if (! $isValidLink && ! str_starts_with($sanitizedLink, '/')) {
            // Relative path without leading slash, or invalid
            $sanitizedLink = '#';
        }

        // Build rel attribute
        $relParts = [];
        if ($target === '_blank') {
            $relParts[] = 'noopener';
            $relParts[] = 'noreferrer';
        }
        if ($nofollow) {
            $relParts[] = 'nofollow';
        }
        if ($sponsored) {
            $relParts[] = 'sponsored';
        }

        $relAttr = ! empty($relParts) ? sprintf(' rel="%s"', implode(' ', $relParts)) : '';
        $targetAttr = $target !== '_self' ? sprintf(' target="%s"', e($target)) : '';

        $buttonElement = sprintf(
            '<a href="%s"%s%s class="%s" style="%s">%s</a>',
            e($sanitizedLink),
            $targetAttr,
            $relAttr,
            e($blockClasses),
            e($styleAttr),
            $text // Allow HTML formatting (bold, italic, etc.)
        );
    } else {
        // No link - render as span
        $buttonElement = sprintf(
            '<span class="%s" style="%s">%s</span>',
            e($blockClasses),
            e($styleAttr),
            $text
        );
    }

    // Wrapper for alignment
    return sprintf(
        '<div class="lb-button-wrapper" style="text-align: %s; padding: 10px 0;">%s</div>',
        e($align),
        $buttonElement
    );
};
