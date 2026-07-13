<?php

declare(strict_types=1);

namespace App\Support\Builder;

/**
 * Shared content typography tokens for Lara Builder page rendering.
 */
final class ContentTokens
{
    public const TEXT = '#111827';

    public const HEADING = '#111827';

    public const MUTED = '#6b7280';

    public const ON_DARK = '#f9fafb';

    public const QUOTE_TEXT = '#374151';

    public const QUOTE_AUTHOR = '#111827';

    /** @var list<string> */
    private const LEGACY_CONTENT_COLORS = [
        '#666666',
        '#333333',
        '#374151',
        '#475569',
        '#64748b',
        '#6b7280',
        '#111827',
        '#1e293b',
        '#0f172a',
        '#94a3b8',
        '#d1d5db',
    ];

    public static function shouldInheritContentColor(?string $color): bool
    {
        $normalized = self::normalizeHexColor($color);

        if ($normalized === '' || $normalized === 'inherit' || $normalized === 'currentcolor') {
            return true;
        }

        return in_array($normalized, self::LEGACY_CONTENT_COLORS, true);
    }

    public static function resolvePageTextColor(?string $color, ?string $typographyColor = null): ?string
    {
        if ($typographyColor !== null && $typographyColor !== '' && ! self::shouldInheritContentColor($typographyColor)) {
            return $typographyColor;
        }

        if (self::shouldInheritContentColor($color)) {
            return null;
        }

        return $color;
    }

    public static function resolveEmailTextColor(?string $color, ?string $typographyColor = null, string $fallback = self::TEXT): string
    {
        if ($typographyColor !== null && $typographyColor !== '' && ! self::shouldInheritContentColor($typographyColor)) {
            return $typographyColor;
        }

        if (self::shouldInheritContentColor($color)) {
            return $fallback;
        }

        return $color ?? $fallback;
    }

    public static function resolveHeadingPageColor(?string $color, ?string $typographyColor = null): ?string
    {
        return self::resolvePageTextColor($color, $typographyColor);
    }

    public static function resolveHeadingEmailColor(?string $color, ?string $typographyColor = null): string
    {
        return self::resolveEmailTextColor($color, $typographyColor, self::HEADING);
    }

    /**
     * @return list<string>
     */
    public static function pageWrapperVariableStyles(): array
    {
        return [
            '--lb-color-text: ' . self::TEXT,
            '--lb-color-heading: ' . self::HEADING,
            '--lb-color-muted: ' . self::MUTED,
            '--lb-color-on-dark: ' . self::ON_DARK,
            'color: var(--lb-color-text)',
        ];
    }

    /**
     * @return list<string>
     */
    public static function sectionTextColorStyles(?string $textColor): array
    {
        if (self::shouldInheritContentColor($textColor)) {
            return [];
        }

        return [
            "color: {$textColor}",
            "--lb-color-text: {$textColor}",
            "--lb-color-heading: {$textColor}",
        ];
    }

    public static function contentTokensCss(): string
    {
        $path = resource_path('js/lara-builder/styles/content-tokens.css');

        if (! file_exists($path)) {
            return '';
        }

        return file_get_contents($path) ?: '';
    }

    private static function normalizeHexColor(?string $color): string
    {
        if ($color === null) {
            return '';
        }

        return strtolower(trim($color));
    }
}
