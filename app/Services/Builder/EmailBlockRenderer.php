<?php

declare(strict_types=1);

namespace App\Services\Builder;

use Illuminate\Support\Facades\Log;

/**
 * Email Block Renderer
 *
 * Processes email HTML content to render dynamic blocks server-side.
 * This handles blocks like CRM Contact that have data attributes
 * indicating they need server-side rendering.
 */
class EmailBlockRenderer
{
    public function __construct(
        protected BuilderService $builderService
    ) {
    }

    /**
     * Process email HTML content and render any dynamic blocks
     *
     * Looks for elements with data-lara-block attribute and replaces
     * them with server-rendered content.
     */
    public function processContent(string $content, string $context = 'email'): string
    {
        // Find all block placeholders
        $pattern = '/<div\s+data-lara-block="([^"]+)"([^>]*)>/is';

        if (! preg_match_all($pattern, $content, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
            return $content;
        }

        // Process blocks in reverse order to maintain correct positions
        $replacements = [];

        foreach ($matches as $match) {
            $blockType = $match[1][0];
            $attributes = $match[2][0];
            $openingTag = $match[0][0];
            $startPos = $match[0][1];

            try {
                $props = $this->extractProps($attributes);

                if ($this->builderService->hasBlockRenderCallback($blockType)) {
                    $rendered = $this->builderService->renderBlock($blockType, $props, $context);

                    if ($rendered !== null) {
                        $fullBlock = $this->findFullBlockHtml($content, $startPos);
                        if ($fullBlock !== null) {
                            $replacements[] = [
                                'start' => $startPos,
                                'length' => \strlen($fullBlock),
                                'replacement' => $rendered,
                            ];
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Failed to render block in email', [
                    'block_type' => $blockType,
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
                $replacement['start'],
                $replacement['length']
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
        if (preg_match("/data-props='([^']+)'/s", $attributes, $propsMatch)) {
            $propsJson = html_entity_decode($propsMatch[1], ENT_QUOTES, 'UTF-8');
            $decoded = json_decode($propsJson, true);

            if (json_last_error() === JSON_ERROR_NONE && \is_array($decoded)) {
                $props = $decoded;
            }
        }

        return $props;
    }
}
