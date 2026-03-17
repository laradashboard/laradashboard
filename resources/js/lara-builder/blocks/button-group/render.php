<?php

/**
 * Button Group Block - Server-side Renderer
 *
 * Renders multiple buttons in a flex row with responsive stacking.
 * Each button supports solid, outline, and ghost variants.
 */

return function (array $props, string $context = 'page', ?string $blockId = null): string {
    $buttons = $props['buttons'] ?? [];
    $alignment = $props['alignment'] ?? 'center';
    $gap = $props['gap'] ?? '12px';
    $size = $props['size'] ?? 'md';
    $stackOnMobile = (bool) ($props['stackOnMobile'] ?? true);
    $borderRadius = $props['borderRadius'] ?? '8px';
    $customClass = $props['customClass'] ?? '';
    $layoutStyles = $props['layoutStyles'] ?? [];

    if (empty($buttons)) {
        return '';
    }

    // Size mapping
    $sizeMap = [
        'sm' => ['fontSize' => '13px', 'padding' => '8px 16px'],
        'md' => ['fontSize' => '15px', 'padding' => '10px 20px'],
        'lg' => ['fontSize' => '17px', 'padding' => '14px 28px'],
    ];
    $currentSize = $sizeMap[$size] ?? $sizeMap['md'];

    // Alignment mapping
    $alignMap = [
        'left' => 'flex-start',
        'center' => 'center',
        'right' => 'flex-end',
    ];
    $justifyContent = $alignMap[$alignment] ?? 'center';

    // Build container styles
    $containerStyles = [
        'display: flex',
        'flex-wrap: wrap',
        'align-items: center',
        "justify-content: {$justifyContent}",
        "gap: {$gap}",
    ];

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

    $blockClasses = 'lb-block lb-button-group';
    if (! empty($customClass)) {
        $blockClasses .= ' ' . e($customClass);
    }

    // Unique ID for mobile stacking
    $groupId = 'btn-group-' . ($blockId ?? uniqid());

    // Build buttons HTML
    $buttonsHtml = '';
    foreach ($buttons as $btn) {
        $text = $btn['text'] ?? 'Button';
        $link = $btn['link'] ?? '#';
        $variant = $btn['variant'] ?? 'solid';
        $bgColor = $btn['backgroundColor'] ?? '#3b82f6';
        $txtColor = $btn['textColor'] ?? '#ffffff';
        $icon = $btn['icon'] ?? '';
        $iconPosition = $btn['iconPosition'] ?? 'left';

        // Build button styles
        $btnStyles = [
            'display: inline-flex',
            'align-items: center',
            'gap: 6px',
            'font-weight: 600',
            'text-decoration: none',
            'transition: opacity 0.2s',
            "border-radius: {$borderRadius}",
            "font-size: {$currentSize['fontSize']}",
            "padding: {$currentSize['padding']}",
        ];

        switch ($variant) {
            case 'solid':
                $btnStyles[] = "background-color: {$bgColor}";
                $btnStyles[] = "color: {$txtColor}";
                $btnStyles[] = 'border: 2px solid transparent';
                break;
            case 'outline':
                $btnStyles[] = 'background-color: transparent';
                $btnStyles[] = "color: {$txtColor}";
                $btnStyles[] = "border: 2px solid {$bgColor}";
                break;
            case 'ghost':
                $btnStyles[] = 'background-color: transparent';
                $btnStyles[] = "color: {$txtColor}";
                $btnStyles[] = 'border: 2px solid transparent';
                break;
            default:
                $btnStyles[] = "background-color: {$bgColor}";
                $btnStyles[] = "color: {$txtColor}";
                $btnStyles[] = 'border: 2px solid transparent';
                break;
        }

        // Icon HTML
        $iconHtml = '';
        if (! empty($icon)) {
            $iconHtml = sprintf(
                '<iconify-icon icon="%s" width="%s" height="%s" aria-hidden="true"></iconify-icon>',
                e($icon),
                e($currentSize['fontSize']),
                e($currentSize['fontSize'])
            );
        }

        $leftIcon = ($iconPosition === 'left' && ! empty($iconHtml)) ? $iconHtml : '';
        $rightIcon = ($iconPosition === 'right' && ! empty($iconHtml)) ? $iconHtml : '';

        $buttonsHtml .= sprintf(
            '<a href="%s" style="%s">%s%s%s</a>',
            e($link),
            e(implode('; ', $btnStyles)),
            $leftIcon,
            e($text),
            $rightIcon
        );
    }

    // Build stack-on-mobile style tag
    $mobileStyleHtml = '';
    if ($stackOnMobile) {
        $mobileStyleHtml = sprintf(
            '<style>@media (max-width: 640px) { #%s { flex-direction: column !important; align-items: stretch !important; } #%s a { justify-content: center; text-align: center; } }</style>',
            e($groupId),
            e($groupId)
        );
    }

    return sprintf(
        '%s<div class="%s" id="%s" style="%s">%s</div>',
        $mobileStyleHtml,
        e($blockClasses),
        e($groupId),
        e(implode('; ', $containerStyles)),
        $buttonsHtml
    );
};
