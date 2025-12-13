<?php

/**
 * Table of Contents Block - Server-side Renderer
 *
 * This callback is invoked by the BlockRenderer when processing content.
 * It scans all blocks passed via props and generates the TOC from headings.
 *
 * The save.js file outputs a placeholder with data-lara-block="toc" and includes
 * all blocks in data-props._allBlocks so we can scan for headings here.
 */

return function (array $props, string $context = 'page', ?string $blockId = null): string {
    $title = $props['title'] ?? __('Table of Contents');
    $showTitle = $props['showTitle'] ?? true;
    $minLevel = $props['minLevel'] ?? 'h1';
    $maxLevel = $props['maxLevel'] ?? 'h4';
    $listStyle = $props['listStyle'] ?? 'bullet';
    $backgroundColor = $props['backgroundColor'] ?? '#f8fafc';
    $borderColor = $props['borderColor'] ?? '#e2e8f0';
    $titleColor = $props['titleColor'] ?? '#1e293b';
    $linkColor = $props['linkColor'] ?? '#635bff';
    $layoutStyles = $props['layoutStyles'] ?? [];
    $customCSS = $props['customCSS'] ?? '';
    $customClass = $props['customClass'] ?? '';

    $minLevelNum = (int) str_replace('h', '', $minLevel);
    $maxLevelNum = (int) str_replace('h', '', $maxLevel);

    // Get the headings from the allBlocks passed in props (injected by save.js)
    $headings = [];
    $allBlocks = $props['_allBlocks'] ?? [];

    // Recursively find all heading blocks
    $findHeadings = function ($blocks) use (&$findHeadings, &$headings, $minLevelNum, $maxLevelNum) {
        foreach ($blocks as $block) {
            if (! is_array($block)) {
                continue;
            }

            if (($block['type'] ?? '') === 'heading') {
                $level = $block['props']['level'] ?? 'h2';
                $levelNum = (int) str_replace('h', '', $level);

                if ($levelNum >= $minLevelNum && $levelNum <= $maxLevelNum) {
                    $text = strip_tags($block['props']['text'] ?? '');
                    if (! empty($text)) {
                        $headings[] = [
                            'level' => $levelNum,
                            'text' => $text,
                            'id' => $block['id'] ?? 'heading-' . count($headings),
                        ];
                    }
                }
            }

            // Check nested blocks in columns
            if (! empty($block['props']['children']) && is_array($block['props']['children'])) {
                foreach ($block['props']['children'] as $column) {
                    if (is_array($column)) {
                        $findHeadings($column);
                    }
                }
            }
        }
    };

    $findHeadings($allBlocks);

    // Build list style
    $listTag = $listStyle === 'number' ? 'ol' : 'ul';
    $listStyleValue = match ($listStyle) {
        'none' => 'none',
        'number' => 'decimal',
        default => 'disc',
    };
    $paddingLeft = $listStyle === 'none' ? '0' : '20px';

    // Build title HTML
    $titleHtml = '';
    if ($showTitle) {
        $titleHtml = sprintf(
            '<h4 style="color: %s; font-size: 18px; font-weight: 600; margin: 0 0 12px 0; padding-bottom: 8px; border-bottom: 1px solid %s;">%s</h4>',
            e($titleColor),
            e($borderColor),
            e($title)
        );
    }

    // Build list items with anchor links
    $listItems = '';
    if (empty($headings)) {
        $listItems = sprintf(
            '<li style="color: #94a3b8; font-style: italic;">%s</li>',
            __('No headings found.')
        );
    } else {
        foreach ($headings as $heading) {
            $indent = ($heading['level'] - $minLevelNum) * 16;
            // Generate slug that matches what heading block generates
            $slug = 'toc-' . \Illuminate\Support\Str::slug($heading['text']) . '-' . $heading['id'];

            $listItems .= sprintf(
                '<li style="margin-left: %dpx; margin-bottom: 6px; line-height: 1.6;"><a href="#%s" style="color: %s; text-decoration: none;">%s</a></li>',
                $indent,
                e($slug),
                e($linkColor),
                e($heading['text'])
            );
        }
    }

    // Build block classes
    $blockClasses = 'lb-block lb-toc';
    if (! empty($customClass)) {
        $blockClasses .= ' ' . e($customClass);
    }

    // Build inline styles
    $styles = [
        "background-color: {$backgroundColor}",
        "border: 1px solid {$borderColor}",
        'border-radius: 8px',
        'padding: 16px 20px',
        'margin-bottom: 16px',
    ];

    // Add layout styles if present
    if (! empty($layoutStyles)) {
        if (! empty($layoutStyles['margin'])) {
            $margin = $layoutStyles['margin'];
            if (isset($margin['top'])) {
                $styles[] = "margin-top: {$margin['top']}";
            }
            if (isset($margin['bottom'])) {
                $styles[] = "margin-bottom: {$margin['bottom']}";
            }
        }
    }

    if (! empty($customCSS)) {
        $styles[] = $customCSS;
    }

    $styleAttr = implode('; ', $styles);

    return sprintf(
        '<div class="%s" style="%s">
            %s
            <nav class="lb-toc-nav">
                <%s class="lb-toc-list" style="margin: 0; padding: 0; list-style: %s; padding-left: %s;">
                    %s
                </%s>
            </nav>
        </div>',
        $blockClasses,
        $styleAttr,
        $titleHtml,
        $listTag,
        $listStyleValue,
        $paddingLeft,
        $listItems,
        $listTag
    );
};
