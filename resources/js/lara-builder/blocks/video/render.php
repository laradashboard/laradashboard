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

    // Parse video URL for platform detection (keep in sync with save.js parseVideoUrl)
    $vidInfo = null;
    if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/|youtube\.com\/shorts\/)([a-zA-Z0-9_-]{11})/', $videoUrl, $ytMatch)) {
        $vidInfo = [
            'platform' => 'youtube',
            'id' => $ytMatch[1],
            'thumbnail' => "https://img.youtube.com/vi/{$ytMatch[1]}/maxresdefault.jpg",
            'color' => '#FF0000',
            'embedUrl' => "https://www.youtube.com/embed/{$ytMatch[1]}?rel=0",
        ];
    } elseif (preg_match('/vimeo\.com\/(\d+)/', $videoUrl, $vimeoMatch)) {
        $vidInfo = [
            'platform' => 'vimeo',
            'id' => $vimeoMatch[1],
            'thumbnail' => null,
            'color' => '#1AB7EA',
            'embedUrl' => "https://player.vimeo.com/video/{$vimeoMatch[1]}",
        ];
    } elseif (preg_match('/(?:dailymotion\.com\/video\/|dai\.ly\/)([a-zA-Z0-9]+)/', $videoUrl, $dmMatch)) {
        $vidInfo = [
            'platform' => 'dailymotion',
            'id' => $dmMatch[1],
            'thumbnail' => "https://www.dailymotion.com/thumbnail/video/{$dmMatch[1]}",
            'color' => '#00AAFF',
            'embedUrl' => "https://www.dailymotion.com/embed/video/{$dmMatch[1]}",
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

    // Page context - embed the player (matches save.js page output)
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

    $frameWrapper = static fn (string $media): string => sprintf(
        '<div class="%s" style="display: flex; justify-content: %s;"><div style="position: relative; max-width: %s; width: 100%%;"><div style="position: relative; padding-bottom: 56.25%%; height: 0; overflow: hidden; border-radius: 8px; background: #000;">%s</div></div></div>',
        e($blockClasses),
        $justifyContent,
        e($width),
        $media
    );

    if ($vidInfo && ! empty($vidInfo['embedUrl'])) {
        return $frameWrapper(sprintf(
            '<iframe src="%s" title="%s" loading="lazy" style="position: absolute; top: 0; left: 0; width: 100%%; height: 100%%; border: 0;" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>',
            e($vidInfo['embedUrl']),
            e($alt)
        ));
    }

    if (preg_match('/\.(mp4|webm|ogg|mov|m4v)(\?.*)?$/i', $videoUrl)) {
        return $frameWrapper(sprintf(
            '<video src="%s"%s controls preload="metadata" style="position: absolute; top: 0; left: 0; width: 100%%; height: 100%%;"></video>',
            e($videoUrl),
            $thumbnailUrl ? ' poster="'.e($thumbnailUrl).'"' : ''
        ));
    }

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
