<?php

/**
 * Footer Block - Server-side Renderer
 */

return function (array $props, string $context = 'page', ?string $blockId = null): string {
    $companyName = $props['companyName'] ?? '';
    $address = $props['address'] ?? '';
    $email = $props['email'] ?? '';
    $phone = $props['phone'] ?? '';
    $unsubscribeText = $props['unsubscribeText'] ?? 'Unsubscribe';
    $unsubscribeUrl = $props['unsubscribeUrl'] ?? '#unsubscribe';
    $copyright = $props['copyright'] ?? '';
    $textColor = $props['textColor'] ?? '#6b7280';
    $linkColor = $props['linkColor'] ?? '#635bff';
    $fontSize = $props['fontSize'] ?? '12px';
    $align = $props['align'] ?? 'center';
    $layoutStyles = $props['layoutStyles'] ?? [];

    if ($context === 'email') {
        $styles = [
            'padding: 24px 16px',
            "text-align: {$align}",
            'border-top: 1px solid #e5e7eb',
            'font-family: Arial, Helvetica, sans-serif',
        ];

        $layoutCSS = \App\Helpers\EmailStyleHelper::buildLayoutStyles($layoutStyles);
        if ($layoutCSS) {
            $styles[] = $layoutCSS;
        }

        $html = sprintf('<div style="%s">', e(implode('; ', $styles)));

        if ($companyName) {
            $html .= sprintf('<p style="color: %s; font-size: 14px; font-weight: 600; margin: 0 0 12px 0;">%s</p>', e($textColor), e($companyName));
        }
        if ($address) {
            $html .= sprintf('<p style="color: %s; font-size: %s; margin: 0 0 8px 0;">%s</p>', e($textColor), e($fontSize), e($address));
        }
        if ($email || $phone) {
            $parts = [];
            if ($email) {
                $parts[] = sprintf('<a href="mailto:%s" style="color: %s; text-decoration: underline;">%s</a>', e($email), e($linkColor), e($email));
            }
            if ($phone) {
                $parts[] = e($phone);
            }
            $html .= sprintf('<p style="color: %s; font-size: %s; margin: 0 0 8px 0;">%s</p>', e($textColor), e($fontSize), implode(' | ', $parts));
        }
        if ($unsubscribeText) {
            $html .= sprintf('<p style="color: %s; font-size: %s; margin: 16px 0 0 0;"><a href="%s" style="color: %s; text-decoration: underline;">%s</a></p>', e($textColor), e($fontSize), e($unsubscribeUrl), e($linkColor), e($unsubscribeText));
        }
        if ($copyright) {
            $html .= sprintf('<p style="color: %s; font-size: 11px; margin: 12px 0 0 0;">%s</p>', e($textColor), e($copyright));
        }

        $html .= '</div>';

        return $html;
    }

    // Page context
    $customClass = $props['customClass'] ?? '';
    $blockClasses = 'lb-block lb-footer';
    if (! empty($customClass)) {
        $blockClasses .= ' ' . e($customClass);
    }

    $typography = $layoutStyles['typography'] ?? [];
    $pageTextColor = $typography['color'] ?? $textColor;
    $pageFontSize = $typography['fontSize'] ?? $fontSize;

    $pageStyles = [
        'padding: 24px 16px',
        "text-align: {$align}",
    ];

    if (empty($layoutStyles['border'])) {
        $pageStyles[] = 'border-top: 1px solid #e5e7eb';
    }

    if (! empty($layoutStyles['margin'])) {
        foreach (['top', 'right', 'bottom', 'left'] as $side) {
            if (isset($layoutStyles['margin'][$side])) {
                $pageStyles[] = "margin-{$side}: {$layoutStyles['margin'][$side]}";
            }
        }
    }
    if (! empty($layoutStyles['padding'])) {
        foreach (['top', 'right', 'bottom', 'left'] as $side) {
            if (isset($layoutStyles['padding'][$side])) {
                $pageStyles[] = "padding-{$side}: {$layoutStyles['padding'][$side]}";
            }
        }
    }
    if (! empty($layoutStyles['background']['color'])) {
        $pageStyles[] = "background-color: {$layoutStyles['background']['color']}";
    }

    $html = sprintf('<footer class="%s" style="%s">', e($blockClasses), e(implode('; ', $pageStyles)));

    if ($companyName) {
        $html .= sprintf('<p style="color: %s; font-size: 14px; font-weight: 600; margin: 0 0 12px 0;">%s</p>', e($pageTextColor), e($companyName));
    }
    if ($address) {
        $html .= sprintf('<p style="color: %s; font-size: %s; margin: 0 0 8px 0;">%s</p>', e($pageTextColor), e($pageFontSize), e($address));
    }
    if ($email || $phone) {
        $parts = [];
        if ($phone) {
            $parts[] = e($phone);
        }
        if ($email) {
            $parts[] = sprintf('<a href="mailto:%s" style="color: %s;">%s</a>', e($email), e($linkColor), e($email));
        }
        $html .= sprintf('<p style="color: %s; font-size: %s; margin: 0 0 8px 0;">%s</p>', e($pageTextColor), e($pageFontSize), implode(' | ', $parts));
    }
    if ($copyright) {
        $html .= sprintf('<p style="color: %s; font-size: 11px; margin: 12px 0 0 0;">%s</p>', e($pageTextColor), e($copyright));
    }

    $html .= '</footer>';

    return $html;
};
