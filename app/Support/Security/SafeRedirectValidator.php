<?php

declare(strict_types=1);

namespace App\Support\Security;

class SafeRedirectValidator
{
    /**
     * Return a safe redirect target, or null when the URL must be ignored.
     */
    public function sanitize(?string $url): ?string
    {
        if ($url === null) {
            return null;
        }

        $url = trim($url);

        if ($url === '') {
            return null;
        }

        if (preg_match('/[\x00-\x1F\x7F]/', $url) === 1) {
            return null;
        }

        if (str_starts_with($url, '/') && ! str_starts_with($url, '//')) {
            return $url;
        }

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $parts = parse_url($url);

        if ($parts === false) {
            return null;
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));

        if (! in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        $host = strtolower((string) ($parts['host'] ?? ''));

        if ($host === '') {
            return null;
        }

        if (! $this->isSameApplicationHost($host)) {
            return null;
        }

        if (app()->environment('production') && $scheme !== 'https') {
            return null;
        }

        return $url;
    }

    private function isSameApplicationHost(string $host): bool
    {
        $appHost = strtolower((string) parse_url((string) config('app.url'), PHP_URL_HOST));

        if ($appHost === '') {
            return false;
        }

        return $host === $appHost;
    }
}
