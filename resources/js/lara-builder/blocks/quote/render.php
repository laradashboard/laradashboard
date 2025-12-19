<?php

/**
 * Quote Block - Server-side Renderer
 *
 * This callback is invoked by the BlockRenderer when processing content.
 * It generates semantic blockquote elements with proper citation.
 *
 * Benefits of server-side rendering:
 * - Semantic HTML (blockquote, cite)
 * - Content sanitization
 * - Future shortcode/variable support
 * - Future-proof: update rendering without migrating stored content
 */

return function (array $props, string $context = 'page', ?string $blockId = null): string {
    $text = $props['text'] ?? '';
    $author = $props['author'] ?? '';
    $authorTitle = $props['authorTitle'] ?? '';
    $align = $props['align'] ?? 'left';
    $borderColor = $props['borderColor'] ?? '#635bff';
    $backgroundColor = $props['backgroundColor'] ?? '#f8fafc';
    $textColor = $props['textColor'] ?? '#475569';
    $authorColor = $props['authorColor'] ?? '#1e293b';
    $layoutStyles = $props['layoutStyles'] ?? [];
    $customCSS = $props['customCSS'] ?? '';
    $customClass = $props['customClass'] ?? '';

    // Build block classes
    $blockClasses = 'lb-block lb-quote';
    if (! empty($customClass)) {
        $blockClasses .= ' ' . e($customClass);
    }

    // Build block styles
    $blockStyles = [
        'padding: 20px',
        'padding-left: 24px',
        "text-align: {$align}",
        'margin: 10px 0',
    ];

    // Background - check layoutStyles first
    if (! empty($layoutStyles['background']['color'])) {
        $blockStyles[] = "background-color: {$layoutStyles['background']['color']}";
    } else {
        $blockStyles[] = "background-color: {$backgroundColor}";
    }

    // Border - check layoutStyles first
    if (! empty($layoutStyles['border'])) {
        $border = $layoutStyles['border'];
        if (! empty($border['width']['left'])) {
            $blockStyles[] = "border-left-width: {$border['width']['left']}";
        } else {
            $blockStyles[] = 'border-left-width: 4px';
        }
        $blockStyles[] = 'border-left-style: solid';
        $blockStyles[] = "border-left-color: " . ($border['color'] ?? $borderColor);

        if (! empty($border['radius'])) {
            $radius = $border['radius'];
            if (! empty($radius['topLeft'])) {
                $blockStyles[] = "border-top-left-radius: {$radius['topLeft']}";
            }
            if (! empty($radius['bottomLeft'])) {
                $blockStyles[] = "border-bottom-left-radius: {$radius['bottomLeft']}";
            }
        } else {
            $blockStyles[] = 'border-radius: 4px';
        }
    } else {
        $blockStyles[] = "border-left: 4px solid {$borderColor}";
        $blockStyles[] = 'border-radius: 4px';
    }

    // Layout styles (margin, padding overrides)
    if (! empty($layoutStyles['margin'])) {
        $margin = $layoutStyles['margin'];
        foreach (['top', 'right', 'bottom', 'left'] as $side) {
            if (isset($margin[$side])) {
                $blockStyles[] = "margin-{$side}: {$margin[$side]}";
            }
        }
    }

    if (! empty($layoutStyles['padding'])) {
        $padding = $layoutStyles['padding'];
        foreach (['top', 'right', 'bottom', 'left'] as $side) {
            if (isset($padding[$side])) {
                $blockStyles[] = "padding-{$side}: {$padding[$side]}";
            }
        }
    }

    // Custom CSS
    if (! empty($customCSS)) {
        $blockStyles[] = $customCSS;
    }

    $styleAttr = implode('; ', $blockStyles);

    // Build quote text - allow HTML formatting (bold, italic, etc.)
    $quoteHtml = sprintf(
        '<p style="color: %s; font-size: 1.125rem; font-style: italic; line-height: 1.6; margin: 0 0 12px 0;">"%s"</p>',
        e($textColor),
        $text // Allow HTML formatting
    );

    // Build author citation
    $authorHtml = '';
    if (! empty($author)) {
        $authorHtml = sprintf(
            '<cite style="color: %s; font-size: 0.875rem; font-weight: 600; font-style: normal; display: block;">%s</cite>',
            e($authorColor),
            e($author)
        );
    }

    // Build author title
    $authorTitleHtml = '';
    if (! empty($authorTitle)) {
        $authorTitleHtml = sprintf(
            '<span style="color: %s; font-size: 0.75rem;">%s</span>',
            e($textColor),
            e($authorTitle)
        );
    }

    return sprintf(
        '<blockquote class="%s" style="%s">%s%s%s</blockquote>',
        e($blockClasses),
        e($styleAttr),
        $quoteHtml,
        $authorHtml,
        $authorTitleHtml
    );
};
