<?php

declare(strict_types=1);

namespace App\Support\Security;

/**
 * Guards the free-form `target` field on a "custom" type MenuItem.
 *
 * Unlike the page/post/category/tag/route menu item types, a custom item's target is
 * rendered verbatim as the public navigation's href ({@see \App\Models\MenuItem::getUrl()}).
 * Blade's `{{ }}` only HTML-entity-escapes; it does not validate or restrict URL schemes,
 * so without this guard a saved `javascript:` (or `vbscript:`/`data:`) target becomes a
 * live, clickable stored-XSS payload on every page that renders the menu.
 *
 * See GHSA-883p-xm36-3p99.
 */
class MenuTargetSanitizer
{
    /**
     * Schemes a "Custom Link" menu item is allowed to point at.
     *
     * @var list<string>
     */
    private const ALLOWED_SCHEMES = ['http', 'https', 'mailto', 'tel', 'sms'];

    /**
     * Whether $target is safe to render directly as an href.
     *
     * A target with no scheme (a relative path, a query string, a `#fragment`, or a
     * protocol-relative `//host/path`) is always safe — none of those can execute script
     * from an href. A target with a scheme must be one of the allowed, navigable ones.
     */
    public static function isSafe(?string $target): bool
    {
        $target = (string) $target;

        if (trim($target) === '') {
            return true;
        }

        $scheme = self::extractScheme($target);

        if ($scheme === null) {
            return true;
        }

        return in_array($scheme, self::ALLOWED_SCHEMES, true);
    }

    /**
     * Return $target if it's safe to render as an href, or '#' otherwise.
     *
     * This is the last line of defense for data that reached the model without going
     * through {@see \App\Rules\SafeMenuTargetUrl} (an older row, a seeder, a direct
     * database edit), so {@see \App\Models\MenuItem::getUrl()} never has to return an
     * unchecked value for the 'custom' type.
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
     * Extract the URL scheme a browser would act on, or null if there isn't one.
     *
     * Browsers strip tab/newline/carriage-return characters and leading whitespace
     * before resolving a URL's scheme, so "java\tscript:alert(1)" and
     * "   javascript:alert(1)" both still execute as `javascript:` even though
     * `parse_url()` fails to see a scheme in either string. We normalize the same way
     * before looking for one, rather than trusting `parse_url()` alone.
     */
    private static function extractScheme(string $target): ?string
    {
        $normalized = ltrim(str_replace(["\t", "\n", "\r"], '', $target));

        if (! preg_match('/^([a-zA-Z][a-zA-Z0-9+\-.]*):/', $normalized, $matches)) {
            return null;
        }

        return strtolower($matches[1]);
    }
}
