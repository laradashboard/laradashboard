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
    protected function renderBlock(string $blockType, array $props, string $context): ?string
    {
        $callback = $this->getBlockRenderCallback($blockType);

        if (! $callback) {
            return null;
        }

        return call_user_func($callback, $props, $context);
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
        // Find all block placeholders - match the full element including closing tag
        // Pattern matches: <div data-lara-block="type" data-props='...'></div>
        // The data-props value is wrapped in single quotes and may contain complex JSON
        $pattern = '/<div\s+data-lara-block="([^"]+)"\s+data-props=\'((?:[^\']|&#39;)*)\'>([^<]*)<\/div>/is';

        if (! preg_match_all($pattern, $content, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
            return $content;
        }

        // Process blocks in reverse order to maintain correct positions
        $replacements = [];

        foreach ($matches as $match) {
            $blockType = $match[1][0];
            $propsJson = $match[2][0];
            $startPos = (int) $match[0][1];
            $fullMatch = $match[0][0];

            try {
                // Decode props from JSON
                $propsJson = html_entity_decode($propsJson, ENT_QUOTES, 'UTF-8');
                $props = json_decode($propsJson, true) ?? [];

                // Use auto-discovery method which checks registered + discovers render.php
                $rendered = $this->renderBlock($blockType, $props, $context);

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
