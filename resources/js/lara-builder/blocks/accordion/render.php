<?php

/**
 * Accordion Block - Server-side Renderer
 *
 * Email: renders as expanded static content (no JS in email).
 * Page: renders with interactive JS accordion behavior.
 */

return function (array $props, string $context = 'page', ?string $blockId = null): string {
    $items = $props['items'] ?? [['title' => 'Accordion Item', 'content' => 'Content goes here...']];
    $borderColor = $props['borderColor'] ?? '#e5e7eb';
    $borderRadius = $props['borderRadius'] ?? '8px';
    $headerBgColor = $props['headerBgColor'] ?? '#ffffff';
    $headerPadding = $props['headerPadding'] ?? '16px';
    $titleColor = $props['titleColor'] ?? '#1f2937';
    $titleFontSize = $props['titleFontSize'] ?? '16px';
    $titleFontWeight = $props['titleFontWeight'] ?? '600';
    $contentBgColor = $props['contentBgColor'] ?? '#ffffff';
    $contentColor = $props['contentColor'] ?? '#4b5563';
    $contentFontSize = $props['contentFontSize'] ?? '14px';
    $contentPadding = $props['contentPadding'] ?? '16px';
    $iconColor = $props['iconColor'] ?? '#6b7280';
    $layoutStyles = $props['layoutStyles'] ?? [];

    // Use layoutStyles typography if present
    $typography = $layoutStyles['typography'] ?? [];
    $tColor = $typography['color'] ?? $titleColor;
    $tFontSize = $typography['fontSize'] ?? $titleFontSize;
    $tFontWeight = $typography['fontWeight'] ?? $titleFontWeight;

    if ($context === 'email') {
        $itemsHtml = '';
        $lastIdx = count($items) - 1;
        foreach ($items as $index => $item) {
            $isLast = $index === $lastIdx;
            $itemsHtml .= sprintf(
                '<div style="border-bottom: %s;"><div style="display: flex; align-items: center; justify-content: space-between; padding: %s; background-color: %s;"><span style="font-weight: %s; font-size: %s; color: %s;">%s</span><span style="color: %s; font-size: 12px;">&#9660;</span></div><div style="padding: %s; background-color: %s; color: %s; font-size: %s; line-height: 1.6;">%s</div></div>',
                $isLast ? 'none' : "1px solid {$borderColor}",
                e($headerPadding),
                e($headerBgColor),
                e($tFontWeight),
                e($tFontSize),
                e($tColor),
                e($item['title'] ?? ''),
                e($iconColor),
                e($contentPadding),
                e($contentBgColor),
                e($contentColor),
                e($contentFontSize),
                $item['content'] ?? ''
            );
        }

        $styles = [
            "border: 1px solid {$borderColor}",
            "border-radius: {$borderRadius}",
            'overflow: hidden',
            'font-family: Arial, Helvetica, sans-serif',
        ];

        $layoutCSS = \App\Helpers\EmailStyleHelper::buildLayoutStyles($layoutStyles);
        if ($layoutCSS) {
            $styles[] = $layoutCSS;
        }

        return sprintf('<div style="%s">%s</div>', e(implode('; ', $styles)), $itemsHtml);
    }

    // Page context: interactive accordion with JS
    $customClass = $props['customClass'] ?? '';
    $blockClasses = 'lb-block lb-accordion';
    if (! empty($customClass)) {
        $blockClasses .= ' ' . e($customClass);
    }

    $headerBgColorActive = $props['headerBgColorActive'] ?? '#f9fafb';
    $iconPosition = $props['iconPosition'] ?? 'right';
    $transitionDuration = $props['transitionDuration'] ?? 200;
    $independentToggle = $props['independentToggle'] ?? false;
    $itemGap = $props['itemGap'] ?? '12px';
    $defaultOpenIndex = $props['defaultOpenIndex'] ?? 0;

    $accordionId = 'accordion-' . ($blockId ?? uniqid());

    $itemsHtml = '';
    foreach ($items as $index => $item) {
        $itemId = "{$accordionId}-item-{$index}";
        $flexDir = $iconPosition === 'left' ? 'row-reverse' : 'row';

        $itemStyles = [
            "border: 1px solid {$borderColor}",
            "border-radius: {$borderRadius}",
            'overflow: hidden',
        ];

        $itemsHtml .= sprintf(
            '<div class="lb-accordion-item" data-index="%d" style="%s"><button type="button" class="lb-accordion-header" data-target="%s" style="display: flex; align-items: center; justify-content: space-between; width: 100%%; padding: %s; background-color: %s; border: none; cursor: pointer; text-align: left; transition: background-color 0.2s; flex-direction: %s;"><span style="font-weight: %s; font-size: %s; color: %s; flex: 1;">%s</span><span class="lb-accordion-icon" style="color: %s; transition: transform %dms ease; %s"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg></span></button><div id="%s" class="lb-accordion-content" style="max-height: 0; overflow: hidden; transition: max-height %dms ease-in-out;"><div style="padding: %s; background-color: %s; color: %s; font-size: %s; line-height: 1.6; border-top: 1px solid %s;">%s</div></div></div>',
            $index,
            e(implode('; ', $itemStyles)),
            e($itemId),
            e($headerPadding),
            e($headerBgColor),
            $flexDir,
            e($tFontWeight),
            e($tFontSize),
            e($tColor),
            e($item['title'] ?? ''),
            e($iconColor),
            $transitionDuration,
            $iconPosition === 'left' ? 'margin-right: 12px;' : 'margin-left: 12px;',
            e($itemId),
            $transitionDuration,
            e($contentPadding),
            e($contentBgColor),
            e($contentColor),
            e($contentFontSize),
            e($borderColor),
            $item['content'] ?? ''
        );
    }

    $blockStyles = [
        'display: flex',
        'flex-direction: column',
        "gap: {$itemGap}",
    ];

    return sprintf(
        '<div class="%s" id="%s" data-independent="%s" data-default-open="%d" style="%s">%s</div><script>(function(){var a=document.getElementById("%s");if(!a)return;var i=a.dataset.independent==="true";var d=parseInt(a.dataset.defaultOpen)||0;a.querySelectorAll(".lb-accordion-header").forEach(function(h){h.addEventListener("click",function(){var t=this.dataset.target,c=document.getElementById(t),ic=this.querySelector(".lb-accordion-icon"),o=c.style.maxHeight&&c.style.maxHeight!=="0px";if(!i){a.querySelectorAll(".lb-accordion-content").forEach(function(x){x.style.maxHeight="0px"});a.querySelectorAll(".lb-accordion-icon").forEach(function(x){x.style.transform="rotate(0deg)"});a.querySelectorAll(".lb-accordion-header").forEach(function(x){x.style.backgroundColor="%s"})}if(o){c.style.maxHeight="0px";ic.style.transform="rotate(0deg)";this.style.backgroundColor="%s"}else{c.style.maxHeight=c.scrollHeight+"px";ic.style.transform="rotate(180deg)";this.style.backgroundColor="%s"}})});var headers=a.querySelectorAll(".lb-accordion-header");if(headers[d])headers[d].click()})();</script>',
        e($blockClasses),
        e($accordionId),
        $independentToggle ? 'true' : 'false',
        (int) $defaultOpenIndex,
        e(implode('; ', $blockStyles)),
        $itemsHtml,
        e($accordionId),
        $headerBgColor,
        $headerBgColor,
        $headerBgColorActive
    );
};
