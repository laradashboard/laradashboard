<?php

/**
 * Spacer Block - Server-side Renderer
 */

return function (array $props, string $context = 'page', ?string $blockId = null): string {
    $height = $props['height'] ?? '20px';

    if ($context === 'email') {
        return sprintf(
            '<table width="100%%" cellpadding="0" cellspacing="0" border="0" role="presentation"><tr><td style="height: %s; line-height: %s; font-size: 1px;">&nbsp;</td></tr></table>',
            e($height),
            e($height)
        );
    }

    // Page context
    $customClass = $props['customClass'] ?? '';
    $blockClasses = 'lb-block lb-spacer';
    if (! empty($customClass)) {
        $blockClasses .= ' ' . e($customClass);
    }

    return sprintf(
        '<div class="%s" style="height: %s;"></div>',
        e($blockClasses),
        e($height)
    );
};
