<?php

declare(strict_types=1);

namespace App\Services\Builder;

use Illuminate\Support\Facades\Log;

/**
 * Design JSON Renderer
 *
 * Server-side renderer for design_json blocks.
 * Converts page builder blocks stored in design_json to HTML.
 * Uses render.php files from block folders when available.
 */
class DesignJsonRenderer
{
    /**
     * Cache for discovered render callbacks
     */
    protected array $discoveredCallbacks = [];

    /**
     * Gradient direction mapping
     */
    protected array $gradientDirectionMap = [
        'to-t' => 'to top',
        'to-tr' => 'to top right',
        'to-r' => 'to right',
        'to-br' => 'to bottom right',
        'to-b' => 'to bottom',
        'to-bl' => 'to bottom left',
        'to-l' => 'to left',
        'to-tl' => 'to top left',
    ];

    /**
     * Render design_json blocks to HTML
     *
     * @param  array  $blocks  The blocks from design_json
     * @param  string  $context  The rendering context ('page', 'email')
     * @return string The generated HTML
     */
    public function render(array $blocks, string $context = 'page'): string
    {
        $html = '';

        foreach ($blocks as $block) {
            $html .= $this->renderBlock($block, $context);
        }

        return '<div class="lb-content lb-page-content">' . $html . '</div>';
    }

    /**
     * Render a single block to HTML
     */
    public function renderBlock(array $block, string $context = 'page'): string
    {
        $type = $block['type'] ?? '';
        $props = $block['props'] ?? [];
        $blockId = $block['id'] ?? null;

        if (empty($type)) {
            return '';
        }

        try {
            // Try render.php first
            $callback = $this->getBlockRenderCallback($type);

            if ($callback) {
                return $callback($props, $context, $blockId);
            }

            // Fall back to built-in renderers
            return match ($type) {
                'section' => $this->renderSection($props, $context),
                'heading' => $this->renderHeading($props),
                'text' => $this->renderText($props),
                'columns' => $this->renderColumns($props, $context),
                'icon' => $this->renderIcon($props),
                'stats-item' => $this->renderStatsItem($props),
                'feature-box' => $this->renderFeatureBox($props),
                'button' => $this->renderButton($props),
                'image' => $this->renderImage($props),
                'spacer' => $this->renderSpacer($props),
                'divider' => $this->renderDivider($props),
                default => '',
            };
        } catch (\Throwable $e) {
            Log::warning('Failed to render design_json block', [
                'block_type' => $type,
                'context' => $context,
                'error' => $e->getMessage(),
            ]);

            return '';
        }
    }

    /**
     * Get the render callback for a block type
     */
    protected function getBlockRenderCallback(string $blockType): ?callable
    {
        if (array_key_exists($blockType, $this->discoveredCallbacks)) {
            return $this->discoveredCallbacks[$blockType];
        }

        $renderPath = resource_path("js/lara-builder/blocks/{$blockType}/render.php");

        if (file_exists($renderPath)) {
            $callback = require $renderPath;
            if (is_callable($callback)) {
                $this->discoveredCallbacks[$blockType] = $callback;

                return $callback;
            }
        }

        $this->discoveredCallbacks[$blockType] = null;

        return null;
    }

    /**
     * Render section block
     */
    protected function renderSection(array $props, string $context): string
    {
        $fullWidth = $props['fullWidth'] ?? true;
        $containerMaxWidth = $props['containerMaxWidth'] ?? '1280px';
        $contentAlign = $props['contentAlign'] ?? 'center';
        $backgroundType = $props['backgroundType'] ?? 'solid';
        $backgroundColor = $props['backgroundColor'] ?? '#ffffff';
        $gradientFrom = $props['gradientFrom'] ?? '#f9fafb';
        $gradientTo = $props['gradientTo'] ?? '#f3f4f6';
        $gradientDirection = $props['gradientDirection'] ?? 'to-br';
        $children = $props['children'] ?? [];
        $layoutStyles = $props['layoutStyles'] ?? [];
        $customClass = $props['customClass'] ?? '';

        // Build background style
        if ($backgroundType === 'gradient') {
            $direction = $this->gradientDirectionMap[$gradientDirection] ?? 'to bottom right';
            $backgroundStyle = "background: linear-gradient({$direction}, {$gradientFrom}, {$gradientTo})";
        } else {
            $backgroundStyle = "background-color: {$backgroundColor}";
        }

        // Container alignment
        $containerMargin = match ($contentAlign) {
            'left' => 'margin-left: 0',
            'right' => 'margin-right: 0',
            default => 'margin: 0 auto',
        };

        // Build padding from layoutStyles
        $padding = $this->buildPadding($layoutStyles['padding'] ?? []);

        // Build section styles
        $sectionStyles = array_filter([
            $backgroundStyle,
            $padding ?: 'padding: 48px 16px',
        ]);

        // Container styles
        $containerStyles = $fullWidth
            ? "max-width: {$containerMaxWidth}; {$containerMargin}; width: 100%"
            : 'width: 100%';

        // Render children - section uses wrapped structure: [[block1, block2, ...]]
        $childrenHtml = '';
        $childBlocks = $children[0] ?? [];
        foreach ($childBlocks as $child) {
            $childrenHtml .= $this->renderBlock($child, $context);
        }

        $classes = trim("lb-block lb-section {$customClass}");

        return sprintf(
            '<section class="%s" style="%s"><div class="lb-section-container" style="%s">%s</div></section>',
            htmlspecialchars($classes),
            htmlspecialchars(implode('; ', $sectionStyles)),
            htmlspecialchars($containerStyles),
            $childrenHtml
        );
    }

    /**
     * Render heading block
     */
    protected function renderHeading(array $props): string
    {
        $text = $props['text'] ?? '';
        $level = $props['level'] ?? 'h1';
        $align = $props['align'] ?? 'left';
        $color = $props['color'] ?? '#111827';
        $fontSize = $props['fontSize'] ?? '24px';
        $fontWeight = $props['fontWeight'] ?? 'bold';

        $style = "text-align: {$align}; color: {$color}; font-size: {$fontSize}; font-weight: {$fontWeight}; margin: 0 0 16px 0;";

        return sprintf(
            '<%s class="lb-block lb-heading" style="%s">%s</%s>',
            $level,
            htmlspecialchars($style),
            htmlspecialchars($text),
            $level
        );
    }

    /**
     * Render text block
     */
    protected function renderText(array $props): string
    {
        $text = $props['text'] ?? $props['content'] ?? '';
        $align = $props['align'] ?? 'left';
        $color = $props['color'] ?? '#6b7280';
        $fontSize = $props['fontSize'] ?? '16px';
        $layoutStyles = $props['layoutStyles'] ?? [];

        $styles = [
            "text-align: {$align}",
            "color: {$color}",
            "font-size: {$fontSize}",
            'line-height: 1.6',
            'margin: 0 0 16px 0',
        ];

        // Add margin from layoutStyles
        if (! empty($layoutStyles['margin'])) {
            $margin = $this->buildMargin($layoutStyles['margin']);
            if ($margin) {
                $styles[] = $margin;
            }
        }

        // Add max-width from layoutStyles
        if (! empty($layoutStyles['maxWidth'])) {
            $styles[] = "max-width: {$layoutStyles['maxWidth']}";
        }

        return sprintf(
            '<div class="lb-block lb-text" style="%s">%s</div>',
            htmlspecialchars(implode('; ', $styles)),
            htmlspecialchars($text)
        );
    }

    /**
     * Render columns block
     */
    protected function renderColumns(array $props, string $context): string
    {
        $columns = $props['columns'] ?? 2;
        $gap = $props['gap'] ?? '24px';
        $verticalAlign = $props['verticalAlign'] ?? 'start';
        $children = $props['children'] ?? [];

        $alignMap = [
            'start' => 'flex-start',
            'center' => 'center',
            'end' => 'flex-end',
            'stretch' => 'stretch',
        ];

        $flexAlign = $alignMap[$verticalAlign] ?? 'flex-start';

        $containerStyle = "display: flex; flex-wrap: wrap; gap: {$gap}; align-items: {$flexAlign};";
        $columnWidth = match ($columns) {
            1 => '100%',
            2 => 'calc(50% - ' . ((float) $gap / 2) . 'px)',
            3 => 'calc(33.333% - ' . ((float) $gap * 2 / 3) . 'px)',
            4 => 'calc(25% - ' . ((float) $gap * 3 / 4) . 'px)',
            default => 'calc(50% - ' . ((float) $gap / 2) . 'px)',
        };

        $columnsHtml = '';
        foreach ($children as $column) {
            $columnContent = '';
            if (is_array($column)) {
                foreach ($column as $block) {
                    $columnContent .= $this->renderBlock($block, $context);
                }
            }
            $columnsHtml .= sprintf(
                '<div class="lb-column" style="flex: 1 1 %s; min-width: 250px;">%s</div>',
                $columnWidth,
                $columnContent
            );
        }

        return sprintf(
            '<div class="lb-block lb-columns" style="%s">%s</div>',
            htmlspecialchars($containerStyle),
            $columnsHtml
        );
    }

    /**
     * Render icon block
     */
    protected function renderIcon(array $props): string
    {
        $icon = $props['icon'] ?? 'lucide:star';
        $size = $props['size'] ?? '32px';
        $color = $props['color'] ?? '#3b82f6';
        $align = $props['align'] ?? 'center';
        $backgroundColor = $props['backgroundColor'] ?? '';
        $backgroundShape = $props['backgroundShape'] ?? 'none';
        $backgroundPadding = $props['backgroundPadding'] ?? '16px';

        $alignMap = [
            'left' => 'flex-start',
            'center' => 'center',
            'right' => 'flex-end',
        ];

        $flexAlign = $alignMap[$align] ?? 'center';

        $iconHtml = sprintf(
            '<iconify-icon icon="%s" width="%s" height="%s" style="color: %s;"></iconify-icon>',
            htmlspecialchars($icon),
            htmlspecialchars($size),
            htmlspecialchars($size),
            htmlspecialchars($color)
        );

        if ($backgroundColor && $backgroundShape !== 'none') {
            $borderRadius = match ($backgroundShape) {
                'circle' => '50%',
                'rounded' => '12px',
                'square' => '0',
                default => '0',
            };

            $iconHtml = sprintf(
                '<div style="background-color: %s; padding: %s; border-radius: %s; display: inline-flex; align-items: center; justify-content: center;">%s</div>',
                htmlspecialchars($backgroundColor),
                htmlspecialchars($backgroundPadding),
                $borderRadius,
                $iconHtml
            );
        }

        return sprintf(
            '<div class="lb-block lb-icon" style="display: flex; justify-content: %s;">%s</div>',
            $flexAlign,
            $iconHtml
        );
    }

    /**
     * Render stats-item block
     */
    protected function renderStatsItem(array $props): string
    {
        $value = $props['value'] ?? '0';
        $suffix = $props['suffix'] ?? '';
        $prefix = $props['prefix'] ?? '';
        $label = $props['label'] ?? '';
        $valueColor = $props['valueColor'] ?? '#3b82f6';
        $valueSize = $props['valueSize'] ?? '48px';
        $labelColor = $props['labelColor'] ?? '#6b7280';
        $labelSize = $props['labelSize'] ?? '14px';
        $align = $props['align'] ?? 'center';

        $alignMap = [
            'left' => 'flex-start',
            'center' => 'center',
            'right' => 'flex-end',
        ];

        $flexAlign = $alignMap[$align] ?? 'center';
        $textAlign = $align;

        $valueHtml = sprintf(
            '<div style="color: %s; font-size: %s; font-weight: bold; line-height: 1;">%s%s%s</div>',
            htmlspecialchars($valueColor),
            htmlspecialchars($valueSize),
            htmlspecialchars($prefix),
            htmlspecialchars($value),
            htmlspecialchars($suffix)
        );

        $labelHtml = $label ? sprintf(
            '<div style="color: %s; font-size: %s; margin-top: 8px;">%s</div>',
            htmlspecialchars($labelColor),
            htmlspecialchars($labelSize),
            htmlspecialchars($label)
        ) : '';

        return sprintf(
            '<div class="lb-block lb-stats-item" style="display: flex; flex-direction: column; align-items: %s; text-align: %s;">%s%s</div>',
            $flexAlign,
            $textAlign,
            $valueHtml,
            $labelHtml
        );
    }

    /**
     * Render feature-box block
     */
    protected function renderFeatureBox(array $props): string
    {
        $icon = $props['icon'] ?? 'lucide:star';
        $iconSize = $props['iconSize'] ?? '32px';
        $iconColor = $props['iconColor'] ?? '#3b82f6';
        $iconBackgroundColor = $props['iconBackgroundColor'] ?? '#dbeafe';
        $iconBackgroundShape = $props['iconBackgroundShape'] ?? 'circle';
        $title = $props['title'] ?? '';
        $titleColor = $props['titleColor'] ?? '#111827';
        $titleSize = $props['titleSize'] ?? '18px';
        $description = $props['description'] ?? '';
        $descriptionColor = $props['descriptionColor'] ?? '#6b7280';
        $descriptionSize = $props['descriptionSize'] ?? '14px';
        $align = $props['align'] ?? 'center';
        $layoutStyles = $props['layoutStyles'] ?? [];

        $alignMap = [
            'left' => 'flex-start',
            'center' => 'center',
            'right' => 'flex-end',
        ];

        $flexAlign = $alignMap[$align] ?? 'center';
        $textAlign = $align;

        // Build container styles
        $containerStyles = [
            'display: flex',
            'flex-direction: column',
            "align-items: {$flexAlign}",
            "text-align: {$textAlign}",
            'padding: 16px',
        ];

        // Add background and border radius from layoutStyles
        if (! empty($layoutStyles['background']['color'])) {
            $containerStyles[] = "background-color: {$layoutStyles['background']['color']}";
        }
        if (! empty($layoutStyles['border']['radius'])) {
            $radius = $layoutStyles['border']['radius'];
            $containerStyles[] = sprintf(
                'border-radius: %s %s %s %s',
                $radius['topLeft'] ?? '0',
                $radius['topRight'] ?? '0',
                $radius['bottomRight'] ?? '0',
                $radius['bottomLeft'] ?? '0'
            );
        }
        if (! empty($layoutStyles['padding'])) {
            $containerStyles[] = $this->buildPadding($layoutStyles['padding']);
        }

        // Build icon HTML
        $iconHtml = sprintf(
            '<iconify-icon icon="%s" width="%s" height="%s" style="color: %s;"></iconify-icon>',
            htmlspecialchars($icon),
            htmlspecialchars($iconSize),
            htmlspecialchars($iconSize),
            htmlspecialchars($iconColor)
        );

        if ($iconBackgroundColor && $iconBackgroundShape !== 'none') {
            $borderRadius = match ($iconBackgroundShape) {
                'circle' => '50%',
                'rounded' => '12px',
                'square' => '0',
                default => '0',
            };

            $iconHtml = sprintf(
                '<div style="background-color: %s; padding: 16px; border-radius: %s; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px;">%s</div>',
                htmlspecialchars($iconBackgroundColor),
                $borderRadius,
                $iconHtml
            );
        } else {
            $iconHtml = sprintf('<div style="margin-bottom: 16px;">%s</div>', $iconHtml);
        }

        $titleHtml = $title ? sprintf(
            '<h3 style="color: %s; font-size: %s; font-weight: 600; margin: 0 0 8px 0;">%s</h3>',
            htmlspecialchars($titleColor),
            htmlspecialchars($titleSize),
            htmlspecialchars($title)
        ) : '';

        $descriptionHtml = $description ? sprintf(
            '<p style="color: %s; font-size: %s; margin: 0; line-height: 1.5;">%s</p>',
            htmlspecialchars($descriptionColor),
            htmlspecialchars($descriptionSize),
            htmlspecialchars($description)
        ) : '';

        return sprintf(
            '<div class="lb-block lb-feature-box" style="%s">%s%s%s</div>',
            htmlspecialchars(implode('; ', $containerStyles)),
            $iconHtml,
            $titleHtml,
            $descriptionHtml
        );
    }

    /**
     * Render button block
     */
    protected function renderButton(array $props): string
    {
        $text = $props['text'] ?? 'Click Here';
        $link = $props['link'] ?? '#';
        $backgroundColor = $props['backgroundColor'] ?? '#3b82f6';
        $textColor = $props['textColor'] ?? '#ffffff';
        $borderRadius = $props['borderRadius'] ?? '6px';
        $padding = $props['padding'] ?? '12px 24px';
        $align = $props['align'] ?? 'center';
        $fontSize = $props['fontSize'] ?? '16px';
        $fontWeight = $props['fontWeight'] ?? '600';

        $buttonStyle = implode('; ', [
            'display: inline-block',
            "background-color: {$backgroundColor}",
            "color: {$textColor}",
            "padding: {$padding}",
            "border-radius: {$borderRadius}",
            'text-decoration: none',
            "font-size: {$fontSize}",
            "font-weight: {$fontWeight}",
        ]);

        return sprintf(
            '<div class="lb-block lb-button" style="text-align: %s; padding: 10px 0;"><a href="%s" style="%s">%s</a></div>',
            $align,
            htmlspecialchars($link),
            htmlspecialchars($buttonStyle),
            htmlspecialchars($text)
        );
    }

    /**
     * Render image block
     */
    protected function renderImage(array $props): string
    {
        $src = $props['src'] ?? '';
        $alt = $props['alt'] ?? '';
        $width = $props['width'] ?? '100%';
        $align = $props['align'] ?? 'center';
        $link = $props['link'] ?? '';

        if (empty($src)) {
            return '';
        }

        $imgHtml = sprintf(
            '<img src="%s" alt="%s" style="max-width: %s; height: auto; display: block;" />',
            htmlspecialchars($src),
            htmlspecialchars($alt),
            htmlspecialchars($width)
        );

        if ($link) {
            $imgHtml = sprintf('<a href="%s" target="_blank">%s</a>', htmlspecialchars($link), $imgHtml);
        }

        return sprintf(
            '<div class="lb-block lb-image" style="text-align: %s; padding: 10px 0;">%s</div>',
            $align,
            $imgHtml
        );
    }

    /**
     * Render spacer block
     */
    protected function renderSpacer(array $props): string
    {
        $height = $props['height'] ?? '20px';

        return sprintf('<div class="lb-block lb-spacer" style="height: %s;"></div>', htmlspecialchars($height));
    }

    /**
     * Render divider block
     */
    protected function renderDivider(array $props): string
    {
        $color = $props['color'] ?? '#e5e7eb';
        $thickness = $props['thickness'] ?? '1px';
        $width = $props['width'] ?? '100%';

        return sprintf(
            '<hr class="lb-block lb-divider" style="border: none; border-top: %s solid %s; width: %s; margin: 20px 0;" />',
            htmlspecialchars($thickness),
            htmlspecialchars($color),
            htmlspecialchars($width)
        );
    }

    /**
     * Build padding CSS from layoutStyles padding array
     */
    protected function buildPadding(array $padding): string
    {
        if (empty($padding)) {
            return '';
        }

        $top = $padding['top'] ?? '0';
        $right = $padding['right'] ?? '0';
        $bottom = $padding['bottom'] ?? '0';
        $left = $padding['left'] ?? '0';

        return "padding: {$top} {$right} {$bottom} {$left}";
    }

    /**
     * Build margin CSS from layoutStyles margin array
     */
    protected function buildMargin(array $margin): string
    {
        if (empty($margin)) {
            return '';
        }

        $top = $margin['top'] ?? '0';
        $right = $margin['right'] ?? '0';
        $bottom = $margin['bottom'] ?? '0';
        $left = $margin['left'] ?? '0';

        // Handle 'auto' values
        if ($left === 'auto' && $right === 'auto') {
            return "margin: {$top} auto {$bottom} auto";
        }

        return "margin: {$top} {$right} {$bottom} {$left}";
    }
}
