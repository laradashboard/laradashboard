<?php

/**
 * Steps Block - Server-side Renderer
 *
 * Page: renders numbered steps with title, description, optional code block and link.
 * Supports vertical and horizontal layouts with connector lines.
 */

return function (array $props, string $context = 'page', ?string $blockId = null): string {
    $steps = $props['steps'] ?? [
        ['title' => 'Step 1', 'description' => 'Description', 'code' => '', 'linkText' => '', 'linkUrl' => ''],
    ];
    $layout = $props['layout'] ?? 'vertical';
    $showNumbers = (bool) ($props['showNumbers'] ?? true);
    $showConnector = (bool) ($props['showConnector'] ?? true);
    $numberColor = $props['numberColor'] ?? '#ffffff';
    $numberBgColor = $props['numberBgColor'] ?? '#3b82f6';
    $titleColor = $props['titleColor'] ?? '#111827';
    $titleSize = $props['titleSize'] ?? '20px';
    $descriptionColor = $props['descriptionColor'] ?? '#6b7280';
    $descriptionSize = $props['descriptionSize'] ?? '14px';
    $connectorColor = $props['connectorColor'] ?? '#e5e7eb';
    $codeBackgroundColor = $props['codeBackgroundColor'] ?? '#1f2937';
    $codeTextColor = $props['codeTextColor'] ?? '#e5e7eb';
    $customClass = $props['customClass'] ?? '';

    $blockClasses = trim("lb-block lb-steps {$customClass}");
    $circleSize = 40;
    $totalSteps = count($steps);

    if ($layout === 'horizontal') {
        $stepsHtml = '';
        foreach ($steps as $index => $step) {
            $isLast = $index === $totalSteps - 1;
            $title = e($step['title'] ?? '');
            $description = e($step['description'] ?? '');

            // Number circle with connectors
            $leftConnector = '';
            if ($index > 0 && $showConnector) {
                $leftConnector = sprintf('<div style="flex: 1; height: 3px; background-color: %s;"></div>', e($connectorColor));
            } else {
                $leftConnector = '<div style="flex: 1;"></div>';
            }

            $rightConnector = '';
            if (! $isLast && $showConnector) {
                $rightConnector = sprintf('<div style="flex: 1; height: 3px; background-color: %s;"></div>', e($connectorColor));
            } else {
                $rightConnector = '<div style="flex: 1;"></div>';
            }

            $circleHtml = '';
            if ($showNumbers) {
                $circleHtml = sprintf(
                    '<div style="width: %dpx; height: %dpx; border-radius: 50%%; background-color: %s; color: %s; display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: 700; flex-shrink: 0;">%d</div>',
                    $circleSize,
                    $circleSize,
                    e($numberBgColor),
                    e($numberColor),
                    $index + 1
                );
            }

            $stepsHtml .= sprintf(
                '<div style="flex: 1; display: flex; flex-direction: column; align-items: center; position: relative;"><div style="display: flex; align-items: center; width: 100%%; margin-bottom: 16px;">%s%s%s</div><div style="text-align: center; padding: 0 8px;"><h4 style="font-size: %s; font-weight: 600; color: %s; margin: 0 0 8px 0;">%s</h4><p style="font-size: %s; color: %s; margin: 0; line-height: 1.5;">%s</p></div></div>',
                $leftConnector,
                $circleHtml,
                $rightConnector,
                e($titleSize),
                e($titleColor),
                $title,
                e($descriptionSize),
                e($descriptionColor),
                $description
            );
        }

        return sprintf(
            '<div class="%s"><div style="display: flex; align-items: flex-start; gap: 0;">%s</div></div>',
            e($blockClasses),
            $stepsHtml
        );
    }

    // Vertical layout (default)
    $stepsHtml = '';
    foreach ($steps as $index => $step) {
        $isLast = $index === $totalSteps - 1;
        $title = e($step['title'] ?? '');
        $description = e($step['description'] ?? '');
        $code = $step['code'] ?? '';
        $linkText = $step['linkText'] ?? '';
        $linkUrl = $step['linkUrl'] ?? '';

        // Number circle
        $circleHtml = '';
        if ($showNumbers) {
            $circleHtml = sprintf(
                '<div style="width: %dpx; height: %dpx; border-radius: 50%%; background-color: %s; color: %s; display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: 700; flex-shrink: 0; z-index: 1;">%d</div>',
                $circleSize,
                $circleSize,
                e($numberBgColor),
                e($numberColor),
                $index + 1
            );
        }

        // Connector line
        $connectorHtml = '';
        if (! $isLast && $showConnector) {
            $connectorHtml = sprintf(
                '<div style="width: 3px; flex: 1; background-color: %s; margin-top: 4px; margin-bottom: 4px;"></div>',
                e($connectorColor)
            );
        }

        // Code block
        $codeHtml = '';
        if (! empty($code)) {
            $codeHtml = sprintf(
                '<pre style="background-color: %s; color: %s; padding: 12px 16px; border-radius: 8px; font-size: 13px; font-family: ui-monospace, SFMono-Regular, \'SF Mono\', Menlo, Consolas, monospace; margin: 0 0 12px 0; overflow: auto; white-space: pre-wrap; word-break: break-all;"><code>%s</code></pre>',
                e($codeBackgroundColor),
                e($codeTextColor),
                e($code)
            );
        }

        // Link
        $linkHtml = '';
        if (! empty($linkText) && ! empty($linkUrl)) {
            $linkHtml = sprintf(
                '<a href="%s" style="display: inline-flex; align-items: center; gap: 4px; font-size: 14px; font-weight: 500; color: %s; text-decoration: none;">%s <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></a>',
                e($linkUrl),
                e($numberBgColor),
                e($linkText)
            );
        }

        $paddingBottom = $isLast ? '0' : '32px';

        $stepsHtml .= sprintf(
            '<div style="display: flex; gap: 20px; position: relative; min-height: %s;"><div style="display: flex; flex-direction: column; align-items: center; flex-shrink: 0; width: %dpx;">%s%s</div><div style="flex: 1; padding-bottom: %s;"><h4 style="font-size: %s; font-weight: 600; color: %s; margin: 0 0 8px 0; line-height: %dpx;">%s</h4><p style="font-size: %s; color: %s; margin: 0 0 12px 0; line-height: 1.6;">%s</p>%s%s</div></div>',
            $isLast ? 'auto' : '100px',
            $circleSize,
            $circleHtml,
            $connectorHtml,
            $paddingBottom,
            e($titleSize),
            e($titleColor),
            $circleSize,
            $title,
            e($descriptionSize),
            e($descriptionColor),
            $description,
            $codeHtml,
            $linkHtml
        );
    }

    return sprintf(
        '<div class="%s" style="display: flex; flex-direction: column; gap: 0;">%s</div>',
        e($blockClasses),
        $stepsHtml
    );
};
