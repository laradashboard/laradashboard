<?php

/**
 * Time to Read Block - Server-side Renderer
 *
 * This callback is invoked by the BlockRenderer when processing content.
 * It calculates the reading time based on the total word count of the page content.
 *
 * Features:
 * - Calculates reading time from total page word count
 * - Supports range display (e.g., "1-2 minutes")
 * - Configurable words per minute
 * - Custom prefix/suffix text
 * - Optional clock icon
 */

return function (array $props, string $context = 'page', ?string $blockId = null): string {
    $wordsPerMinute = (int) ($props['wordsPerMinute'] ?? 200);
    $displayAsRange = $props['displayAsRange'] ?? true;
    $prefix = $props['prefix'] ?? '';
    $suffix = $props['suffix'] ?? '';
    $align = $props['align'] ?? 'left';
    $color = $props['color'] ?? '#666666';
    $fontSize = $props['fontSize'] ?? '14px';
    $iconColor = $props['iconColor'] ?? '#666666';
    $showIcon = $props['showIcon'] ?? true;
    $layoutStyles = $props['layoutStyles'] ?? [];
    $customCSS = $props['customCSS'] ?? '';
    $customClass = $props['customClass'] ?? '';

    // Get page content word count from context
    // This will be passed by the BlockRenderer or calculated from surrounding content
    $wordCount = $props['_wordCount'] ?? 0;

    // Calculate reading time
    $minutes = $wordsPerMinute > 0 ? ceil($wordCount / $wordsPerMinute) : 1;
    $minutes = max(1, $minutes); // Minimum 1 minute

    // Format the reading time text
    if ($displayAsRange) {
        $minMinutes = max(1, $minutes - 1);
        $maxMinutes = $minutes;
        if ($minMinutes === $maxMinutes) {
            $timeText = $minutes === 1 ? __('1 minute') : sprintf(__('%d minutes'), $minutes);
        } else {
            $timeText = sprintf(__('%d-%d minutes'), $minMinutes, $maxMinutes);
        }
    } else {
        $timeText = $minutes === 1 ? __('1 minute') : sprintf(__('%d minutes'), $minutes);
    }

    // Build block classes
    $blockClasses = 'lb-block lb-time-to-read';
    if (! empty($customClass)) {
        $blockClasses .= ' ' . e($customClass);
    }

    // Build inline styles
    $styles = [];
    $styles[] = 'display: flex';
    $styles[] = 'align-items: center';
    $styles[] = 'gap: 6px';

    // Alignment
    $justifyMap = [
        'left' => 'flex-start',
        'center' => 'center',
        'right' => 'flex-end',
    ];
    $styles[] = 'justify-content: ' . ($justifyMap[$align] ?? 'flex-start');

    // Layout styles
    if (! empty($layoutStyles['margin'])) {
        $margin = $layoutStyles['margin'];
        foreach (['top', 'right', 'bottom', 'left'] as $side) {
            if (isset($margin[$side])) {
                $styles[] = "margin-{$side}: {$margin[$side]}";
            }
        }
    }

    if (! empty($layoutStyles['padding'])) {
        $padding = $layoutStyles['padding'];
        foreach (['top', 'right', 'bottom', 'left'] as $side) {
            if (isset($padding[$side])) {
                $styles[] = "padding-{$side}: {$padding[$side]}";
            }
        }
    }

    // Custom CSS
    if (! empty($customCSS)) {
        $styles[] = $customCSS;
    }

    $containerStyleAttr = implode('; ', $styles);

    // Text styles
    $textStyles = [];
    $textColor = $layoutStyles['typography']['color'] ?? $color;
    $textFontSize = $layoutStyles['typography']['fontSize'] ?? $fontSize;
    $textStyles[] = "color: {$textColor}";
    $textStyles[] = "font-size: {$textFontSize}";
    $textStyles[] = 'line-height: 1.4';
    $textStyleAttr = implode('; ', $textStyles);

    // Icon styles
    $iconStyles = [];
    $iconStyles[] = "color: {$iconColor}";
    $iconStyles[] = 'width: 16px';
    $iconStyles[] = 'height: 16px';
    $iconStyles[] = 'flex-shrink: 0';
    $iconStyleAttr = implode('; ', $iconStyles);

    // Clock SVG icon
    $iconSvg = $showIcon ? sprintf(
        '<svg style="%s" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
        e($iconStyleAttr)
    ) : '';

    // Build display text
    $displayText = e($prefix) . $timeText . e($suffix);

    return sprintf(
        '<div class="%s" style="%s">%s<span style="%s">%s</span></div>',
        e($blockClasses),
        e($containerStyleAttr),
        $iconSvg,
        e($textStyleAttr),
        $displayText
    );
};
