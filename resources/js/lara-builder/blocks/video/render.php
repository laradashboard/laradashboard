<?php

/**
 * Video Block - Server-side Renderer
 *
 * Email: thumbnail image with play link.
 * Page: embedded video or thumbnail with click-to-play.
 */

return function (array $props, string $context = 'page', ?string $blockId = null): string {
    $videoUrl = $props['videoUrl'] ?? '';
    $thumbnailUrl = $props['thumbnailUrl'] ?? $props['thumbnail'] ?? '';
    $alt = $props['alt'] ?? 'Video';
    $width = $props['width'] ?? '100%';
    $align = $props['align'] ?? 'center';
    $playButtonColor = $props['playButtonColor'] ?? '#635bff';
    $layoutStyles = $props['layoutStyles'] ?? [];

    // Parse video URL for platform detection
    $vidInfo = null;
    if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/|youtube\.com\/shorts\/)([a-zA-Z0-9_-]{11})/', $videoUrl, $ytMatch)) {
        $vidInfo = [
            'platform' => 'youtube',
            'id' => $ytMatch[1],
            'thumbnail' => "https://img.youtube.com/vi/{$ytMatch[1]}/maxresdefault.jpg",
            'color' => '#FF0000',
        ];
    } elseif (preg_match('/dailymotion\.com\/video\/([a-zA-Z0-9]+)/', $videoUrl, $dmMatch)) {
        $vidInfo = [
            'platform' => 'dailymotion',
            'id' => $dmMatch[1],
            'thumbnail' => "https://www.dailymotion.com/thumbnail/video/{$dmMatch[1]}",
            'color' => '#00AAFF',
        ];
    }

    if ($context === 'email') {
        // Determine thumbnail
        $thumb = $thumbnailUrl;
        if (empty($thumb) && $vidInfo) {
            $thumb = $vidInfo['thumbnail'];
        }
        if (empty($thumb)) {
            $thumb = 'https://via.placeholder.com/600x340/1a1a2e/ffffff?text=Video';
        }

        $btnColor = $playButtonColor;
        if ($vidInfo) {
            $btnColor = $vidInfo['color'];
        }

        $styles = ["text-align: {$align}"];

        $layoutCSS = \App\Helpers\EmailStyleHelper::buildLayoutStyles($layoutStyles);
        if ($layoutCSS) {
            $styles[] = $layoutCSS;
        }

        $imgHtml = sprintf(
            '<img src="%s" alt="%s" style="width: 100%%; height: auto; display: block; border-radius: 8px; background-color: #1a1a2e;" />',
            e($thumb),
            e($alt)
        );

        $linkHtml = ! empty($videoUrl)
            ? sprintf('<a href="%s" target="_blank" style="display: block; text-decoration: none;">%s</a>', e($videoUrl), $imgHtml)
            : $imgHtml;

        return sprintf(
            '<div style="%s"><div style="position: relative; display: inline-block; max-width: %s; width: 100%%;">%s</div></div>',
            e(implode('; ', $styles)),
            e($width),
            $linkHtml
        );
    }

    // Page context - simplified server rendering (thumbnail with link)
    $customClass = $props['customClass'] ?? '';
    $blockClasses = 'lb-block lb-video';
    if (! empty($customClass)) {
        $blockClasses .= ' ' . e($customClass);
    }

    $justifyContent = match ($align) {
        'left' => 'flex-start',
        'right' => 'flex-end',
        default => 'center',
    };

    if (! empty($thumbnailUrl) || $vidInfo) {
        $thumb = $thumbnailUrl ?: ($vidInfo['thumbnail'] ?? '');
        $imgHtml = sprintf(
            '<img src="%s" alt="%s" style="width: 100%%; height: auto; display: block; border-radius: 8px;" />',
            e($thumb),
            e($alt)
        );

        $linkedImg = ! empty($videoUrl)
            ? sprintf('<a href="%s" target="_blank" rel="noopener noreferrer" style="display: block;">%s</a>', e($videoUrl), $imgHtml)
            : $imgHtml;

        return sprintf(
            '<div class="%s" style="display: flex; justify-content: %s;"><div style="position: relative; max-width: %s; width: 100%%;">%s</div></div>',
            e($blockClasses),
            $justifyContent,
            e($width),
            $linkedImg
        );
    }

    // Fallback
    return sprintf(
        '<div class="%s" style="display: flex; justify-content: %s;"><a href="%s" target="_blank" rel="noopener noreferrer">Watch Video</a></div>',
        e($blockClasses),
        $justifyContent,
        e($videoUrl)
    );
};
