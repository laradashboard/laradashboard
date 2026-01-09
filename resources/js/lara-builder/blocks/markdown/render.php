<?php

/**
 * Markdown Block - Server-side Renderer
 *
 * This callback is invoked by the BlockRenderer when processing content.
 * Supports two modes:
 * - content: Converts direct markdown content to HTML
 * - url: Fetches markdown from external URLs and converts to HTML
 *
 * Benefits of server-side rendering:
 * - Avoids CORS issues with external URLs
 * - Enables caching for better performance
 * - Secure URL validation
 * - GitHub Flavored Markdown support
 */

use App\Services\Builder\MarkdownFetchService;

return function (array $props, string $context = 'page', ?string $blockId = null): string {
    $sourceType = $props['sourceType'] ?? 'content';
    $content = $props['content'] ?? '';
    $url = $props['url'] ?? '';
    $showSource = $props['showSource'] ?? true;
    $cacheEnabled = $props['cacheEnabled'] ?? true;
    $layoutStyles = $props['layoutStyles'] ?? [];

    // Get the markdown service
    /** @var MarkdownFetchService $markdownService */
    $markdownService = app(MarkdownFetchService::class);

    // Handle based on source type
    if ($sourceType === 'content') {
        // Direct content mode
        if (empty($content)) {
            return '<div class="lb-block lb-markdown markdown-empty" style="padding: 24px; text-align: center; color: #9ca3af; background: #f9fafb; border: 1px dashed #e5e7eb; border-radius: 8px;">
                <p style="margin: 0; font-size: 14px;">No markdown content configured</p>
            </div>';
        }

        $result = $markdownService->convertMarkdown($content);
    } else {
        // URL mode
        if (empty($url)) {
            return '<div class="lb-block lb-markdown markdown-empty" style="padding: 24px; text-align: center; color: #9ca3af; background: #f9fafb; border: 1px dashed #e5e7eb; border-radius: 8px;">
                <p style="margin: 0; font-size: 14px;">No markdown URL configured</p>
            </div>';
        }

        $result = $markdownService->fetchAndConvert($url, $cacheEnabled);
    }

    // Build wrapper styles
    $wrapperStyles = [];

    // Background
    if (! empty($layoutStyles['background']['color'])) {
        $wrapperStyles[] = 'background-color: ' . e($layoutStyles['background']['color']);
    }

    // Padding
    if (! empty($layoutStyles['padding'])) {
        $padding = $layoutStyles['padding'];
        foreach (['top', 'right', 'bottom', 'left'] as $side) {
            if (! empty($padding[$side])) {
                $wrapperStyles[] = "padding-{$side}: " . e($padding[$side]);
            }
        }
    }

    // Margin
    if (! empty($layoutStyles['margin'])) {
        $margin = $layoutStyles['margin'];
        foreach (['top', 'right', 'bottom', 'left'] as $side) {
            if (! empty($margin[$side])) {
                $wrapperStyles[] = "margin-{$side}: " . e($margin[$side]);
            }
        }
    }

    // Border
    if (! empty($layoutStyles['border'])) {
        $border = $layoutStyles['border'];

        $borderWidth = $border['width'] ?? [];
        if (! empty($borderWidth['top'])) {
            $wrapperStyles[] = 'border-top-width: ' . e($borderWidth['top']);
        }
        if (! empty($borderWidth['right'])) {
            $wrapperStyles[] = 'border-right-width: ' . e($borderWidth['right']);
        }
        if (! empty($borderWidth['bottom'])) {
            $wrapperStyles[] = 'border-bottom-width: ' . e($borderWidth['bottom']);
        }
        if (! empty($borderWidth['left'])) {
            $wrapperStyles[] = 'border-left-width: ' . e($borderWidth['left']);
        }

        if (! empty($border['style'])) {
            $wrapperStyles[] = 'border-style: ' . e($border['style']);
        }
        if (! empty($border['color'])) {
            $wrapperStyles[] = 'border-color: ' . e($border['color']);
        }

        $radius = $border['radius'] ?? [];
        if (! empty($radius['topLeft'])) {
            $wrapperStyles[] = 'border-top-left-radius: ' . e($radius['topLeft']);
        }
        if (! empty($radius['topRight'])) {
            $wrapperStyles[] = 'border-top-right-radius: ' . e($radius['topRight']);
        }
        if (! empty($radius['bottomLeft'])) {
            $wrapperStyles[] = 'border-bottom-left-radius: ' . e($radius['bottomLeft']);
        }
        if (! empty($radius['bottomRight'])) {
            $wrapperStyles[] = 'border-bottom-right-radius: ' . e($radius['bottomRight']);
        }
    }

    $styleAttr = ! empty($wrapperStyles) ? ' style="' . implode('; ', $wrapperStyles) . '"' : '';

    // Handle error
    if (! $result['success']) {
        $error = e($result['error'] ?? 'Unknown error');

        return '<div class="lb-block lb-markdown markdown-error"' . $styleAttr . '>
            <div style="padding: 16px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px;">
                <p style="margin: 0; color: #dc2626; font-size: 14px; font-weight: 500;">Failed to load markdown</p>
                <p style="margin: 8px 0 0; color: #ef4444; font-size: 12px;">' . $error . '</p>
            </div>
        </div>';
    }

    $html = $result['html'];
    $sourceUrl = e($result['source_url'] ?? $url);

    // Build the output
    $output = '<div class="lb-block lb-markdown markdown-content"' . $styleAttr . '>';

    // Source URL indicator (only for URL mode)
    if ($showSource && $sourceType === 'url' && ! empty($url)) {
        $output .= '<div class="markdown-source" style="margin-bottom: 12px; padding: 8px 12px; background: #f9fafb; border-radius: 6px; font-size: 12px; color: #6b7280;">
            <span style="margin-right: 8px;">Source:</span>
            <a href="' . $sourceUrl . '" target="_blank" rel="noopener noreferrer" style="color: #6366f1; text-decoration: underline;">' . $sourceUrl . '</a>
        </div>';
    }

    // Markdown content
    $output .= '<div class="markdown-body">' . $html . '</div>';

    $output .= '</div>';

    return $output;
};
