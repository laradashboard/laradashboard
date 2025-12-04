/**
 * EmailAdapter - Email-safe HTML Output Adapter
 *
 * Generates email-compatible HTML using tables and inline styles.
 * This adapter migrates and extends the existing generateHtml.js logic.
 */

import { BaseAdapter } from './BaseAdapter';
import { LaraHooks } from '../hooks-system/LaraHooks';
import { BuilderHooks, getBlockHook } from '../hooks-system/HookNames';
import { blockRegistry } from '../registry/BlockRegistry';
import { layoutStylesToInlineCSS } from '../components/LayoutStylesSection';

export class EmailAdapter extends BaseAdapter {
    constructor() {
        super('email');
    }

    /**
     * Get default canvas settings for email
     */
    getDefaultSettings() {
        return {
            width: '700px',
            backgroundColor: '#f3f4f6',
            backgroundImage: '',
            backgroundSize: 'cover',
            backgroundPosition: 'center',
            backgroundRepeat: 'no-repeat',
            contentBackgroundColor: '#ffffff',
            contentBackgroundImage: '',
            contentBackgroundSize: 'cover',
            contentBackgroundPosition: 'center',
            contentBackgroundRepeat: 'no-repeat',
            contentPadding: '32px',
            contentMargin: '40px',
            contentBorderWidth: '0px',
            contentBorderColor: '#e5e7eb',
            contentBorderRadius: '8px',
            fontFamily: 'Arial, sans-serif',
        };
    }

    /**
     * Generate HTML for a single block
     */
    generateBlockHtml(block, options = {}) {
        const { type, props } = block;

        // Check if block has a custom HTML generator registered
        const blockDef = blockRegistry.get(type);
        if (blockDef?.htmlGenerator?.email) {
            return blockDef.htmlGenerator.email(props, options);
        }

        // Apply filter for block HTML
        let html = this._generateDefaultBlockHtml(block, options);
        html = LaraHooks.applyFilters(getBlockHook(BuilderHooks.FILTER_HTML_BLOCK, type), html, props, options);
        html = LaraHooks.applyFilters(`builder.email.block.${type}`, html, props, options);

        return html;
    }

    /**
     * Generate layout wrapper with inline styles (email-safe using tables)
     */
    _wrapWithLayoutStyles(html, layoutStyles) {
        const layoutCSS = layoutStylesToInlineCSS(layoutStyles);
        if (!layoutCSS) {
            return html;
        }
        // For email compatibility, use a table-based wrapper
        return `<table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="${layoutCSS}">${html}</td></tr></table>`;
    }

    /**
     * Generate default block HTML (migrated from generateHtml.js)
     */
    _generateDefaultBlockHtml(block, options = {}) {
        const { type, props } = block;
        const { previewMode = false } = options;
        const layoutStyles = props?.layoutStyles;

        let html = '';
        switch (type) {
            case 'heading':
                html = `<${props.level} style="text-align: ${props.align}; color: ${props.color}; font-size: ${props.fontSize}; font-weight: ${props.fontWeight}; margin: 0 0 16px 0;">${props.text}</${props.level}>`;
                break;

            case 'text':
                html = `<div style="text-align: ${props.align}; color: ${props.color}; font-size: ${props.fontSize}; line-height: ${props.lineHeight};">${props.content}</div>`;
                break;

            case 'text-editor':
                html = `<div style="text-align: ${props.align || 'left'}; color: ${props.color || '#333333'}; font-size: ${props.fontSize || '16px'}; line-height: ${props.lineHeight || '1.6'};">${props.content || ''}</div>`;
                break;

            case 'image':
                html = this._generateImageHtml(props);
                break;

            case 'button':
                html = this._generateButtonHtml(props);
                break;

            case 'divider':
                html = `<hr style="border: none; border-top: ${props.thickness} ${props.style} ${props.color}; width: ${props.width}; margin: ${props.margin};" />`;
                break;

            case 'spacer':
                html = `<div style="height: ${props.height};"></div>`;
                break;

            case 'columns':
                html = this._generateColumnsHtml(props, options);
                break;

            case 'social':
                html = this._generateSocialHtml(props);
                break;

            case 'html':
                html = props.code || '';
                break;

            case 'quote':
                html = this._generateQuoteHtml(props);
                break;

            case 'list':
                html = this._generateListHtml(props);
                break;

            case 'video':
                html = this._generateVideoHtml(props, previewMode);
                break;

            case 'footer':
                html = this._generateFooterHtml(props);
                break;

            case 'countdown':
                html = this._generateCountdownHtml(props);
                break;

            case 'table':
                html = this._generateTableHtml(props);
                break;

            default:
                html = '';
        }

        // Wrap with layout styles if present
        return this._wrapWithLayoutStyles(html, layoutStyles);
    }

    _generateImageHtml(props) {
        const isCustomWidth = props.width === 'custom' && props.customWidth;
        const isCustomHeight = props.height === 'custom' && props.customHeight;
        const imgWidth = isCustomWidth ? props.customWidth : (props.width || '100%');
        const imgHeight = isCustomHeight ? props.customHeight : (props.height || 'auto');
        const imgStyle = `max-width: ${imgWidth};${isCustomWidth ? ` width: ${props.customWidth};` : ''} height: ${imgHeight}; display: block; margin: 0 auto;${imgHeight !== 'auto' ? ' object-fit: cover;' : ''}`;
        const img = `<img src="${props.src}" alt="${props.alt}" style="${imgStyle}" />`;

        return props.link
            ? `<a href="${props.link}" target="_blank" style="display: block; text-align: ${props.align};">${img}</a>`
            : `<div style="text-align: ${props.align};">${img}</div>`;
    }

    _generateButtonHtml(props) {
        return `
            <div style="text-align: ${props.align}; padding: 10px 0;">
                <a href="${props.link}" target="_blank" style="display: inline-block; background-color: ${props.backgroundColor}; color: ${props.textColor}; padding: ${props.padding}; border-radius: ${props.borderRadius}; text-decoration: none; font-size: ${props.fontSize}; font-weight: ${props.fontWeight};">${props.text}</a>
            </div>
        `;
    }

    _generateColumnsHtml(props, options) {
        const columnWidth = `${100 / props.columns}%`;
        const columnsHtml = props.children.map((columnBlocks, index) => {
            const columnContent = columnBlocks.map(b => this.generateBlockHtml(b, options)).join('');
            return `<td style="width: ${columnWidth}; vertical-align: top; padding: 0 ${index < props.columns - 1 ? props.gap : '0'} 0 0;">${columnContent || '&nbsp;'}</td>`;
        }).join('');

        return `
            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>${columnsHtml}</tr>
            </table>
        `;
    }

    _generateSocialHtml(props) {
        const socialIcons = {
            facebook: 'https://cdn-icons-png.flaticon.com/32/733/733547.png',
            twitter: 'https://cdn-icons-png.flaticon.com/32/733/733579.png',
            instagram: 'https://cdn-icons-png.flaticon.com/32/2111/2111463.png',
            linkedin: 'https://cdn-icons-png.flaticon.com/32/733/733561.png',
            youtube: 'https://cdn-icons-png.flaticon.com/32/733/733646.png',
        };
        const socialIconSize = parseInt(props.iconSize) || 32;
        const socialGap = parseInt(props.gap) / 2 || 6;
        const socialLinksHtml = Object.entries(props.links || {})
            .filter(([, url]) => url)
            .map(([platform, url]) => `<a href="${url}" target="_blank" style="display: inline-block; margin: 0 ${socialGap}px;"><img src="${socialIcons[platform]}" alt="${platform}" width="${socialIconSize}" height="${socialIconSize}" style="border: 0;" /></a>`)
            .join('');

        return socialLinksHtml ? `<div style="text-align: ${props.align || 'center'}; padding: 10px 0;">${socialLinksHtml}</div>` : '';
    }

    _generateQuoteHtml(props) {
        return `
            <div style="padding: 20px; padding-left: 24px; background-color: ${props.backgroundColor || '#f8fafc'}; border-left: 4px solid ${props.borderColor || '#635bff'}; text-align: ${props.align || 'left'}; border-radius: 4px; margin: 10px 0;">
                <p style="color: ${props.textColor || '#475569'}; font-size: 16px; font-style: italic; line-height: 1.6; margin: 0 0 12px 0;">"${props.text}"</p>
                ${props.author ? `<p style="color: ${props.authorColor || '#1e293b'}; font-size: 14px; font-weight: 600; margin: 0;">${props.author}</p>` : ''}
                ${props.authorTitle ? `<p style="color: ${props.textColor || '#475569'}; font-size: 12px; margin: 0;">${props.authorTitle}</p>` : ''}
            </div>
        `;
    }

    _generateListHtml(props) {
        const listItems = (props.items || []).map(item => {
            if (props.listType === 'check') {
                return `<tr><td style="vertical-align: top; padding-right: 8px; color: ${props.iconColor || '#635bff'};">&#10003;</td><td style="color: ${props.color || '#333333'}; font-size: ${props.fontSize || '16px'}; padding-bottom: 8px;">${item}</td></tr>`;
            }
            return `<li style="margin-bottom: 8px;">${item}</li>`;
        }).join('');

        if (props.listType === 'check') {
            return `<table style="color: ${props.color || '#333333'}; font-size: ${props.fontSize || '16px'}; line-height: 1.6;">${listItems}</table>`;
        }
        const listTag = props.listType === 'number' ? 'ol' : 'ul';
        return `<${listTag} style="color: ${props.color || '#333333'}; font-size: ${props.fontSize || '16px'}; line-height: 1.8; margin: 0; padding-left: 24px;">${listItems}</${listTag}>`;
    }

    _generateVideoHtml(props, previewMode) {
        const isDirectVideoFile = (url) => {
            if (!url) return false;
            const videoExtensions = /\.(mp4|webm|ogg|mov|avi|m4v)(\?.*)?$/i;
            return videoExtensions.test(url);
        };

        const parseVideoUrl = (url) => {
            if (!url) return null;
            const ytMatch = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/|youtube\.com\/shorts\/)([a-zA-Z0-9_-]{11})/);
            if (ytMatch) return { platform: 'youtube', id: ytMatch[1], thumbnail: `https://img.youtube.com/vi/${ytMatch[1]}/maxresdefault.jpg`, color: '#FF0000', embedUrl: `https://www.youtube.com/embed/${ytMatch[1]}` };
            const vimeoMatch = url.match(/vimeo\.com\/(\d+)/);
            if (vimeoMatch) return { platform: 'vimeo', id: vimeoMatch[1], thumbnail: null, color: '#1AB7EA', embedUrl: `https://player.vimeo.com/video/${vimeoMatch[1]}` };
            const dmMatch = url.match(/(?:dailymotion\.com\/video\/|dai\.ly\/)([a-zA-Z0-9]+)/);
            if (dmMatch) return { platform: 'dailymotion', id: dmMatch[1], thumbnail: `https://www.dailymotion.com/thumbnail/video/${dmMatch[1]}`, color: '#00AAFF', embedUrl: `https://www.dailymotion.com/embed/video/${dmMatch[1]}` };
            return null;
        };

        const isDirectVideo = isDirectVideoFile(props.videoUrl);
        const vidInfo = parseVideoUrl(props.videoUrl);

        // Preview mode uses actual video/iframe elements
        if (previewMode) {
            if (isDirectVideo && !props.thumbnailUrl) {
                return `
                    <div style="text-align: ${props.align || 'center'};">
                        <video src="${props.videoUrl}" controls style="max-width: ${props.width || '100%'}; width: 100%; height: auto; display: inline-block; border-radius: 8px; background-color: #1a1a2e;" preload="metadata"></video>
                    </div>
                `;
            } else if (vidInfo?.embedUrl && !props.thumbnailUrl) {
                return `
                    <div style="text-align: ${props.align || 'center'};">
                        <div style="position: relative; max-width: ${props.width || '100%'}; width: 100%; display: inline-block;">
                            <div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 8px;">
                                <iframe src="${vidInfo.embedUrl}" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;" allowfullscreen></iframe>
                            </div>
                        </div>
                    </div>
                `;
            }
        }

        // Email HTML uses clickable thumbnail
        let vidThumbnail;
        if (props.thumbnailUrl) {
            vidThumbnail = props.thumbnailUrl;
        } else if (vidInfo?.thumbnail) {
            vidThumbnail = vidInfo.thumbnail;
        } else if (isDirectVideo) {
            vidThumbnail = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='600' height='340' viewBox='0 0 600 340'%3E%3Crect fill='%231a1a2e' width='600' height='340'/%3E%3Ctext fill='%23ffffff' font-family='Arial' font-size='16' x='50%25' y='60%25' text-anchor='middle'%3EClick to play video%3C/text%3E%3C/svg%3E";
        } else {
            vidThumbnail = 'https://via.placeholder.com/600x340/1a1a2e/ffffff?text=Video';
        }

        const vidPlayColor = props.playButtonColor || vidInfo?.color || (isDirectVideo ? '#635bff' : '#ff0000');
        const videoThumbnail = `
            <div style="position: relative; display: inline-block; max-width: ${props.width || '100%'}; width: 100%;">
                <a href="${props.videoUrl}" target="_blank" style="display: block; text-decoration: none;">
                    <img src="${vidThumbnail}" alt="${props.alt || 'Video thumbnail'}" style="width: 100%; height: auto; display: block; border-radius: 8px; background-color: #1a1a2e;" />
                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 68px; height: 68px; background-color: ${vidPlayColor}; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <span style="width: 0; height: 0; border-top: 12px solid transparent; border-bottom: 12px solid transparent; border-left: 20px solid white; margin-left: 4px;"></span>
                    </div>
                </a>
            </div>
        `;
        return `<div style="text-align: ${props.align || 'center'};">${videoThumbnail}</div>`;
    }

    _generateFooterHtml(props) {
        return `
            <div style="padding: 24px 16px; text-align: ${props.align || 'center'}; border-top: 1px solid #e5e7eb;">
                ${props.companyName ? `<p style="color: ${props.textColor || '#6b7280'}; font-size: 14px; font-weight: 600; margin: 0 0 12px 0;">${props.companyName}</p>` : ''}
                ${props.address ? `<p style="color: ${props.textColor || '#6b7280'}; font-size: ${props.fontSize || '12px'}; margin: 0 0 8px 0;">${props.address}</p>` : ''}
                ${(props.phone || props.email) ? `<p style="color: ${props.textColor || '#6b7280'}; font-size: ${props.fontSize || '12px'}; margin: 0 0 8px 0;">${props.phone || ''}${props.phone && props.email ? ' | ' : ''}${props.email ? `<a href="mailto:${props.email}" style="color: ${props.linkColor || '#635bff'}; text-decoration: underline;">${props.email}</a>` : ''}</p>` : ''}
                ${props.unsubscribeText ? `<p style="color: ${props.textColor || '#6b7280'}; font-size: ${props.fontSize || '12px'}; margin: 16px 0 0 0;"><a href="${props.unsubscribeUrl || '#'}" style="color: ${props.linkColor || '#635bff'}; text-decoration: underline;">${props.unsubscribeText}</a></p>` : ''}
                ${props.copyright ? `<p style="color: ${props.textColor || '#6b7280'}; font-size: 11px; margin: 12px 0 0 0;">${props.copyright}</p>` : ''}
            </div>
        `;
    }

    _generateCountdownHtml(props) {
        const countdownTargetDate = props.targetDate || new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
        const countdownTargetTime = props.targetTime || '23:59';
        const targetDateTime = `${countdownTargetDate}T${countdownTargetTime}:00`;
        const targetDate = new Date(targetDateTime);
        const now = new Date();
        const diff = Math.max(0, targetDate - now);
        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff / (1000 * 60 * 60)) % 24);
        const minutes = Math.floor((diff / 1000 / 60) % 60);
        const seconds = Math.floor((diff / 1000) % 60);

        if (diff <= 0 && props.expiredMessage) {
            return `
                <div style="padding: 24px; background-color: ${props.backgroundColor || '#1e293b'}; border-radius: 8px; text-align: ${props.align || 'center'};">
                    <p style="color: ${props.textColor || '#ffffff'}; font-size: 18px; font-weight: 600; margin: 0;">${props.expiredMessage}</p>
                </div>
            `;
        }

        return `
            <div style="padding: 24px; background-color: ${props.backgroundColor || '#1e293b'}; border-radius: 8px; text-align: ${props.align || 'center'};">
                ${props.title ? `<p style="color: ${props.textColor || '#ffffff'}; font-size: 18px; font-weight: 600; margin: 0 0 16px 0;">${props.title}</p>` : ''}
                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td align="center">
                            <table cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="text-align: center; padding: 0 12px;">
                                        <div style="background-color: rgba(255,255,255,0.1); border-radius: 8px; padding: 12px 16px;">
                                            <span style="color: ${props.numberColor || '#635bff'}; font-size: 36px; font-weight: 700; display: block;">${String(days).padStart(2, '0')}</span>
                                            <span style="color: ${props.textColor || '#ffffff'}; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">Days</span>
                                        </div>
                                    </td>
                                    <td style="color: ${props.numberColor || '#635bff'}; font-size: 28px; font-weight: 700; padding: 0 4px;">:</td>
                                    <td style="text-align: center; padding: 0 12px;">
                                        <div style="background-color: rgba(255,255,255,0.1); border-radius: 8px; padding: 12px 16px;">
                                            <span style="color: ${props.numberColor || '#635bff'}; font-size: 36px; font-weight: 700; display: block;">${String(hours).padStart(2, '0')}</span>
                                            <span style="color: ${props.textColor || '#ffffff'}; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">Hours</span>
                                        </div>
                                    </td>
                                    <td style="color: ${props.numberColor || '#635bff'}; font-size: 28px; font-weight: 700; padding: 0 4px;">:</td>
                                    <td style="text-align: center; padding: 0 12px;">
                                        <div style="background-color: rgba(255,255,255,0.1); border-radius: 8px; padding: 12px 16px;">
                                            <span style="color: ${props.numberColor || '#635bff'}; font-size: 36px; font-weight: 700; display: block;">${String(minutes).padStart(2, '0')}</span>
                                            <span style="color: ${props.textColor || '#ffffff'}; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">Mins</span>
                                        </div>
                                    </td>
                                    <td style="color: ${props.numberColor || '#635bff'}; font-size: 28px; font-weight: 700; padding: 0 4px;">:</td>
                                    <td style="text-align: center; padding: 0 12px;">
                                        <div style="background-color: rgba(255,255,255,0.1); border-radius: 8px; padding: 12px 16px;">
                                            <span style="color: ${props.numberColor || '#635bff'}; font-size: 36px; font-weight: 700; display: block;">${String(seconds).padStart(2, '0')}</span>
                                            <span style="color: ${props.textColor || '#ffffff'}; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">Secs</span>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
        `;
    }

    _generateTableHtml(props) {
        const tableHeaders = (props.headers || []).map(header =>
            `<th style="background-color: ${props.headerBgColor || '#f1f5f9'}; color: ${props.headerTextColor || '#1e293b'}; padding: ${props.cellPadding || '12px'}; text-align: left; font-weight: 600; border-bottom: 2px solid ${props.borderColor || '#e2e8f0'};">${header}</th>`
        ).join('');
        const tableRows = (props.rows || []).map(row =>
            `<tr>${row.map(cell => `<td style="padding: ${props.cellPadding || '12px'}; border-bottom: 1px solid ${props.borderColor || '#e2e8f0'}; color: #374151;">${cell}</td>`).join('')}</tr>`
        ).join('');

        return `
            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="font-size: ${props.fontSize || '14px'}; border-collapse: collapse;">
                ${props.showHeader && tableHeaders ? `<thead><tr>${tableHeaders}</tr></thead>` : ''}
                <tbody>${tableRows}</tbody>
            </table>
        `;
    }

    /**
     * Wrap the final HTML output
     */
    wrapOutput(content, settings) {
        // Get layout styles from settings (same format as blocks)
        const layoutStyles = layoutStylesToInlineCSS(settings.layoutStyles || {});

        // Basic settings
        const maxWidth = settings.width || '600px';
        const contentPadding = settings.contentPadding || '40px';
        const contentMargin = settings.contentMargin || '40px';

        // Extract values from layoutStyles or use defaults
        const background = settings.layoutStyles?.background || {};
        const typography = settings.layoutStyles?.typography || {};
        const border = settings.layoutStyles?.border || {};
        const boxShadow = settings.layoutStyles?.boxShadow || {};

        // Outer background (simplified)
        let outerBgStyle = 'background-color: #f4f4f4;';

        // Content background
        let contentBgStyle = `background-color: ${background.color || '#ffffff'};`;
        if (background.image) {
            contentBgStyle += ` background-image: url('${background.image}'); background-size: ${background.size || 'cover'}; background-position: ${background.position || 'center'}; background-repeat: ${background.repeat || 'no-repeat'};`;
        }

        // Font family
        const fontFamily = typography.fontFamily || 'Arial, sans-serif';

        // Border
        const borderWidth = border.width?.top || '0px';
        const borderColor = border.color || '#e5e7eb';
        const borderStyle = borderWidth !== '0px' ? `border: ${borderWidth} ${border.style || 'solid'} ${borderColor};` : '';

        // Border radius
        const contentBorderRadius = border.radius?.topLeft || '8px';

        // Box shadow
        let boxShadowStyle = '';
        if (boxShadow.blur && boxShadow.blur !== '0px') {
            const inset = boxShadow.inset ? 'inset ' : '';
            boxShadowStyle = `box-shadow: ${inset}${boxShadow.x || '0px'} ${boxShadow.y || '0px'} ${boxShadow.blur || '0px'} ${boxShadow.spread || '0px'} ${boxShadow.color || 'rgba(0, 0, 0, 0.1)'};`;
        }

        return `
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
    <style>
        body { margin: 0; padding: 0; font-family: ${fontFamily}; }
        table { border-collapse: collapse; }
        img { border: 0; line-height: 100%; outline: none; text-decoration: none; }
        a { color: inherit; }
    </style>
</head>
<body style="margin: 0; padding: 0; ${outerBgStyle}">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="${outerBgStyle}">
        <tr>
            <td align="center" style="padding: ${contentMargin} 20px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: ${maxWidth}; ${contentBgStyle} border-radius: ${contentBorderRadius}; ${borderStyle} ${boxShadowStyle}">
                    <tr>
                        <td style="padding: ${contentPadding};">
                            ${content}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
        `.trim();
    }
}

export default EmailAdapter;
