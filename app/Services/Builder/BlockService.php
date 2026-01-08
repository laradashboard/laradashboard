<?php

declare(strict_types=1);

namespace App\Services\Builder;

use Illuminate\Support\Str;

class BlockService
{
    /**
     * Helper: Create a unique block ID
     */
    public function blockId(): string
    {
        return 'block_' . Str::random(8);
    }

    /**
     * Helper: Default layout styles for blocks
     */
    public function defaultLayoutStyles(): array
    {
        return [
            'margin' => ['top' => '', 'right' => '', 'bottom' => '', 'left' => ''],
            'padding' => ['top' => '', 'right' => '', 'bottom' => '', 'left' => ''],
        ];
    }

    /**
     * Default canvas settings for email templates.
     */
    public function getDefaultCanvasSettings(): array
    {
        return [
            'width' => '600px',
            'contentPadding' => '32px',
            'contentMargin' => '40px',
            'layoutStyles' => [
                'background' => ['color' => '#ffffff'],
                'typography' => [
                    'fontFamily' => 'Arial, sans-serif',
                    'fontSize' => '16px',
                    'color' => '#333333',
                ],
                'border' => [
                    'radius' => [
                        'topLeft' => '8px',
                        'topRight' => '8px',
                        'bottomLeft' => '8px',
                        'bottomRight' => '8px',
                    ],
                ],
            ],
        ];
    }

    /**
     * Generate complete HTML email from blocks and canvas settings.
     */
    public function generateEmailHtml(array $blocks, ?array $canvasSettings = null): string
    {
        $canvasSettings = $canvasSettings ?? $this->getDefaultCanvasSettings();
        $layoutStyles = $canvasSettings['layoutStyles'] ?? [];
        $bgColor = $layoutStyles['background']['color'] ?? '#ffffff';
        $borderRadius = $layoutStyles['border']['radius'] ?? [];
        $radiusTL = $borderRadius['topLeft'] ?? '8px';
        $radiusTR = $borderRadius['topRight'] ?? '8px';
        $radiusBL = $borderRadius['bottomLeft'] ?? '8px';
        $radiusBR = $borderRadius['bottomRight'] ?? '8px';
        $contentPadding = $canvasSettings['contentPadding'] ?? '32px';
        $contentMargin = $canvasSettings['contentMargin'] ?? '40px';
        $maxWidth = $canvasSettings['width'] ?? '600px';

        $blocksHtml = $this->parseBlocks($blocks);

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Email</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
</head>
<body style="margin: 0; padding: 0; background-color: #f4f4f4;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f4f4f4;">
        <tr>
            <td align="center" style="padding: {$contentMargin} 20px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: {$maxWidth}; background-color: {$bgColor}; border-top-left-radius: {$radiusTL}; border-top-right-radius: {$radiusTR}; border-bottom-left-radius: {$radiusBL}; border-bottom-right-radius: {$radiusBR};">
                    <tr>
                        <td style="padding: {$contentPadding};">
                            {$blocksHtml}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    /**
     * Create a complete template data array ready for database insertion.
     */
    public function createTemplateData(
        string $name,
        string $subject,
        string $type,
        string $description,
        array $blocks,
        bool $isActive = false,
        bool $isDeletable = true,
        int $createdBy = 1
    ): array {
        $canvasSettings = $this->getDefaultCanvasSettings();
        $designJson = [
            'blocks' => $blocks,
            'canvasSettings' => $canvasSettings,
            'version' => 1,
        ];

        return [
            'uuid' => (string) Str::uuid(),
            'name' => $name,
            'subject' => $subject,
            'body_html' => $this->generateEmailHtml($blocks, $canvasSettings),
            'design_json' => $designJson,
            'type' => $type,
            'description' => $description,
            'is_active' => $isActive,
            'is_deleteable' => $isDeletable,
            'created_by' => $createdBy,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function parseBlocks(array $blocks): string
    {
        $html = '';
        foreach ($blocks as $block) {
            $html .= $this->renderBlock($block);
        }
        return $html;
    }

    public function heading(string $text, string $level = 'h1', string $align = 'center', string $color = '#333333', string $fontSize = '28px'): array
    {
        return [
            'id' => $this->blockId(),
            'type' => 'heading',
            'props' => [
                'text' => $text,
                'level' => $level,
                'align' => $align,
                'color' => $color,
                'fontSize' => $fontSize,
                'fontWeight' => 'bold',
                'layoutStyles' => $this->defaultLayoutStyles(),
            ],
        ];
    }

    public function text(string $content, string $align = 'left', string $color = '#666666', string $fontSize = '16px'): array
    {
        return [
            'id' => $this->blockId(),
            'type' => 'text',
            'props' => [
                'content' => $content,
                'align' => $align,
                'color' => $color,
                'fontSize' => $fontSize,
                'lineHeight' => '1.6',
                'layoutStyles' => $this->defaultLayoutStyles(),
            ],
        ];
    }

    /**
     * Helper: Create button block
     */
    public function button(string $text, string $link = '#', string $bgColor = '#635bff', string $textColor = '#ffffff', string $align = 'center'): array
    {
        return [
            'id' => $this->blockId(),
            'type' => 'button',
            'props' => [
                'text' => $text,
                'link' => $link,
                'backgroundColor' => $bgColor,
                'textColor' => $textColor,
                'borderRadius' => '6px',
                'padding' => '14px 28px',
                'align' => $align,
                'fontSize' => '16px',
                'fontWeight' => '600',
                'layoutStyles' => $this->defaultLayoutStyles(),
            ],
        ];
    }

    /**
     * Helper: Create image block
     */
    public function image(string $src, string $alt = '', string $width = '100%', string $align = 'center', string $link = ''): array
    {
        return [
            'id' => $this->blockId(),
            'type' => 'image',
            'props' => [
                'src' => $src,
                'alt' => $alt,
                'width' => $width,
                'height' => 'auto',
                'align' => $align,
                'link' => $link,
                'layoutStyles' => $this->defaultLayoutStyles(),
            ],
        ];
    }

    /**
     * Helper: Create spacer block
     */
    public function spacer(string $height = '20px'): array
    {
        return [
            'id' => $this->blockId(),
            'type' => 'spacer',
            'props' => [
                'height' => $height,
                'layoutStyles' => $this->defaultLayoutStyles(),
            ],
        ];
    }

    /**
     * Helper: Create divider block
     */
    public function divider(string $color = '#e5e7eb', string $thickness = '1px', string $width = '100%'): array
    {
        return [
            'id' => $this->blockId(),
            'type' => 'divider',
            'props' => [
                'style' => 'solid',
                'color' => $color,
                'thickness' => $thickness,
                'width' => $width,
                'margin' => '20px 0',
                'layoutStyles' => $this->defaultLayoutStyles(),
            ],
        ];
    }

    /**
     * Helper: Create list block
     */
    public function listBlock(array $items, string $listType = 'bullet', string $color = '#666666'): array
    {
        return [
            'id' => $this->blockId(),
            'type' => 'list',
            'props' => [
                'items' => $items,
                'listType' => $listType,
                'color' => $color,
                'fontSize' => '16px',
                'iconColor' => '#635bff',
                'layoutStyles' => $this->defaultLayoutStyles(),
            ],
        ];
    }

    /**
     * Helper: Create quote block
     */
    public function quote(string $text, string $author = '', string $authorTitle = ''): array
    {
        return [
            'id' => $this->blockId(),
            'type' => 'quote',
            'props' => [
                'text' => $text,
                'author' => $author,
                'authorTitle' => $authorTitle,
                'borderColor' => '#635bff',
                'backgroundColor' => '#f8fafc',
                'textColor' => '#475569',
                'authorColor' => '#1e293b',
                'align' => 'left',
                'layoutStyles' => $this->defaultLayoutStyles(),
            ],
        ];
    }

    /**
     * Helper: Create table block
     */
    public function table(array $headers, array $rows, bool $showHeader = true): array
    {
        return [
            'id' => $this->blockId(),
            'type' => 'table',
            'props' => [
                'headers' => $headers,
                'rows' => $rows,
                'showHeader' => $showHeader,
                'headerBgColor' => '#f1f5f9',
                'headerTextColor' => '#1e293b',
                'borderColor' => '#e2e8f0',
                'cellPadding' => '12px',
                'fontSize' => '14px',
                'layoutStyles' => $this->defaultLayoutStyles(),
            ],
        ];
    }

    /**
     * Helper: Create footer block
     */
    public function footer(string $companyName = '{app_name}', string $address = '', string $email = ''): array
    {
        return [
            'id' => $this->blockId(),
            'type' => 'footer',
            'props' => [
                'companyName' => $companyName,
                'address' => $address,
                'phone' => '',
                'email' => $email,
                'unsubscribeText' => 'Unsubscribe from these emails',
                'unsubscribeUrl' => '#unsubscribe',
                'copyright' => '© {year} ' . $companyName . '. All rights reserved.',
                'textColor' => '#6b7280',
                'linkColor' => '#635bff',
                'fontSize' => '12px',
                'align' => 'center',
                'layoutStyles' => $this->defaultLayoutStyles(),
            ],
        ];
    }

    /**
     * Helper: Create social block
     */
    public function social(array $links = [], string $align = 'center'): array
    {
        return [
            'id' => $this->blockId(),
            'type' => 'social',
            'props' => [
                'align' => $align,
                'iconSize' => '32px',
                'gap' => '12px',
                'links' => array_merge([
                    'facebook' => '',
                    'twitter' => '',
                    'instagram' => '',
                    'linkedin' => '',
                    'youtube' => '',
                ], $links),
                'layoutStyles' => $this->defaultLayoutStyles(),
            ],
        ];
    }

    /**
     * Helper: Create countdown block
     */
    public function countdown(string $title = 'Sale Ends In', string $targetDate = ''): array
    {
        $date = $targetDate ?: date('Y-m-d', strtotime('+7 days'));
        return [
            'id' => $this->blockId(),
            'type' => 'countdown',
            'props' => [
                'targetDate' => $date,
                'targetTime' => '23:59',
                'title' => $title,
                'backgroundColor' => '#1e293b',
                'textColor' => '#ffffff',
                'numberColor' => '#635bff',
                'align' => 'center',
                'expiredMessage' => 'This offer has expired!',
                'layoutStyles' => $this->defaultLayoutStyles(),
            ],
        ];
    }

    /**
     * Helper: Create video block
     */
    public function video(string $thumbnailUrl = '', string $videoUrl = '', string $alt = 'Video'): array
    {
        return [
            'id' => $this->blockId(),
            'type' => 'video',
            'props' => [
                'thumbnailUrl' => $thumbnailUrl,
                'videoUrl' => $videoUrl,
                'alt' => $alt,
                'width' => '100%',
                'align' => 'center',
                'playButtonColor' => '#635bff',
                'layoutStyles' => $this->defaultLayoutStyles(),
            ],
        ];
    }

    /**
     * Render a single block to HTML
     */
    public function renderBlock(array $block): string
    {
        $type = $block['type'] ?? '';
        $props = $block['props'] ?? [];

        return match ($type) {
            'heading' => $this->renderHeading($props),
            'text' => $this->renderText($props),
            'button' => $this->renderButton($props),
            'image' => $this->renderImage($props),
            'spacer' => $this->renderSpacer($props),
            'divider' => $this->renderDivider($props),
            'list' => $this->renderList($props),
            'quote' => $this->renderQuote($props),
            'table' => $this->renderTable($props),
            'footer' => $this->renderFooter($props),
            'social' => $this->renderSocial($props),
            'countdown' => $this->renderCountdown($props),
            'video' => $this->renderVideo($props),
            default => '',
        };
    }

    public function renderHeading(array $props): string
    {
        $text = $props['text'] ?? '';
        $level = $props['level'] ?? 'h1';
        $align = $props['align'] ?? 'center';
        $color = $props['color'] ?? '#333333';
        $fontSize = $props['fontSize'] ?? '28px';
        $fontWeight = $props['fontWeight'] ?? 'bold';

        return "<{$level} style=\"text-align: {$align}; color: {$color}; font-size: {$fontSize}; font-weight: {$fontWeight}; margin: 0 0 16px 0;\">{$text}</{$level}>";
    }

    public function renderText(array $props): string
    {
        $content = $props['content'] ?? '';
        $align = $props['align'] ?? 'left';
        $color = $props['color'] ?? '#666666';
        $fontSize = $props['fontSize'] ?? '16px';
        $lineHeight = $props['lineHeight'] ?? '1.6';

        return "<div style=\"text-align: {$align}; color: {$color}; font-size: {$fontSize}; line-height: {$lineHeight};\">{$content}</div>";
    }

    public function renderButton(array $props): string
    {
        $text = $props['text'] ?? 'Click Here';
        $link = $props['link'] ?? '#';
        $bgColor = $props['backgroundColor'] ?? '#635bff';
        $textColor = $props['textColor'] ?? '#ffffff';
        $borderRadius = $props['borderRadius'] ?? '6px';
        $padding = $props['padding'] ?? '14px 28px';
        $align = $props['align'] ?? 'center';
        $fontSize = $props['fontSize'] ?? '16px';
        $fontWeight = $props['fontWeight'] ?? '600';

        return <<<HTML
            <div style="text-align: {$align}; padding: 10px 0;">
                <a href="{$link}" target="_blank" style="display: inline-block; background-color: {$bgColor}; color: {$textColor}; padding: {$padding}; border-radius: {$borderRadius}; text-decoration: none; font-size: {$fontSize}; font-weight: {$fontWeight};">{$text}</a>
            </div>
        HTML;
    }

    public function renderImage(array $props): string
    {
        $src = $props['src'] ?? '';
        $alt = $props['alt'] ?? '';
        $width = $props['width'] ?? '100%';
        $align = $props['align'] ?? 'center';
        $link = $props['link'] ?? '';

        if (empty($src)) {
            return '';
        }

        $imgHtml = "<img src=\"{$src}\" alt=\"{$alt}\" style=\"max-width: {$width}; height: auto; display: block;\" />";

        if ($link) {
            $imgHtml = "<a href=\"{$link}\" target=\"_blank\">{$imgHtml}</a>";
        }

        return "<div style=\"text-align: {$align}; padding: 10px 0;\">{$imgHtml}</div>";
    }

    public function renderSpacer(array $props): string
    {
        $height = $props['height'] ?? '20px';

        return "<div style=\"height: {$height};\"></div>";
    }

    public function renderDivider(array $props): string
    {
        $color = $props['color'] ?? '#e5e7eb';
        $thickness = $props['thickness'] ?? '1px';
        $width = $props['width'] ?? '100%';
        $margin = $props['margin'] ?? '20px 0';

        return "<hr style=\"border: none; border-top: {$thickness} solid {$color}; width: {$width}; margin: {$margin};\" />";
    }

    public function renderList(array $props): string
    {
        $items = $props['items'] ?? [];
        $listType = $props['listType'] ?? 'bullet';
        $color = $props['color'] ?? '#666666';
        $fontSize = $props['fontSize'] ?? '16px';

        if (empty($items)) {
            return '';
        }

        $tag = $listType === 'number' ? 'ol' : 'ul';
        $itemsHtml = '';
        foreach ($items as $item) {
            $itemsHtml .= "<li style=\"margin-bottom: 8px;\">{$item}</li>";
        }

        return "<{$tag} style=\"color: {$color}; font-size: {$fontSize}; line-height: 1.8; margin: 0; padding-left: 24px;\">{$itemsHtml}</{$tag}>";
    }

    public function renderQuote(array $props): string
    {
        $text = $props['text'] ?? '';
        $author = $props['author'] ?? '';
        $authorTitle = $props['authorTitle'] ?? '';
        $borderColor = $props['borderColor'] ?? '#635bff';
        $bgColor = $props['backgroundColor'] ?? '#f8fafc';
        $textColor = $props['textColor'] ?? '#475569';
        $authorColor = $props['authorColor'] ?? '#1e293b';

        $authorHtml = '';
        if ($author) {
            $titleHtml = $authorTitle ? "<span style=\"color: {$textColor}; font-size: 14px;\"> - {$authorTitle}</span>" : '';
            $authorHtml = "<p style=\"color: {$authorColor}; font-size: 14px; font-weight: 600; margin: 12px 0 0 0;\">{$author}{$titleHtml}</p>";
        }

        return <<<HTML
            <div style="padding: 20px; padding-left: 24px; background-color: {$bgColor}; border-left: 4px solid {$borderColor}; border-radius: 4px; margin: 10px 0;">
                <p style="color: {$textColor}; font-size: 16px; font-style: italic; line-height: 1.6; margin: 0;">"{$text}"</p>
                {$authorHtml}
            </div>
        HTML;
    }

    public function renderTable(array $props): string
    {
        $headers = $props['headers'] ?? [];
        $rows = $props['rows'] ?? [];
        $showHeader = $props['showHeader'] ?? true;
        $headerBgColor = $props['headerBgColor'] ?? '#f1f5f9';
        $headerTextColor = $props['headerTextColor'] ?? '#1e293b';
        $borderColor = $props['borderColor'] ?? '#e2e8f0';
        $cellPadding = $props['cellPadding'] ?? '12px';
        $fontSize = $props['fontSize'] ?? '14px';

        $headerHtml = '';
        if ($showHeader && ! empty($headers)) {
            $headerCells = '';
            foreach ($headers as $header) {
                $headerCells .= "<th style=\"padding: {$cellPadding}; text-align: left; border: 1px solid {$borderColor}; font-weight: 600;\">{$header}</th>";
            }
            $headerHtml = "<thead><tr style=\"background-color: {$headerBgColor}; color: {$headerTextColor};\">{$headerCells}</tr></thead>";
        }

        $bodyHtml = '';
        foreach ($rows as $row) {
            $cells = '';
            foreach ($row as $cell) {
                $cells .= "<td style=\"padding: {$cellPadding}; border: 1px solid {$borderColor};\">{$cell}</td>";
            }
            $bodyHtml .= "<tr>{$cells}</tr>";
        }

        return <<<HTML
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size: {$fontSize}; border-collapse: collapse; margin: 10px 0;">
                {$headerHtml}
                <tbody>{$bodyHtml}</tbody>
            </table>
        HTML;
    }

    public function renderFooter(array $props): string
    {
        $companyName = $props['companyName'] ?? '';
        $address = $props['address'] ?? '';
        $email = $props['email'] ?? '';
        $phone = $props['phone'] ?? '';
        $unsubscribeText = $props['unsubscribeText'] ?? 'Unsubscribe';
        $unsubscribeUrl = $props['unsubscribeUrl'] ?? '#unsubscribe';
        $copyright = $props['copyright'] ?? '';
        $textColor = $props['textColor'] ?? '#6b7280';
        $linkColor = $props['linkColor'] ?? '#635bff';
        $fontSize = $props['fontSize'] ?? '12px';
        $align = $props['align'] ?? 'center';

        $addressHtml = $address ? "<p style=\"color: {$textColor}; font-size: {$fontSize}; margin: 8px 0;\">{$address}</p>" : '';
        $contactHtml = '';
        if ($email || $phone) {
            $parts = [];
            if ($email) {
                $parts[] = "<a href=\"mailto:{$email}\" style=\"color: {$linkColor};\">{$email}</a>";
            }
            if ($phone) {
                $parts[] = $phone;
            }
            $contactHtml = "<p style=\"color: {$textColor}; font-size: {$fontSize}; margin: 8px 0;\">" . implode(' | ', $parts) . '</p>';
        }

        return <<<HTML
            <div style="padding: 24px 16px; text-align: {$align}; border-top: 1px solid #e5e7eb;">
                <p style="color: {$textColor}; font-size: 14px; font-weight: 600; margin: 0 0 12px 0;">{$companyName}</p>
                {$addressHtml}
                {$contactHtml}
                <p style="color: {$textColor}; font-size: {$fontSize}; margin: 16px 0 0 0;"><a href="{$unsubscribeUrl}" style="color: {$linkColor}; text-decoration: underline;">{$unsubscribeText}</a></p>
                <p style="color: {$textColor}; font-size: 11px; margin: 12px 0 0 0;">{$copyright}</p>
            </div>
        HTML;
    }

    public function renderSocial(array $props): string
    {
        $links = $props['links'] ?? [];
        $align = $props['align'] ?? 'center';
        $iconSize = $props['iconSize'] ?? '32px';
        $gap = $props['gap'] ?? '12px';

        $iconsHtml = '';
        $socialIcons = [
            'facebook' => 'https://cdn-icons-png.flaticon.com/512/124/124010.png',
            'twitter' => 'https://cdn-icons-png.flaticon.com/512/124/124021.png',
            'instagram' => 'https://cdn-icons-png.flaticon.com/512/174/174855.png',
            'linkedin' => 'https://cdn-icons-png.flaticon.com/512/174/174857.png',
            'youtube' => 'https://cdn-icons-png.flaticon.com/512/174/174883.png',
        ];

        foreach ($links as $platform => $url) {
            if ($url && isset($socialIcons[$platform])) {
                $icon = $socialIcons[$platform];
                $iconsHtml .= "<a href=\"{$url}\" target=\"_blank\" style=\"display: inline-block; margin: 0 {$gap};\"><img src=\"{$icon}\" alt=\"{$platform}\" width=\"{$iconSize}\" height=\"{$iconSize}\" style=\"border-radius: 4px;\" /></a>";
            }
        }

        if (empty($iconsHtml)) {
            return '';
        }

        return "<div style=\"text-align: {$align}; padding: 20px 0;\">{$iconsHtml}</div>";
    }

    public function renderCountdown(array $props): string
    {
        $title = $props['title'] ?? 'Sale Ends In';
        $targetDate = $props['targetDate'] ?? '';
        $bgColor = $props['backgroundColor'] ?? '#1e293b';
        $textColor = $props['textColor'] ?? '#ffffff';
        $numberColor = $props['numberColor'] ?? '#635bff';
        $align = $props['align'] ?? 'center';

        // Static countdown display (actual countdown is JS-based)
        return <<<HTML
            <div style="background-color: {$bgColor}; padding: 24px; padding-top: 10px; border-radius: 8px; text-align: {$align}; margin: 10px 0;">
                <p style="color: {$textColor}; font-size: 16px; font-weight: 600; margin: 0 0 16px 0;">{$title}</p>
                <div style="display: inline-flex; gap: 16px;">
                    <div style="text-align: center;">
                        <span style="color: {$numberColor}; font-size: 32px; font-weight: bold;">00</span>
                        <p style="color: {$textColor}; font-size: 12px; margin: 4px 0 0 0;">Days</p>
                    </div>
                    <div style="text-align: center;">
                        <span style="color: {$numberColor}; font-size: 32px; font-weight: bold;">00</span>
                        <p style="color: {$textColor}; font-size: 12px; margin: 4px 0 0 0;">Hours</p>
                    </div>
                    <div style="text-align: center;">
                        <span style="color: {$numberColor}; font-size: 32px; font-weight: bold;">00</span>
                        <p style="color: {$textColor}; font-size: 12px; margin: 4px 0 0 0;">Minutes</p>
                    </div>
                    <div style="text-align: center;">
                        <span style="color: {$numberColor}; font-size: 32px; font-weight: bold;">00</span>
                        <p style="color: {$textColor}; font-size: 12px; margin: 4px 0 0 0;">Seconds</p>
                    </div>
                </div>
            </div>
        HTML;
    }

    public function renderVideo(array $props): string
    {
        $thumbnailUrl = $props['thumbnailUrl'] ?? '';
        $videoUrl = $props['videoUrl'] ?? '';
        $alt = $props['alt'] ?? 'Video';
        $width = $props['width'] ?? '100%';
        $align = $props['align'] ?? 'center';
        $playButtonColor = $props['playButtonColor'] ?? '#635bff';

        if (empty($thumbnailUrl)) {
            // Placeholder for video
            return <<<HTML
                <div style="text-align: {$align}; padding: 10px 0;">
                    <div style="background-color: #1e293b; padding: 60px 20px; border-radius: 8px; text-align: center;">
                        <div style="width: 60px; height: 60px; background-color: {$playButtonColor}; border-radius: 50%; margin: 0 auto; display: flex; align-items: center; justify-content: center;">
                            <span style="color: #ffffff; font-size: 24px;">▶</span>
                        </div>
                        <p style="color: #94a3b8; font-size: 14px; margin: 16px 0 0 0;">{$alt}</p>
                    </div>
                </div>
            HTML;
        }

        $linkWrapper = $videoUrl ? "href=\"{$videoUrl}\" target=\"_blank\"" : '';

        return <<<HTML
            <div style="text-align: {$align}; padding: 10px 0;">
                <a {$linkWrapper} style="display: block; position: relative; text-decoration: none;">
                    <img src="{$thumbnailUrl}" alt="{$alt}" style="max-width: {$width}; height: auto; display: block; border-radius: 8px;" />
                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 60px; height: 60px; background-color: {$playButtonColor}; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <span style="color: #ffffff; font-size: 24px;">▶</span>
                    </div>
                </a>
            </div>
        HTML;
    }
}
