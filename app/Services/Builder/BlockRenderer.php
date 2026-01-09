<?php

declare(strict_types=1);

namespace App\Services\Builder;

use Illuminate\Support\Facades\Log;

/**
 * Block Renderer
 *
 * Processes HTML content to render dynamic blocks server-side.
 * This handles blocks that have data-lara-block attributes and
 * auto-discovers render.php files in block folders.
 *
 * Works for all contexts: email, page, campaign.
 */
class BlockRenderer
{
    /**
     * Cache for discovered render callbacks
     */
    protected array $discoveredCallbacks = [];

    public function __construct(
        protected BuilderService $builderService
    ) {
    }

    /**
     * Get the render callback for a block type.
     * First checks registered callbacks, then auto-discovers render.php files.
     */
    protected function getBlockRenderCallback(string $blockType): ?callable
    {
        // Check if already registered via BuilderService
        if ($this->builderService->hasBlockRenderCallback($blockType)) {
            return $this->builderService->getBlockRenderCallback($blockType);
        }

        // Check discovery cache
        if (array_key_exists($blockType, $this->discoveredCallbacks)) {
            return $this->discoveredCallbacks[$blockType];
        }

        // Auto-discover render.php in core blocks folder
        $renderPath = resource_path("js/lara-builder/blocks/{$blockType}/render.php");

        if (file_exists($renderPath)) {
            $callback = require $renderPath;
            if (is_callable($callback)) {
                $this->discoveredCallbacks[$blockType] = $callback;

                return $callback;
            }
        }

        // Cache null to avoid repeated file checks
        $this->discoveredCallbacks[$blockType] = null;

        return null;
    }

    /**
     * Render a block using its callback
     */
    protected function renderBlock(string $blockType, array $props, string $context, ?string $blockId = null): ?string
    {
        $callback = $this->getBlockRenderCallback($blockType);

        if (! $callback) {
            return null;
        }

        return call_user_func($callback, $props, $context, $blockId);
    }

    /**
     * Process HTML content and render any dynamic blocks
     *
     * Looks for elements with data-lara-block attribute and replaces
     * them with server-rendered content.
     *
     * @param  string  $content  The HTML content to process
     * @param  string  $context  The rendering context (email, page, campaign)
     * @return string The processed HTML with dynamic blocks rendered
     */
    public function processContent(string $content, string $context = 'page'): string
    {
        // First, handle legacy markdown blocks with data-block-type="markdown" format
        $content = $this->processLegacyMarkdownBlocks($content, $context);

        // Find all block placeholders by locating opening tags with data-lara-block attribute
        // Pattern matches the opening tag: <div data-lara-block="type" [data-block-id="id"] data-props='...' [other-attrs]>
        // This allows for any additional attributes (like style) after data-props
        // Note: Using optimized pattern to avoid JIT stack exhaustion with large props content
        // The pattern [^\']*(?:&#39;[^\']*)* is more efficient than (?:[^\']|&#39;)* for large strings
        $openingTagPattern = '/<div\s+data-lara-block="([^"]+)"(?:\s+data-block-id="([^"]*)")?\s+data-props=\'([^\']*(?:&#39;[^\']*)*)\'[^>]*>/is';

        if (! preg_match_all($openingTagPattern, $content, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
            return $content;
        }

        // Pre-calculate word count for time-to-read blocks
        $wordCount = $this->calculateWordCount($content);

        // Process blocks in reverse order to maintain correct positions
        $replacements = [];

        foreach ($matches as $match) {
            $blockType = $match[1][0];
            $blockId = (\count($match) > 2 && $match[2][0] !== '') ? $match[2][0] : null;
            $propsJson = $match[3][0];
            $startPos = (int) $match[0][1];

            // Find the full block HTML including nested content and proper closing tag
            $fullMatch = $this->findFullBlockHtml($content, $startPos);

            if ($fullMatch === null) {
                continue;
            }

            try {
                // Decode props from JSON
                $propsJson = html_entity_decode($propsJson, ENT_QUOTES, 'UTF-8');
                $props = json_decode($propsJson, true) ?? [];

                // Inject word count for time-to-read block
                if ($blockType === 'time-to-read') {
                    $props['_wordCount'] = $wordCount;
                }

                // Use auto-discovery method which checks registered + discovers render.php
                $rendered = $this->renderBlock($blockType, $props, $context, $blockId);

                if ($rendered !== null) {
                    $replacements[] = [
                        'start' => $startPos,
                        'length' => \strlen($fullMatch),
                        'replacement' => $rendered,
                    ];
                }
            } catch (\Throwable $e) {
                Log::warning('Failed to render block', [
                    'block_type' => $blockType,
                    'context' => $context,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Apply replacements in reverse order
        usort($replacements, fn ($a, $b) => $b['start'] - $a['start']);

        foreach ($replacements as $replacement) {
            $content = substr_replace(
                $content,
                $replacement['replacement'],
                (int) $replacement['start'],
                (int) $replacement['length']
            );
        }

        return $content;
    }

    /**
     * Process legacy markdown blocks with data-block-type="markdown" format.
     *
     * This handles blocks saved before the save.js fix that used data-block-type
     * instead of data-lara-block attribute.
     */
    protected function processLegacyMarkdownBlocks(string $content, string $context): string
    {
        // Pattern for legacy markdown blocks: data-block-type="markdown" data-url="..."
        $legacyPattern = '/<div[^>]*data-block-type="markdown"[^>]*data-url="([^"]*)"[^>]*data-show-source="([^"]*)"[^>]*>.*?<\/div>/is';

        return preg_replace_callback($legacyPattern, function ($match) use ($context) {
            $url = urldecode($match[1]);
            $showSource = $match[2] !== 'false';

            if (empty($url)) {
                return $match[0]; // Return original if no URL
            }

            $props = [
                'url' => $url,
                'showSource' => $showSource,
                'cacheEnabled' => true,
                'layoutStyles' => [],
            ];

            $rendered = $this->renderBlock('markdown', $props, $context);

            return $rendered ?? $match[0];
        }, $content) ?? $content;
    }

    /**
     * Calculate word count from HTML content
     *
     * Strips HTML tags and counts words for reading time calculation.
     */
    protected function calculateWordCount(string $content): int
    {
        // Strip all HTML tags
        $text = strip_tags($content);

        // Decode HTML entities
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');

        // Remove extra whitespace and normalize
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);

        // Return 0 for empty content
        if ($text === '') {
            return 0;
        }

        // Count words
        return str_word_count($text);
    }

    /**
     * Find the full block HTML including nested content
     */
    protected function findFullBlockHtml(string $content, int $startPos): ?string
    {
        $searchStart = strpos($content, '>', $startPos);
        if ($searchStart === false) {
            return null;
        }
        $searchStart++;

        $depth = 1;
        $pos = $searchStart;
        $contentLength = \strlen($content);

        while ($depth > 0 && $pos < $contentLength) {
            $nextOpen = strpos($content, '<div', $pos);
            $nextClose = strpos($content, '</div>', $pos);

            if ($nextClose === false) {
                break;
            }

            if ($nextOpen !== false && $nextOpen < $nextClose) {
                $depth++;
                $pos = $nextOpen + 4;
            } else {
                $depth--;
                $pos = $nextClose + 6;
            }
        }

        if ($depth === 0) {
            return substr($content, $startPos, $pos - $startPos);
        }

        return null;
    }

    /**
     * Extract props from element attributes
     */
    protected function extractProps(string $attributes): array
    {
        $props = [];

        // Extract data-props JSON
        // data-props uses single quotes to wrap, JSON uses double quotes inside
        // The &#39; is the HTML entity for single quote, so we need to handle both
        // Use a greedy match that captures everything between data-props=' and the closing '
        if (preg_match("/data-props='(.+?)(?<!\\\)'/s", $attributes, $propsMatch)) {
            $propsJson = html_entity_decode($propsMatch[1], ENT_QUOTES, 'UTF-8');
            $decoded = json_decode($propsJson, true);

            if (json_last_error() === JSON_ERROR_NONE && \is_array($decoded)) {
                $props = $decoded;
            }
        }

        return $props;
    }
}
