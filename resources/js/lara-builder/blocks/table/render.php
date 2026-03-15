<?php

/**
 * Table Block - Server-side Renderer
 */

return function (array $props, string $context = 'page', ?string $blockId = null): string {
    $headers = $props['headers'] ?? [];
    $rows = $props['rows'] ?? [];
    $showHeader = $props['showHeader'] ?? true;
    $headerBgColor = $props['headerBgColor'] ?? '#f1f5f9';
    $headerTextColor = $props['headerTextColor'] ?? '#1e293b';
    $borderColor = $props['borderColor'] ?? '#e2e8f0';
    $cellPadding = $props['cellPadding'] ?? '12px';
    $fontSize = $props['fontSize'] ?? '14px';
    $layoutStyles = $props['layoutStyles'] ?? [];

    $typography = $layoutStyles['typography'] ?? [];
    $textColor = $typography['color'] ?? '#374151';
    $tableFontSize = $typography['fontSize'] ?? $fontSize;

    if ($context === 'email') {
        $headerHtml = '';
        if ($showHeader && ! empty($headers)) {
            $cells = '';
            foreach ($headers as $header) {
                $cells .= sprintf(
                    '<th style="background-color: %s; color: %s; padding: %s; text-align: left; font-weight: 600; border: 1px solid %s;">%s</th>',
                    e($headerBgColor),
                    e($headerTextColor),
                    e($cellPadding),
                    e($borderColor),
                    e($header)
                );
            }
            $headerHtml = "<thead><tr>{$cells}</tr></thead>";
        }

        $bodyHtml = '';
        foreach ($rows as $row) {
            $cells = '';
            foreach ($row as $cell) {
                $cells .= sprintf(
                    '<td style="padding: %s; border: 1px solid %s; color: %s;">%s</td>',
                    e($cellPadding),
                    e($borderColor),
                    e($textColor),
                    e($cell)
                );
            }
            $bodyHtml .= "<tr>{$cells}</tr>";
        }

        $styles = [
            "font-size: {$tableFontSize}",
            'border-collapse: collapse',
            'margin: 10px 0',
            'font-family: Arial, Helvetica, sans-serif',
        ];

        $layoutCSS = \App\Helpers\EmailStyleHelper::buildLayoutStyles($layoutStyles);
        if ($layoutCSS) {
            $styles[] = $layoutCSS;
        }

        return sprintf(
            '<table role="presentation" width="100%%" cellpadding="0" cellspacing="0" style="%s">%s<tbody>%s</tbody></table>',
            e(implode('; ', $styles)),
            $headerHtml,
            $bodyHtml
        );
    }

    // Page context
    $customClass = $props['customClass'] ?? '';
    $blockClasses = 'lb-block lb-table';
    if (! empty($customClass)) {
        $blockClasses .= ' ' . e($customClass);
    }

    $headerHtml = '';
    if ($showHeader && ! empty($headers)) {
        $cells = '';
        foreach ($headers as $header) {
            $cells .= sprintf(
                '<th style="background-color: %s; color: %s; padding: %s; text-align: left; font-weight: 600; border-bottom: 2px solid %s;">%s</th>',
                e($headerBgColor),
                e($headerTextColor),
                e($cellPadding),
                e($borderColor),
                e($header)
            );
        }
        $headerHtml = "<thead><tr>{$cells}</tr></thead>";
    }

    $bodyHtml = '';
    foreach ($rows as $row) {
        $cells = '';
        foreach ($row as $cell) {
            $cells .= sprintf(
                '<td style="padding: %s; border-bottom: 1px solid %s; color: %s;">%s</td>',
                e($cellPadding),
                e($borderColor),
                e($textColor),
                e($cell)
            );
        }
        $bodyHtml .= "<tr>{$cells}</tr>";
    }

    return sprintf(
        '<div class="%s" style="overflow-x: auto"><table class="lb-table-inner" style="width: 100%%; font-size: %s; border-collapse: collapse;">%s<tbody>%s</tbody></table></div>',
        e($blockClasses),
        e($tableFontSize),
        $headerHtml,
        $bodyHtml
    );
};
