<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Email Style Helper
 *
 * Shared utility for building email-safe inline CSS from layoutStyles.
 * Used by render.php files when $context === 'email'.
 */
class EmailStyleHelper
{
    /**
     * Ensure a CSS value has a unit. Numeric values get 'px' appended.
     */
    public static function cssUnit(mixed $value): string
    {
        if (empty($value) && $value !== '0' && $value !== 0) {
            return '0';
        }

        $str = (string) $value;

        if (preg_match('/^\d+(\.\d+)?$/', $str)) {
            return "{$str}px";
        }

        return $str;
    }

    /**
     * Build inline border CSS from layoutStyles border array.
     */
    public static function buildBorderStyles(array $border): string
    {
        $styles = [];

        $width = $border['width'] ?? [];
        if (! empty($width['top'])) {
            $styles[] = 'border: ' . self::cssUnit($width['top']) . ' ' . ($border['style'] ?? 'solid') . ' ' . ($border['color'] ?? '#000000');
        }

        $radius = $border['radius'] ?? [];
        $radiusValues = array_filter([
            $radius['topLeft'] ?? null,
            $radius['topRight'] ?? null,
            $radius['bottomRight'] ?? null,
            $radius['bottomLeft'] ?? null,
        ]);

        if (! empty($radiusValues)) {
            $allSame = count(array_unique($radiusValues)) === 1 && count($radiusValues) === 4;
            if ($allSame) {
                $styles[] = 'border-radius: ' . self::cssUnit(reset($radiusValues));
            } else {
                if (! empty($radius['topLeft'])) {
                    $styles[] = 'border-top-left-radius: ' . self::cssUnit($radius['topLeft']);
                }
                if (! empty($radius['topRight'])) {
                    $styles[] = 'border-top-right-radius: ' . self::cssUnit($radius['topRight']);
                }
                if (! empty($radius['bottomRight'])) {
                    $styles[] = 'border-bottom-right-radius: ' . self::cssUnit($radius['bottomRight']);
                }
                if (! empty($radius['bottomLeft'])) {
                    $styles[] = 'border-bottom-left-radius: ' . self::cssUnit($radius['bottomLeft']);
                }
            }
        }

        return implode('; ', $styles);
    }

    /**
     * Build inline spacing/background CSS from layoutStyles.
     */
    public static function buildLayoutStyles(array $layoutStyles): string
    {
        $styles = [];

        // Margin
        if (! empty($layoutStyles['margin'])) {
            $margin = $layoutStyles['margin'];
            foreach (['top', 'right', 'bottom', 'left'] as $side) {
                if (! empty($margin[$side])) {
                    $styles[] = "margin-{$side}: " . self::cssUnit($margin[$side]);
                }
            }
        }

        // Padding
        if (! empty($layoutStyles['padding'])) {
            $padding = $layoutStyles['padding'];
            foreach (['top', 'right', 'bottom', 'left'] as $side) {
                if (! empty($padding[$side])) {
                    $styles[] = "padding-{$side}: " . self::cssUnit($padding[$side]);
                }
            }
        }

        // Background
        if (! empty($layoutStyles['background']['color'])) {
            $styles[] = "background-color: {$layoutStyles['background']['color']}";
        }

        // Border
        if (! empty($layoutStyles['border'])) {
            $borderCSS = self::buildBorderStyles($layoutStyles['border']);
            if ($borderCSS) {
                $styles[] = $borderCSS;
            }
        }

        // Box shadow
        if (! empty($layoutStyles['boxShadow'])) {
            $shadow = $layoutStyles['boxShadow'];
            $x = self::cssUnit($shadow['x'] ?? '0');
            $y = self::cssUnit($shadow['y'] ?? '0');
            $blur = self::cssUnit($shadow['blur'] ?? '0');
            $spread = self::cssUnit($shadow['spread'] ?? '0');
            $color = $shadow['color'] ?? 'rgba(0,0,0,0.1)';
            $inset = ! empty($shadow['inset']) ? 'inset ' : '';
            $styles[] = "box-shadow: {$inset}{$x} {$y} {$blur} {$spread} {$color}";
        }

        return implode('; ', $styles);
    }

    /**
     * Wrap content in an email-safe table with layout styles.
     */
    public static function wrapInEmailTable(string $content, array $layoutStyles = [], string $align = 'left'): string
    {
        $layoutCSS = self::buildLayoutStyles($layoutStyles);
        $tdStyles = "text-align: {$align}";
        if ($layoutCSS) {
            $tdStyles .= "; {$layoutCSS}";
        }

        return '<table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation"><tr><td style="' . e($tdStyles) . '">' . $content . '</td></tr></table>';
    }

    /**
     * Build email-safe inline text styles from props.
     */
    public static function buildTextStyles(array $props, array $layoutStyles = []): string
    {
        $typography = $layoutStyles['typography'] ?? [];
        $styles = [
            'font-family: Arial, Helvetica, sans-serif',
            'margin: 0',
        ];

        $color = $typography['color'] ?? $props['color'] ?? '#333333';
        $styles[] = "color: {$color}";

        $fontSize = $typography['fontSize'] ?? $props['fontSize'] ?? '16px';
        $styles[] = "font-size: {$fontSize}";

        $fontWeight = $typography['fontWeight'] ?? $props['fontWeight'] ?? 'normal';
        $styles[] = "font-weight: {$fontWeight}";

        $lineHeight = $typography['lineHeight'] ?? $props['lineHeight'] ?? '1.5';
        $styles[] = "line-height: {$lineHeight}";

        if (! empty($props['align'])) {
            $styles[] = "text-align: {$props['align']}";
        }

        if (! empty($typography['letterSpacing']) || ! empty($props['letterSpacing'])) {
            $ls = $typography['letterSpacing'] ?? $props['letterSpacing'];
            if ($ls && $ls !== '0') {
                $styles[] = "letter-spacing: {$ls}";
            }
        }

        return implode('; ', $styles);
    }
}
