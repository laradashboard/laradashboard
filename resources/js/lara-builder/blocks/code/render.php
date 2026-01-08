<?php

/**
 * Code Block - Server-side Renderer
 *
 * This callback is invoked by the BlockRenderer when processing content.
 * It generates code snippets with proper escaping and optional syntax highlighting.
 *
 * Benefits of server-side rendering:
 * - Proper HTML escaping of code content
 * - Server-side syntax highlighting (future)
 * - Security: prevents XSS from code content
 * - Future-proof: update rendering without migrating stored content
 */

return function (array $props, string $context = 'page', ?string $blockId = null): string {
    $code = $props['code'] ?? '';
    $language = $props['language'] ?? 'plaintext';
    $fontSize = $props['fontSize'] ?? '14px';
    $backgroundColor = $props['backgroundColor'] ?? '#1e1e1e';
    $textColor = $props['textColor'] ?? '#d4d4d4';
    $borderRadius = $props['borderRadius'] ?? '8px';
    $layoutStyles = $props['layoutStyles'] ?? [];
    $customCSS = $props['customCSS'] ?? '';
    $customClass = $props['customClass'] ?? '';

    // Build block classes
    $blockClasses = 'lb-block lb-code';
    if (! empty($customClass)) {
        $blockClasses .= ' ' . e($customClass);
    }

    // Escape code content to prevent XSS
    $escapedCode = e($code);

    // Build block styles - minimal wrapper, let Prism handle most styling
    $blockStyles = [];

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

    // Border
    if (! empty($layoutStyles['border'])) {
        $border = $layoutStyles['border'];
        if (! empty($border['width'])) {
            $width = $border['width'];
            foreach (['top', 'right', 'bottom', 'left'] as $side) {
                if (! empty($width[$side])) {
                    $blockStyles[] = "border-{$side}-width: {$width[$side]}";
                }
            }
        }
        if (! empty($border['style'])) {
            $blockStyles[] = "border-style: {$border['style']}";
        }
        if (! empty($border['color'])) {
            $blockStyles[] = "border-color: {$border['color']}";
        }
        if (! empty($border['radius'])) {
            $radius = $border['radius'];
            if (! empty($radius['topLeft'])) {
                $blockStyles[] = "border-top-left-radius: {$radius['topLeft']}";
            }
            if (! empty($radius['topRight'])) {
                $blockStyles[] = "border-top-right-radius: {$radius['topRight']}";
            }
            if (! empty($radius['bottomLeft'])) {
                $blockStyles[] = "border-bottom-left-radius: {$radius['bottomLeft']}";
            }
            if (! empty($radius['bottomRight'])) {
                $blockStyles[] = "border-bottom-right-radius: {$radius['bottomRight']}";
            }
        }
    }

    // Custom CSS
    if (! empty($customCSS)) {
        $blockStyles[] = $customCSS;
    }

    $wrapperStyle = ! empty($blockStyles) ? implode('; ', $blockStyles) : '';

    // Sanitize language for class attribute
    $safeLanguage = preg_replace('/[^a-zA-Z0-9_-]/', '', $language);

    // Pre styles - Prism will add its own background, we just set font sizing
    $preStyles = [
        'margin: 0',
        'white-space: pre-wrap',
        'word-wrap: break-word',
        "font-size: {$fontSize}",
        'line-height: 1.5',
        "border-radius: {$borderRadius}",
    ];
    $preStyleAttr = implode('; ', $preStyles);

    $wrapperStyleAttr = $wrapperStyle ? sprintf(' style="%s"', e($wrapperStyle)) : '';

    return sprintf(
        '<div class="%s"%s><pre class="language-%s" style="%s"><code class="language-%s">%s</code></pre></div>',
        e($blockClasses),
        $wrapperStyleAttr,
        e($safeLanguage),
        e($preStyleAttr),
        e($safeLanguage),
        $escapedCode
    );
};
