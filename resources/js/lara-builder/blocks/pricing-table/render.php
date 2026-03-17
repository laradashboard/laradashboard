<?php

/**
 * Pricing Table Block - Server-side Renderer
 *
 * Renders a pricing card with plan name, price, features, and CTA button.
 */

return function (array $props, string $context = 'page', ?string $blockId = null): string {
    $planName = $props['planName'] ?? 'Pro';
    $price = $props['price'] ?? '$29';
    $period = $props['period'] ?? '/month';
    $description = $props['description'] ?? '';
    $features = $props['features'] ?? [];
    $buttonText = $props['buttonText'] ?? 'Get Started';
    $buttonLink = $props['buttonLink'] ?? '#';
    $buttonColor = $props['buttonColor'] ?? '#3b82f6';
    $buttonTextColor = $props['buttonTextColor'] ?? '#ffffff';
    $highlighted = $props['highlighted'] ?? false;
    $badgeText = $props['badgeText'] ?? '';
    $backgroundColor = $props['backgroundColor'] ?? '#ffffff';
    $headerColor = $props['headerColor'] ?? '#111827';
    $priceColor = $props['priceColor'] ?? '#3b82f6';
    $textColor = $props['textColor'] ?? '#6b7280';
    $borderColor = $props['borderColor'] ?? '#e5e7eb';
    $borderRadius = $props['borderRadius'] ?? '16px';
    $customClass = $props['customClass'] ?? '';
    $customCSS = $props['customCSS'] ?? '';

    $uid = $blockId ?? uniqid('pt-');
    $classes = trim("lb-block lb-pricing-table {$customClass}");

    // Card styles
    $cardStyles = [
        "background-color: {$backgroundColor}",
        sprintf('border: %s solid %s', $highlighted ? '2px' : '1px', $highlighted ? $priceColor : $borderColor),
        "border-radius: {$borderRadius}",
        'padding: 32px 24px',
        'position: relative',
        'overflow: hidden',
        'max-width: 380px',
        'margin: 0 auto',
        'font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
    ];

    if ($highlighted) {
        $cardStyles[] = 'transform: scale(1.02)';
        $cardStyles[] = 'box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04)';
    } else {
        $cardStyles[] = 'box-shadow: 0 1px 3px rgba(0,0,0,0.1)';
    }

    if ($customCSS) {
        $cardStyles[] = $customCSS;
    }

    // Badge HTML
    $badgeHtml = '';
    if (! empty($badgeText)) {
        $badgeHtml = sprintf(
            '<div style="position: absolute; top: 12px; right: -32px; background-color: %s; color: %s; font-size: 11px; font-weight: 600; padding: 4px 40px; transform: rotate(45deg); text-transform: uppercase; letter-spacing: 0.05em;">%s</div>',
            e($priceColor),
            e($buttonTextColor),
            e($badgeText)
        );
    }

    // Plan Name
    $planNameHtml = sprintf(
        '<h3 style="color: %s; font-size: 20px; font-weight: 700; margin: 0 0 8px 0;">%s</h3>',
        e($headerColor),
        e($planName)
    );

    // Price
    $priceHtml = sprintf(
        '<div style="margin: 0 0 8px 0;"><span style="color: %s; font-size: 42px; font-weight: 800; line-height: 1;">%s</span><span style="color: %s; font-size: 16px; font-weight: 400;">%s</span></div>',
        e($priceColor),
        e($price),
        e($textColor),
        e($period)
    );

    // Description
    $descriptionHtml = '';
    if (! empty($description)) {
        $descriptionHtml = sprintf(
            '<p style="color: %s; font-size: 14px; margin: 0 0 24px 0; line-height: 1.5;">%s</p>',
            e($textColor),
            e($description)
        );
    }

    // Features list
    $featuresHtml = '';
    if (! empty($features)) {
        $featureItems = '';
        $lastIdx = count($features) - 1;
        foreach ($features as $index => $feature) {
            $included = $feature['included'] ?? true;
            $text = $feature['text'] ?? '';
            $isLast = $index === $lastIdx;

            $iconHtml = $included
                ? sprintf('<iconify-icon icon="mdi:check-circle" width="20" height="20" style="color: #22c55e; flex-shrink: 0;"></iconify-icon>')
                : sprintf('<iconify-icon icon="mdi:close-circle" width="20" height="20" style="color: #d1d5db; flex-shrink: 0;"></iconify-icon>');

            $textStyle = $included
                ? sprintf('color: %s; font-size: 14px;', e($headerColor))
                : 'color: #9ca3af; font-size: 14px; text-decoration: line-through;';

            $borderBottom = $isLast ? 'none' : "1px solid {$borderColor}";

            $featureItems .= sprintf(
                '<li style="display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: %s;">%s<span style="%s">%s</span></li>',
                e($borderBottom),
                $iconHtml,
                $textStyle,
                e($text)
            );
        }

        $featuresHtml = sprintf(
            '<ul style="list-style: none; padding: 0; margin: 0 0 28px 0;">%s</ul>',
            $featureItems
        );
    }

    // CTA Button
    $buttonHtml = sprintf(
        '<a href="%s" style="display: block; background-color: %s; color: %s; padding: 12px 24px; border-radius: 8px; text-align: center; font-weight: 600; font-size: 15px; text-decoration: none; transition: opacity 0.2s;">%s</a>',
        e($buttonLink),
        e($buttonColor),
        e($buttonTextColor),
        e($buttonText)
    );

    // Style block for highlighted hover effect
    $styleBlock = '';
    if ($highlighted) {
        $styleBlock = sprintf(
            '<style>.lb-pricing-table-%1$s:hover { transform: scale(1.04) !important; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.15) !important; }</style>',
            e($uid)
        );
        $classes .= " lb-pricing-table-{$uid}";
        $cardStyles[] = 'transition: transform 0.2s, box-shadow 0.2s';
    }

    return sprintf(
        '%s<div class="%s" style="%s">%s%s%s%s%s%s</div>',
        $styleBlock,
        e($classes),
        e(implode('; ', $cardStyles)),
        $badgeHtml,
        $planNameHtml,
        $priceHtml,
        $descriptionHtml,
        $featuresHtml,
        $buttonHtml
    );
};
