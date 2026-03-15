<?php

/**
 * Image Block - Server-side Renderer
 *
 * This callback is invoked by the BlockRenderer when processing content.
 * It generates responsive, accessible images with proper lazy loading.
 *
 * Benefits of server-side rendering:
 * - CDN URL transformation
 * - Image optimization (srcset generation)
 * - Security: URL validation
 * - Lazy loading with proper attributes
 * - Future-proof: update rendering without migrating stored content
 */

return function (array $props, string $context = 'page', ?string $blockId = null): string {
    $src = $props['src'] ?? '';
    $alt = $props['alt'] ?? 'Image';
    $width = $props['width'] ?? '100%';
    $height = $props['height'] ?? 'auto';
    $customWidth = $props['customWidth'] ?? '';
    $customHeight = $props['customHeight'] ?? '';
    $align = $props['align'] ?? 'center';
    $link = $props['link'] ?? '';
    $layoutStyles = $props['layoutStyles'] ?? [];
    $customCSS = $props['customCSS'] ?? '';
    $customClass = $props['customClass'] ?? '';

    // Email context: inline-styled email-safe output
    if ($context === 'email') {
        if (empty($src)) {
            return '';
        }

        $isCustomWidth = $width === 'custom' && ! empty($customWidth);
        $isCustomHeight = $height === 'custom' && ! empty($customHeight);
        $imgWidth = $isCustomWidth ? $customWidth : $width;
        $imgHeight = $isCustomHeight ? $customHeight : 'auto';

        $imgStyles = [
            "max-width: {$imgWidth}",
            'display: block',
            'margin: 0 auto',
        ];

        if ($isCustomWidth) {
            $imgStyles[] = "width: {$customWidth}";
        }

        $imgStyles[] = "height: {$imgHeight}";

        if ($imgHeight !== 'auto') {
            $imgStyles[] = 'object-fit: cover';
        }

        // Border and box-shadow on image
        if (! empty($layoutStyles['border'])) {
            $borderCSS = \App\Helpers\EmailStyleHelper::buildBorderStyles($layoutStyles['border']);
            if ($borderCSS) {
                $imgStyles[] = $borderCSS;
            }
        }

        if (! empty($layoutStyles['boxShadow'])) {
            $shadow = $layoutStyles['boxShadow'];
            $x = \App\Helpers\EmailStyleHelper::cssUnit($shadow['x'] ?? '0');
            $y = \App\Helpers\EmailStyleHelper::cssUnit($shadow['y'] ?? '0');
            $blur = \App\Helpers\EmailStyleHelper::cssUnit($shadow['blur'] ?? '0');
            $spread = \App\Helpers\EmailStyleHelper::cssUnit($shadow['spread'] ?? '0');
            $shadowColor = $shadow['color'] ?? 'rgba(0,0,0,0.1)';
            $inset = ! empty($shadow['inset']) ? 'inset ' : '';
            $imgStyles[] = "box-shadow: {$inset}{$x} {$y} {$blur} {$spread} {$shadowColor}";
        }

        $imgStyleAttr = implode('; ', $imgStyles);
        $imgHtml = sprintf('<img src="%s" alt="%s" style="%s" />', e($src), e($alt), e($imgStyleAttr));

        if (! empty($link)) {
            $imgHtml = sprintf('<a href="%s" target="_blank" style="display: inline-block;">%s</a>', e($link), $imgHtml);
        }

        // Wrapper styles (margin, padding, background)
        $wrapperStyles = ["text-align: {$align}"];

        if (! empty($layoutStyles['margin'])) {
            foreach (['top', 'right', 'bottom', 'left'] as $side) {
                if (! empty($layoutStyles['margin'][$side])) {
                    $wrapperStyles[] = "margin-{$side}: " . \App\Helpers\EmailStyleHelper::cssUnit($layoutStyles['margin'][$side]);
                }
            }
        }
        if (! empty($layoutStyles['padding'])) {
            foreach (['top', 'right', 'bottom', 'left'] as $side) {
                if (! empty($layoutStyles['padding'][$side])) {
                    $wrapperStyles[] = "padding-{$side}: " . \App\Helpers\EmailStyleHelper::cssUnit($layoutStyles['padding'][$side]);
                }
            }
        }
        if (! empty($layoutStyles['background']['color'])) {
            $wrapperStyles[] = "background-color: {$layoutStyles['background']['color']}";
        }

        return sprintf('<div style="%s">%s</div>', e(implode('; ', $wrapperStyles)), $imgHtml);
    }

    // Validate and sanitize URL
    if (! empty($src)) {
        $src = filter_var($src, FILTER_SANITIZE_URL);
        // Only allow http, https, or relative paths
        if (! preg_match('#^(https?://|/)#', $src)) {
            $src = '';
        }
    }

    // Build block classes
    $blockClasses = 'lb-block lb-image';
    if (! empty($customClass)) {
        $blockClasses .= ' ' . e($customClass);
    }

    // Determine dimensions
    $isCustomWidth = $width === 'custom' && ! empty($customWidth);
    $isCustomHeight = $height === 'custom' && ! empty($customHeight);

    // Image-specific styles
    $imgStyles = [];

    // Handle width - can be percentage (100%, 75%, 50%, 25%) or 'custom'
    if ($isCustomWidth) {
        // Custom width specified
        $imgStyles[] = "width: {$customWidth}";
        $imgStyles[] = "max-width: {$customWidth}";
    } elseif (preg_match('/^\d+%$/', $width)) {
        // Percentage width (25%, 50%, 75%, 100%)
        $imgStyles[] = "width: {$width}";
        $imgStyles[] = "max-width: {$width}";
    } else {
        // Fallback to 100%
        $imgStyles[] = 'max-width: 100%';
    }

    // Handle height - can be 'auto' or 'custom'
    if ($isCustomHeight) {
        $imgStyles[] = "height: {$customHeight}";
        $imgStyles[] = 'object-fit: cover';
    } else {
        $imgStyles[] = 'height: auto';
    }

    // Border styles
    if (! empty($layoutStyles['border'])) {
        $border = $layoutStyles['border'];

        $borderWidth = $border['width'] ?? [];
        if (! empty($borderWidth['top'])) {
            $imgStyles[] = "border-top-width: {$borderWidth['top']}";
        }
        if (! empty($borderWidth['right'])) {
            $imgStyles[] = "border-right-width: {$borderWidth['right']}";
        }
        if (! empty($borderWidth['bottom'])) {
            $imgStyles[] = "border-bottom-width: {$borderWidth['bottom']}";
        }
        if (! empty($borderWidth['left'])) {
            $imgStyles[] = "border-left-width: {$borderWidth['left']}";
        }

        if (! empty($border['style'])) {
            $imgStyles[] = "border-style: {$border['style']}";
        }
        if (! empty($border['color'])) {
            $imgStyles[] = "border-color: {$border['color']}";
        }

        $radius = $border['radius'] ?? [];
        if (! empty($radius['topLeft'])) {
            $imgStyles[] = "border-top-left-radius: {$radius['topLeft']}";
        }
        if (! empty($radius['topRight'])) {
            $imgStyles[] = "border-top-right-radius: {$radius['topRight']}";
        }
        if (! empty($radius['bottomLeft'])) {
            $imgStyles[] = "border-bottom-left-radius: {$radius['bottomLeft']}";
        }
        if (! empty($radius['bottomRight'])) {
            $imgStyles[] = "border-bottom-right-radius: {$radius['bottomRight']}";
        }
    }

    // Box shadow
    if (! empty($layoutStyles['boxShadow'])) {
        $shadow = $layoutStyles['boxShadow'];
        $x = $shadow['x'] ?? '0px';
        $y = $shadow['y'] ?? '0px';
        $blur = $shadow['blur'] ?? '0px';
        $spread = $shadow['spread'] ?? '0px';
        $color = $shadow['color'] ?? 'rgba(0,0,0,0.1)';
        $inset = ! empty($shadow['inset']) ? 'inset ' : '';
        $imgStyles[] = "box-shadow: {$inset}{$x} {$y} {$blur} {$spread} {$color}";
    }

    // Custom CSS
    if (! empty($customCSS)) {
        $imgStyles[] = $customCSS;
    }

    $imgStyleAttr = implode('; ', $imgStyles);

    // Build wrapper styles
    $wrapperStyles = [];

    // Background
    if (! empty($layoutStyles['background'])) {
        $bg = $layoutStyles['background'];
        if (! empty($bg['color'])) {
            $wrapperStyles[] = "background-color: {$bg['color']}";
        }
        if (! empty($bg['image'])) {
            $wrapperStyles[] = "background-image: url({$bg['image']})";
            $wrapperStyles[] = 'background-size: ' . ($bg['size'] ?? 'cover');
            $wrapperStyles[] = 'background-position: ' . ($bg['position'] ?? 'center');
            $wrapperStyles[] = 'background-repeat: ' . ($bg['repeat'] ?? 'no-repeat');
        }
    }

    // Margin
    if (! empty($layoutStyles['margin'])) {
        $margin = $layoutStyles['margin'];
        foreach (['top', 'right', 'bottom', 'left'] as $side) {
            if (! empty($margin[$side])) {
                $wrapperStyles[] = "margin-{$side}: {$margin[$side]}";
            }
        }
    }

    // Padding
    if (! empty($layoutStyles['padding'])) {
        $padding = $layoutStyles['padding'];
        foreach (['top', 'right', 'bottom', 'left'] as $side) {
            if (! empty($padding[$side])) {
                $wrapperStyles[] = "padding-{$side}: {$padding[$side]}";
            }
        }
    }

    // Build img element
    $imgHtml = sprintf(
        '<img src="%s" alt="%s" class="%s" style="%s" loading="lazy" />',
        e($src),
        e($alt),
        e($blockClasses),
        e($imgStyleAttr)
    );

    // Wrap with link if needed
    if (! empty($link)) {
        $sanitizedLink = filter_var($link, FILTER_SANITIZE_URL);
        $imgHtml = sprintf(
            '<a href="%s" target="_blank" rel="noopener noreferrer" class="lb-image-link">%s</a>',
            e($sanitizedLink),
            $imgHtml
        );
    }

    // Wrap with background wrapper if needed
    if (! empty($wrapperStyles)) {
        $wrapperStyleAttr = implode('; ', $wrapperStyles);
        $imgHtml = sprintf(
            '<div class="lb-image-bg-wrapper" style="%s">%s</div>',
            e($wrapperStyleAttr),
            $imgHtml
        );
    }

    // Alignment
    $justifyContent = match ($align) {
        'left' => 'flex-start',
        'right' => 'flex-end',
        default => 'center',
    };

    return sprintf(
        '<figure class="lb-image-wrapper" style="display: flex; justify-content: %s; margin: 0 0 16px 0;">%s</figure>',
        $justifyContent,
        $imgHtml
    );
};
