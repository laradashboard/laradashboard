<?php

/**
 * HTML Block - Server-side Renderer
 *
 * Raw HTML passthrough.
 */

return function (array $props, string $context = 'page', ?string $blockId = null): string {
    $code = $props['code'] ?? '';

    if ($context === 'email') {
        // Raw HTML passthrough for email
        return $code;
    }

    // Page context
    $customClass = $props['customClass'] ?? '';
    $blockClasses = 'lb-block lb-html';
    if (! empty($customClass)) {
        $blockClasses .= ' ' . e($customClass);
    }

    $layoutStyles = $props['layoutStyles'] ?? [];
    $styles = [];

    if (! empty($layoutStyles['margin'])) {
        foreach (['top', 'right', 'bottom', 'left'] as $side) {
            if (isset($layoutStyles['margin'][$side])) {
                $styles[] = "margin-{$side}: {$layoutStyles['margin'][$side]}";
            }
        }
    }
    if (! empty($layoutStyles['padding'])) {
        foreach (['top', 'right', 'bottom', 'left'] as $side) {
            if (isset($layoutStyles['padding'][$side])) {
                $styles[] = "padding-{$side}: {$layoutStyles['padding'][$side]}";
            }
        }
    }
    if (! empty($layoutStyles['background']['color'])) {
        $styles[] = "background-color: {$layoutStyles['background']['color']}";
    }

    $styleAttr = ! empty($styles) ? sprintf(' style="%s"', e(implode('; ', $styles))) : '';

    return sprintf('<div class="%s"%s>%s</div>', e($blockClasses), $styleAttr, $code);
};
