<?php

declare(strict_types=1);

namespace App\Services\Builder;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Extension\TaskList\TaskListExtension;
use League\CommonMark\MarkdownConverter;

class MarkdownFetchService
{
    private MarkdownConverter $converter;

    public function __construct()
    {
        $environment = new Environment([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'max_nesting_level' => 100,
        ]);

        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new GithubFlavoredMarkdownExtension());
        $environment->addExtension(new TableExtension());
        $environment->addExtension(new TaskListExtension());
        $environment->addExtension(new AutolinkExtension());

        $this->converter = new MarkdownConverter($environment);
    }

    /**
     * Fetch markdown from URL, convert to HTML.
     *
     * @return array{success: bool, html?: string, markdown?: string, error?: string, cached?: bool}
     */
    public function fetchAndConvert(string $url, bool $useCache = true, int $cacheTtl = 3600): array
    {
        // Validate URL
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return ['success' => false, 'error' => 'Invalid URL format'];
        }

        // Convert to raw URL if needed
        $rawUrl = $this->toRawUrl($url);

        // Check cache
        $cacheKey = 'markdown_fetch:' . md5($rawUrl);
        if ($useCache && Cache::has($cacheKey)) {
            $cached = Cache::get($cacheKey);

            return [
                'success' => true,
                'html' => $cached['html'],
                'markdown' => $cached['markdown'],
                'cached' => true,
                'source_url' => $rawUrl,
            ];
        }

        // Fetch content
        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Accept' => 'text/plain, text/markdown, */*',
                    'User-Agent' => 'LaraDashboard-Builder/1.0',
                ])
                ->get($rawUrl);

            if (! $response->successful()) {
                return [
                    'success' => false,
                    'error' => 'Failed to fetch content: HTTP ' . $response->status(),
                ];
            }

            $markdown = $response->body();

            // Check if it's actually markdown content
            if (empty(trim($markdown))) {
                return ['success' => false, 'error' => 'Empty content received'];
            }

            // Convert to HTML
            $html = $this->convertToHtml($markdown);

            // Cache the result
            if ($useCache) {
                Cache::put($cacheKey, [
                    'html' => $html,
                    'markdown' => $markdown,
                ], $cacheTtl);
            }

            return [
                'success' => true,
                'html' => $html,
                'markdown' => $markdown,
                'cached' => false,
                'source_url' => $rawUrl,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to fetch content: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Convert markdown string to HTML.
     */
    public function convertToHtml(string $markdown): string
    {
        return $this->converter->convert($markdown)->getContent();
    }

    /**
     * Convert markdown content directly (without fetching from URL).
     *
     * @return array{success: bool, html?: string, error?: string}
     */
    public function convertMarkdown(string $markdown): array
    {
        if (empty(trim($markdown))) {
            return ['success' => false, 'error' => 'Empty content'];
        }

        try {
            $html = $this->convertToHtml($markdown);

            return [
                'success' => true,
                'html' => $html,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to convert markdown: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Convert various repository URLs to their raw content URLs.
     */
    public function toRawUrl(string $url): string
    {
        // GitHub: github.com/user/repo/blob/branch/path -> raw.githubusercontent.com/user/repo/branch/path
        if (preg_match('#^https?://github\.com/([^/]+)/([^/]+)/blob/([^/]+)/(.+)$#', $url, $matches)) {
            return "https://raw.githubusercontent.com/{$matches[1]}/{$matches[2]}/{$matches[3]}/{$matches[4]}";
        }

        // GitHub with refs/heads: raw.githubusercontent.com/.../refs/heads/branch/... -> raw.githubusercontent.com/.../branch/...
        if (preg_match('#^https?://raw\.githubusercontent\.com/([^/]+)/([^/]+)/refs/heads/([^/]+)/(.+)$#', $url, $matches)) {
            return "https://raw.githubusercontent.com/{$matches[1]}/{$matches[2]}/{$matches[3]}/{$matches[4]}";
        }

        // GitLab: gitlab.com/user/repo/-/blob/branch/path -> gitlab.com/user/repo/-/raw/branch/path
        if (preg_match('#^https?://gitlab\.com/(.+)/-/blob/(.+)$#', $url, $matches)) {
            return "https://gitlab.com/{$matches[1]}/-/raw/{$matches[2]}";
        }

        // Bitbucket: bitbucket.org/user/repo/src/branch/path -> bitbucket.org/user/repo/raw/branch/path
        if (preg_match('#^https?://bitbucket\.org/([^/]+)/([^/]+)/src/([^/]+)/(.+)$#', $url, $matches)) {
            return "https://bitbucket.org/{$matches[1]}/{$matches[2]}/raw/{$matches[3]}/{$matches[4]}";
        }

        // Already a raw URL or other URL, return as-is
        return $url;
    }

    /**
     * Clear cache for a specific URL.
     */
    public function clearCache(string $url): void
    {
        $rawUrl = $this->toRawUrl($url);
        $cacheKey = 'markdown_fetch:' . md5($rawUrl);
        Cache::forget($cacheKey);
    }

    /**
     * Check if a URL is a supported markdown source.
     */
    public function isSupportedSource(string $url): bool
    {
        $supportedDomains = [
            'github.com',
            'raw.githubusercontent.com',
            'gitlab.com',
            'bitbucket.org',
            'gist.github.com',
            'gist.githubusercontent.com',
        ];

        $host = parse_url($url, PHP_URL_HOST);

        // Allow any URL that ends with .md or is from supported domains
        if (preg_match('/\.md$/i', parse_url($url, PHP_URL_PATH) ?? '')) {
            return true;
        }

        return in_array($host, $supportedDomains, true);
    }
}
