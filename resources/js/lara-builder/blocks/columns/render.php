<?php

/**
 * Columns Block - Server-side Renderer
 *
 * Email: table-based columns with td per column.
 * Page: flex-based columns.
 *
 * Uses DesignJsonRenderer for recursive child rendering.
 */

return function (array $props, string $context = 'page', ?string $blockId = null): string {
    $columns = $props['columns'] ?? 2;
    $gap = $props['gap'] ?? '20px';
    $verticalAlign = $props['verticalAlign'] ?? 'stretch';
    $horizontalAlign = $props['horizontalAlign'] ?? 'stretch';
    $children = $props['children'] ?? [];
    $layoutStyles = $props['layoutStyles'] ?? [];

    $renderer = app(\App\Services\Builder\DesignJsonRenderer::class);

    if ($context === 'email') {
        $emailVerticalAlign = [
            'start' => 'top',
            'center' => 'middle',
            'end' => 'bottom',
            'stretch' => 'top',
        ];

        $columnWidth = (int) (100 / $columns) . '%';
        $valign = $emailVerticalAlign[$verticalAlign] ?? 'top';

        $columnsHtml = '';
        foreach ($children as $index => $columnBlocks) {
            $columnContent = '';
            if (is_array($columnBlocks)) {
                foreach ($columnBlocks as $block) {
                    $columnContent .= $renderer->renderBlock($block, $context);
                }
            }

            $paddingRight = $index < $columns - 1 ? $gap : '0';
            $columnsHtml .= sprintf(
                '<td style="width: %s; vertical-align: %s; padding: 0 %s 0 0;">%s</td>',
                e($columnWidth),
                e($valign),
                e($paddingRight),
                $columnContent ?: '&nbsp;'
            );
        }

        $styles = [];
        $layoutCSS = \App\Helpers\EmailStyleHelper::buildLayoutStyles($layoutStyles);
        if ($layoutCSS) {
            $styles[] = $layoutCSS;
        }

        $tableStyle = ! empty($styles) ? sprintf(' style="%s"', e(implode('; ', $styles))) : '';

        return sprintf(
            '<table width="100%%" cellpadding="0" cellspacing="0" border="0"%s><tr>%s</tr></table>',
            $tableStyle,
            $columnsHtml
        );
    }

    // Page context: flex-based columns
    $customClass = $props['customClass'] ?? '';
    $blockClasses = "lb-block lb-columns lb-columns-{$columns}";
    if (! empty($customClass)) {
        $blockClasses .= ' ' . e($customClass);
    }

    $alignItemsMap = [
        'start' => 'flex-start',
        'center' => 'center',
        'end' => 'flex-end',
        'stretch' => 'stretch',
    ];

    $justifyContentMap = [
        'start' => 'flex-start',
        'center' => 'center',
        'end' => 'flex-end',
        'stretch' => 'stretch',
        'space-between' => 'space-between',
        'space-around' => 'space-around',
    ];

    $alignItems = $alignItemsMap[$verticalAlign] ?? 'stretch';
    $justifyContent = $justifyContentMap[$horizontalAlign] ?? 'stretch';

    $containerStyles = [
        'display: flex',
        'flex-wrap: wrap',
        "gap: {$gap}",
        "align-items: {$alignItems}",
        "justify-content: {$justifyContent}",
    ];

    if (! empty($layoutStyles['margin'])) {
        foreach (['top', 'right', 'bottom', 'left'] as $side) {
            if (isset($layoutStyles['margin'][$side])) {
                $containerStyles[] = "margin-{$side}: {$layoutStyles['margin'][$side]}";
            }
        }
    }
    if (! empty($layoutStyles['padding'])) {
        foreach (['top', 'right', 'bottom', 'left'] as $side) {
            if (isset($layoutStyles['padding'][$side])) {
                $containerStyles[] = "padding-{$side}: {$layoutStyles['padding'][$side]}";
            }
        }
    }
    if (! empty($layoutStyles['background']['color'])) {
        $containerStyles[] = "background-color: {$layoutStyles['background']['color']}";
    }

    $columnWidthCSS = $horizontalAlign === 'stretch'
        ? "flex: 1 1 calc(" . (100 / $columns) . "% - {$gap})"
        : "flex: 0 0 auto; width: calc(" . (100 / $columns) . "% - {$gap})";

    $columnsHtml = '';
    foreach ($children as $columnBlocks) {
        $columnContent = '';
        if (is_array($columnBlocks)) {
            foreach ($columnBlocks as $block) {
                $columnContent .= $renderer->renderBlock($block, $context);
            }
        }
        $columnsHtml .= sprintf(
            '<div class="lb-column" style="%s; min-width: 0;">%s</div>',
            $columnWidthCSS,
            $columnContent
        );
    }

    $stackClass = ($props['stackOnMobile'] ?? true) ? ' lb-columns-stack-mobile' : '';

    return sprintf(
        '<div class="%s%s" style="%s">%s</div>',
        e($blockClasses),
        $stackClass,
        e(implode('; ', $containerStyles)),
        $columnsHtml
    );
};
