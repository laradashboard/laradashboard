<?php

/**
 * Countdown Block - Server-side Renderer
 *
 * Email: computes live countdown values at render time.
 * Page: interactive JS countdown.
 */

return function (array $props, string $context = 'page', ?string $blockId = null): string {
    $title = $props['title'] ?? 'Sale Ends In';
    $targetDate = $props['targetDate'] ?? '';
    $targetTime = $props['targetTime'] ?? '23:59';
    $bgColor = $props['backgroundColor'] ?? '#1e293b';
    $textColor = $props['textColor'] ?? '#ffffff';
    $numberColor = $props['numberColor'] ?? '#635bff';
    $align = $props['align'] ?? 'center';
    $expiredMessage = $props['expiredMessage'] ?? '';
    $layoutStyles = $props['layoutStyles'] ?? [];

    if ($context === 'email') {
        // Compute live countdown at render time
        if (empty($targetDate)) {
            $targetDate = date('Y-m-d', strtotime('+7 days'));
        }
        $targetDateTime = "{$targetDate}T{$targetTime}:00";
        $target = strtotime($targetDateTime);
        $now = time();
        $diff = max(0, $target - $now);

        if ($diff <= 0 && $expiredMessage) {
            return sprintf(
                '<div style="padding: 24px; background-color: %s; border-radius: 8px; text-align: %s; font-family: Arial, Helvetica, sans-serif;"><p style="color: %s; font-size: 18px; font-weight: 600; margin: 0;">%s</p></div>',
                e($bgColor),
                e($align),
                e($textColor),
                e($expiredMessage)
            );
        }

        $days = (int) floor($diff / 86400);
        $hours = (int) floor(($diff % 86400) / 3600);
        $minutes = (int) floor(($diff % 3600) / 60);
        $seconds = (int) ($diff % 60);

        $styles = [
            'padding: 24px',
            "background-color: {$bgColor}",
            'border-radius: 8px',
            "text-align: {$align}",
            'font-family: Arial, Helvetica, sans-serif',
        ];

        $layoutCSS = \App\Helpers\EmailStyleHelper::buildLayoutStyles($layoutStyles);
        if ($layoutCSS) {
            $styles[] = $layoutCSS;
        }

        $titleHtml = $title ? sprintf('<p style="color: %s; font-size: 18px; font-weight: 600; margin: 0 0 16px 0;">%s</p>', e($textColor), e($title)) : '';

        $units = [
            ['value' => $days, 'label' => 'Days'],
            ['value' => $hours, 'label' => 'Hours'],
            ['value' => $minutes, 'label' => 'Mins'],
            ['value' => $seconds, 'label' => 'Secs'],
        ];

        $cellsHtml = '';
        foreach ($units as $i => $unit) {
            $separator = $i > 0 ? sprintf('<td style="color: %s; font-size: 28px; font-weight: 700; padding: 0 4px;">:</td>', e($numberColor)) : '';
            $cellsHtml .= $separator . sprintf(
                '<td style="text-align: center; padding: 0 12px;"><div style="background-color: rgba(255,255,255,0.1); border-radius: 8px; padding: 12px 16px;"><span style="color: %s; font-size: 36px; font-weight: 700; display: block;">%s</span><span style="color: %s; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">%s</span></div></td>',
                e($numberColor),
                str_pad((string) $unit['value'], 2, '0', STR_PAD_LEFT),
                e($textColor),
                $unit['label']
            );
        }

        return sprintf(
            '<div style="%s">%s<table width="100%%" cellpadding="0" cellspacing="0" border="0"><tr><td align="center"><table cellpadding="0" cellspacing="0" border="0"><tr>%s</tr></table></td></tr></table></div>',
            e(implode('; ', $styles)),
            $titleHtml,
            $cellsHtml
        );
    }

    // Page context: interactive JS countdown
    $customClass = $props['customClass'] ?? '';
    $blockClasses = 'lb-block lb-countdown';
    if (! empty($customClass)) {
        $blockClasses .= ' ' . e($customClass);
    }

    if (empty($targetDate)) {
        $targetDate = date('Y-m-d', strtotime('+7 days'));
    }
    $targetDateTime = "{$targetDate}T{$targetTime}:00";
    $countdownId = 'countdown-' . ($blockId ?? uniqid());

    $typography = $layoutStyles['typography'] ?? [];
    $pageTextColor = $typography['color'] ?? $textColor;

    $blockStyles = ['padding: 24px', "text-align: {$align}"];
    if (empty($layoutStyles['background']['color'])) {
        $blockStyles[] = "background-color: {$bgColor}";
    }
    if (empty($layoutStyles['border'])) {
        $blockStyles[] = 'border-radius: 8px';
    }

    if (! empty($layoutStyles['margin'])) {
        foreach (['top', 'right', 'bottom', 'left'] as $side) {
            if (isset($layoutStyles['margin'][$side])) {
                $blockStyles[] = "margin-{$side}: {$layoutStyles['margin'][$side]}";
            }
        }
    }
    if (! empty($layoutStyles['padding'])) {
        foreach (['top', 'right', 'bottom', 'left'] as $side) {
            if (isset($layoutStyles['padding'][$side])) {
                $blockStyles[] = "padding-{$side}: {$layoutStyles['padding'][$side]}";
            }
        }
    }
    if (! empty($layoutStyles['background']['color'])) {
        $blockStyles[] = "background-color: {$layoutStyles['background']['color']}";
    }

    $titleHtml = $title ? sprintf('<p style="color: %s; font-size: 18px; font-weight: 600; margin: 0 0 16px 0;">%s</p>', e($pageTextColor), e($title)) : '';

    $unitsHtml = '';
    foreach (['days', 'hours', 'mins', 'secs'] as $unit) {
        $label = ucfirst($unit);
        if ($unit === 'mins') {
            $label = 'Mins';
        }
        if ($unit === 'secs') {
            $label = 'Secs';
        }
        $unitsHtml .= sprintf(
            '<div style="background-color: rgba(255,255,255,0.1); border-radius: 8px; padding: 12px 16px; min-width: 60px;"><span class="lb-countdown-%s" style="color: %s; font-size: 36px; font-weight: 700; display: block;">00</span><span style="color: %s; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">%s</span></div>',
            $unit,
            e($numberColor),
            e($pageTextColor),
            $label
        );
    }

    return sprintf(
        '<div class="%s" id="%s" data-target="%s" data-expired-message="%s" style="%s">%s<div style="display: flex; justify-content: center; gap: 16px; flex-wrap: wrap;">%s</div></div><script>(function(){var e=document.getElementById("%s");if(!e)return;var t=new Date(e.dataset.target),m=e.dataset.expiredMessage;function u(){var n=new Date(),d=Math.max(0,t-n);if(d<=0&&m){e.innerHTML=\'<p style="color: #ffffff; font-size: 18px; font-weight: 600; margin: 0;">\'+m+\'</p>\';return}e.querySelector(".lb-countdown-days").textContent=String(Math.floor(d/864e5)).padStart(2,"0");e.querySelector(".lb-countdown-hours").textContent=String(Math.floor(d/36e5%%24)).padStart(2,"0");e.querySelector(".lb-countdown-mins").textContent=String(Math.floor(d/6e4%%60)).padStart(2,"0");e.querySelector(".lb-countdown-secs").textContent=String(Math.floor(d/1e3%%60)).padStart(2,"0")}u();setInterval(u,1e3)})();</script>',
        e($blockClasses),
        e($countdownId),
        e($targetDateTime),
        e($expiredMessage),
        e(implode('; ', $blockStyles)),
        $titleHtml,
        $unitsHtml,
        e($countdownId)
    );
};
