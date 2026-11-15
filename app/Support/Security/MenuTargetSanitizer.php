<?php

declare(strict_types=1);

namespace App\Support\Security;

/**
 * Allowlists the URL scheme of a menu item's free-form `target`, which a "custom" item
 * renders verbatim as the nav href (GHSA-883p-xm36-3p99).
 */
class MenuTargetSanitizer
{
    /**
     * @var list<string>
     */
    private const ALLOWED_SCHEMES = ['http', 'https', 'mailto', 'tel', 'sms'];

    /**
     * No scheme (relative path, `?query`, `#fragment`, `//host`) is safe; otherwise the scheme must be allowlisted.
     */
    public static function isSafe(?string $target): bool
    {
        $target = (string) $target;

        if (trim($target) === '') {
            return true;
        }

        $scheme = self::extractScheme($target);

        return $scheme === null || in_array($scheme, self::ALLOWED_SCHEMES, true);
    }

    /**
     * Render-time backstop for rows that skipped validation (older data, seeders, imports).
     */
    public static function sanitize(?string $target): string
    {
        $target = trim((string) $target);

        if ($target === '') {
            return '#';
        }

        return self::isSafe($target) ? $target : '#';
    }

    /**
     * Browsers drop C0 control characters and spaces before reading the scheme, so
     * "\x0Cjavascript:" and "java\tscript:" still run as javascript:. Strip them all first.
     */
    private static function extractScheme(string $target): ?string
    {
        $normalized = preg_replace('/[\x00-\x20\x7F]/', '', $target) ?? '';

        if (! preg_match('/^([a-zA-Z][a-zA-Z0-9+\-.]*):/', $normalized, $matches)) {
            return null;
        }

        return strtolower($matches[1]);
    }
}
