<?php

declare(strict_types=1);

namespace App\Services;

class ThemeColorService
{
    /**
     * Generate a color palette based on a primary color
     *
     * @param  string  $baseColor  Hex color code (e.g. #635bff)
     * @param  int  $steps  Number of steps to generate
     * @return array Array of color variations
     */
    public static function generateColorPalette($baseColor, $steps = 9): array
    {
        // Remove '#' if present
        $baseColor = ltrim($baseColor, '#');

        // Convert to RGB
        $r = hexdec(substr($baseColor, 0, 2));
        $g = hexdec(substr($baseColor, 2, 2));
        $b = hexdec(substr($baseColor, 4, 2));

        $palette = [];

        // Generate lighter variations (50, 100, 200, 300, 400)
        for ($i = 5; $i > 0; $i--) {
            $factor = 1 - ($i * 0.1);

            // Make color lighter
            $nr = $r + (255 - $r) * $factor;
            $ng = $g + (255 - $g) * $factor;
            $nb = $b + (255 - $b) * $factor;

            $key = $i * 100;
            if ($i === 5) {
                $key = 50;
            }

            $palette[$key] = sprintf('#%02x%02x%02x', $nr, $ng, $nb);
        }

        // Add base color as 500
        $palette[500] = "#{$baseColor}";

        // Generate darker variations (600, 700, 800, 900)
        for ($i = 1; $i <= 4; $i++) {
            $factor = 1 - ($i * 0.1);

            // Make color darker
            $nr = $r * $factor;
            $ng = $g * $factor;
            $nb = $b * $factor;

            $palette[500 + ($i * 100)] = sprintf('#%02x%02x%02x', $nr, $ng, $nb);
        }

        return $palette;
    }
}
