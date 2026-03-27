<?php

/**
 * Alert Banner Block - Server-side Renderer
 *
 * Renders an announcement banner with optional badge pill, text, and link.
 * Uses an <a> tag wrapper when a link URL is provided, otherwise a <div>.
 */

return function (array $props, string $context = 'page', ?string $blockId = null): string {
    $text = $props['text'] ?? 'AI-powered module marketplace is live';
    $badgeText = $props['badgeText'] ?? 'NEW';
    $linkText = $props['linkText'] ?? '';
    $linkUrl = $props['linkUrl'] ?? '#';
    $backgroundColor = $props['backgroundColor'] ?? '#1e1b4b';
    $textColor = $props['textColor'] ?? '#e0e7ff';
    $badgeColor = $props['badgeColor'] ?? '#6366f1';
    $badgeTextColor = $props['badgeTextColor'] ?? '#ffffff';
    $align = $props['align'] ?? 'center';
    $padding = $props['padding'] ?? '12px 24px';
    $borderRadius = $props['borderRadius'] ?? '';
    $customClass = $props['customClass'] ?? '';
    $layoutStyles = $props['layoutStyles'] ?? [];

    // Alignment mapping
    $alignMap = [
        'left' => 'flex-start',
        'center' => 'center',
        'right' => 'flex-end',
    ];
    $justifyContent = $alignMap[$align] ?? 'center';

    // Container styles
    $containerStyles = [
        'display: flex',
        'align-items: center',
        "justify-content: {$justifyContent}",
        'gap: 10px',
        "background-color: {$backgroundColor}",
        "color: {$textColor}",
        "padding: {$padding}",
        'font-size: 14px',
        'font-weight: 500',
        'line-height: 1.5',
        'text-decoration: none',
    ];

    if (! empty($borderRadius)) {
        $containerStyles[] = "border-radius: {$borderRadius}";
    }

    // Apply layout styles
    if (! empty($layoutStyles['margin'])) {
        foreach (['top', 'right', 'bottom', 'left'] as $side) {
            if (! empty($layoutStyles['margin'][$side])) {
                $containerStyles[] = "margin-{$side}: {$layoutStyles['margin'][$side]}";
            }
        }
    }
    if (! empty($layoutStyles['padding'])) {
        foreach (['top', 'right', 'bottom', 'left'] as $side) {
            if (! empty($layoutStyles['padding'][$side])) {
                $containerStyles[] = "padding-{$side}: {$layoutStyles['padding'][$side]}";
            }
        }
    }

    $blockClasses = 'lb-block lb-alert-banner';
    if (! empty($customClass)) {
        $blockClasses .= ' ' . e($customClass);
    }

    // Badge HTML
    $badgeHtml = '';
    if (! empty($badgeText)) {
        $badgeStyles = [
            'display: inline-flex',
            'align-items: center',
            "background-color: {$badgeColor}",
            "color: {$badgeTextColor}",
            'font-size: 11px',
            'font-weight: 700',
            'padding: 2px 8px',
            'border-radius: 9999px',
            'letter-spacing: 0.05em',
            'text-transform: uppercase',
            'white-space: nowrap',
            'line-height: 1.4',
        ];

        $badgeHtml = sprintf(
            '<span style="%s">%s</span>',
            e(implode('; ', $badgeStyles)),
            e($badgeText)
        );
    }

    // Text HTML
    $textHtml = sprintf('<span>%s</span>', e($text));

    // Link / Arrow HTML
    $linkHtml = '';
    $hasLink = ! empty($linkText) || ($linkUrl !== '#' && ! empty($linkUrl));
    if ($hasLink) {
        $arrowHtml = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>';

        $linkContentHtml = '';
        if (! empty($linkText)) {
            $linkContentHtml .= sprintf(
                '<span style="font-size: 13px; text-decoration: underline;">%s</span>',
                e($linkText)
            );
        }
        $linkContentHtml .= $arrowHtml;

        $linkHtml = sprintf(
            '<span style="display: inline-flex; align-items: center; gap: 4px; opacity: 0.8;">%s</span>',
            $linkContentHtml
        );
    }

    // Inner content
    $innerHtml = $badgeHtml . $textHtml . $linkHtml;

    // Use <a> wrapper if linkUrl is set, otherwise <div>
    if (! empty($linkUrl) && $linkUrl !== '#') {
        return sprintf(
            '<a href="%s" class="%s" style="%s">%s</a>',
            e($linkUrl),
            e($blockClasses),
            e(implode('; ', $containerStyles)),
            $innerHtml
        );
    }

    return sprintf(
        '<div class="%s" style="%s">%s</div>',
        e($blockClasses),
        e(implode('; ', $containerStyles)),
        $innerHtml
    );
};
