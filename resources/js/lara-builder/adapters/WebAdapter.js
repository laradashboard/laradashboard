/**
 * WebAdapter - Modern Web HTML Output Adapter
 *
 * Generates modern HTML5 output with CSS classes for web pages.
 * Uses semantic HTML, flexbox/grid layouts, and native video embeds.
 */

import { BaseAdapter } from './BaseAdapter';
import { LaraHooks } from '../hooks-system/LaraHooks';
import { BuilderHooks, getBlockHook } from '../hooks-system/HookNames';
import { blockRegistry } from '../registry/BlockRegistry';
import { layoutStylesToInlineCSS } from '../components/LayoutStylesSection';

/**
 * Get block class name for consistent naming
 * @param {string} type - Block type (e.g., 'image', 'video', 'heading')
 * @returns {string} - CSS class name (e.g., 'lb-image', 'lb-video', 'lb-heading')
 */
const getBlockClass = (type) => `lb-${type.toLowerCase()}`;

/**
 * Build full class string for a block
 * @param {string} type - Block type
 * @param {object} props - Block props (may contain customClass)
 * @returns {string} - Full class string
 */
const buildBlockClasses = (type, props = {}) => {
    const classes = ['lb-block', getBlockClass(type)];
    if (props.customClass) {
        classes.push(props.customClass);
    }
    return classes.join(' ');
};

export class WebAdapter extends BaseAdapter {
    constructor() {
        super('page');
    }

    /**
     * Get default canvas settings for web pages
     */
    getDefaultSettings() {
        return {
            width: '100%',
            maxWidth: '1200px',
            backgroundColor: '#ffffff',
            contentPadding: '24px',
            fontFamily: 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
            fontSize: '16px',
            lineHeight: '1.6',
            textColor: '#1f2937',
        };
    }

    /**
     * Generate HTML for a single block
     */
    generateBlockHtml(block, options = {}) {
        const { type, props } = block;

        // Check if block has a custom HTML generator registered
        const blockDef = blockRegistry.get(type);
        if (blockDef?.htmlGenerator?.page) {
            return blockDef.htmlGenerator.page(props, options);
        }

        // Apply filter for block HTML
        let html = this._generateDefaultBlockHtml(block, options);
        html = LaraHooks.applyFilters(getBlockHook(BuilderHooks.FILTER_HTML_BLOCK, type), html, props, options);
        html = LaraHooks.applyFilters(`builder.page.block.${type}`, html, props, options);

        return html;
    }

    /**
     * Generate layout wrapper with inline styles and custom CSS
     */
    _wrapWithLayoutStyles(html, layoutStyles, customCSS = '') {
        const layoutCSS = layoutStylesToInlineCSS(layoutStyles);
        const combinedCSS = [layoutCSS, customCSS].filter(Boolean).join('; ');

        if (!combinedCSS) {
            return html;
        }
        return `<div class="lb-layout-wrapper" style="${combinedCSS}">${html}</div>`;
    }

    /**
     * Wrap block HTML with consistent block classes and styles
     * @param {string} type - Block type
     * @param {object} props - Block props
     * @param {string} innerHtml - The block's inner HTML
     * @param {object} options - Additional options (tag, additionalClasses, additionalStyles)
     * @returns {string} - Wrapped HTML
     */
    _wrapBlock(type, props, innerHtml, options = {}) {
        const {
            tag = 'div',
            additionalClasses = '',
            additionalStyles = '',
            wrapWithLayout = true,
        } = options;

        const blockClasses = buildBlockClasses(type, props);
        const customCSS = props?.customCSS || '';
        const allClasses = [blockClasses, additionalClasses].filter(Boolean).join(' ');
        const allStyles = [additionalStyles, customCSS].filter(Boolean).join('; ');

        let html = `<${tag} class="${allClasses}"${allStyles ? ` style="${allStyles}"` : ''}>${innerHtml}</${tag}>`;

        // Wrap with layout styles if needed
        if (wrapWithLayout && props?.layoutStyles) {
            html = this._wrapWithLayoutStyles(html, props.layoutStyles);
        }

        return html;
    }

    /**
     * Generate default block HTML for web context
     */
    _generateDefaultBlockHtml(block, options = {}) {
        const { type, props } = block;
        const layoutStyles = props?.layoutStyles;
        const customCSS = props?.customCSS || '';
        const customClass = props?.customClass || '';

        let html = '';
        switch (type) {
            case 'heading':
                html = this._generateHeadingHtml(props, type);
                break;

            case 'text':
                html = this._generateTextHtml(props, type);
                break;

            case 'text-editor':
                html = this._generateTextEditorHtml(props, type);
                break;

            case 'image':
                html = this._generateImageHtml(props, type);
                break;

            case 'button':
                html = this._generateButtonHtml(props, type);
                break;

            case 'divider':
                html = this._generateDividerHtml(props, type);
                break;

            case 'spacer':
                html = this._generateSpacerHtml(props, type);
                break;

            case 'columns':
                html = this._generateColumnsHtml(props, options, type);
                break;

            case 'social':
                html = this._generateSocialHtml(props, type);
                break;

            case 'html':
                html = this._generateHtmlBlockHtml(props, type);
                break;

            case 'quote':
                html = this._generateQuoteHtml(props, type);
                break;

            case 'list':
                html = this._generateListHtml(props, type);
                break;

            case 'video':
                html = this._generateVideoHtml(props, type);
                break;

            case 'footer':
                html = this._generateFooterHtml(props, type);
                break;

            case 'countdown':
                html = this._generateCountdownHtml(props, type);
                break;

            case 'table':
                html = this._generateTableHtml(props, type);
                break;

            case 'section':
                html = this._generateSectionHtml(props, options, type);
                break;

            case 'accordion':
                html = this._generateAccordionHtml(props, type);
                break;

            default:
                html = '';
        }

        // Wrap with layout styles and custom CSS if present
        return this._wrapWithLayoutStyles(html, layoutStyles, customCSS);
    }

    /**
     * Generate spacer block HTML
     */
    _generateSpacerHtml(props, type) {
        const classes = buildBlockClasses(type, props);
        return `<div class="${classes}" style="height: ${props.height || '20px'};"></div>`;
    }

    /**
     * Generate HTML block HTML
     */
    _generateHtmlBlockHtml(props, type) {
        const classes = buildBlockClasses(type, props);
        return `<div class="${classes}">${props.code || ''}</div>`;
    }

    _generateHeadingHtml(props) {
        const level = props.level || 'h2';
        const classes = ['lb-heading'];
        const styles = [];

        if (props.align) styles.push(`text-align: ${props.align}`);
        if (props.color) styles.push(`color: ${props.color}`);
        if (props.fontSize) styles.push(`font-size: ${props.fontSize}`);
        if (props.fontWeight) styles.push(`font-weight: ${props.fontWeight}`);

        return `<${level} class="${classes.join(' ')}" style="${styles.join('; ')}">${props.text || ''}</${level}>`;
    }

    _generateTextHtml(props) {
        const styles = [];

        if (props.align) styles.push(`text-align: ${props.align}`);
        if (props.color) styles.push(`color: ${props.color}`);
        if (props.fontSize) styles.push(`font-size: ${props.fontSize}`);
        if (props.lineHeight) styles.push(`line-height: ${props.lineHeight}`);

        return `<div class="lb-text" style="${styles.join('; ')}">${props.content || ''}</div>`;
    }

    _generateTextEditorHtml(props) {
        const styles = [];

        if (props.align) styles.push(`text-align: ${props.align}`);
        if (props.color) styles.push(`color: ${props.color}`);
        if (props.fontSize) styles.push(`font-size: ${props.fontSize}`);
        if (props.lineHeight) styles.push(`line-height: ${props.lineHeight}`);

        // Text editor content is already HTML from TinyMCE
        return `<div class="lb-text-editor" style="${styles.join('; ')}">${props.content || ''}</div>`;
    }

    _generateImageHtml(props, type = 'image') {
        const isCustomWidth = props.width === 'custom' && props.customWidth;
        const isCustomHeight = props.height === 'custom' && props.customHeight;

        const imgStyles = [];
        if (isCustomWidth) {
            imgStyles.push(`width: ${props.customWidth}`);
            imgStyles.push(`max-width: ${props.customWidth}`);
        } else {
            imgStyles.push('max-width: 100%');
        }
        if (isCustomHeight) {
            imgStyles.push(`height: ${props.customHeight}`);
            imgStyles.push('object-fit: cover');
        } else {
            imgStyles.push('height: auto');
        }

        const img = `<img src="${props.src || ''}" alt="${props.alt || ''}" class="lb-image-element" style="${imgStyles.join('; ')}" loading="lazy" />`;

        const align = props.align || 'center';
        const justifyContent = align === 'left' ? 'flex-start' : align === 'right' ? 'flex-end' : 'center';

        const wrapper = props.link
            ? `<a href="${props.link}" target="_blank" rel="noopener noreferrer" class="lb-image-link">${img}</a>`
            : img;

        // Use buildBlockClasses for consistent naming
        const blockClasses = buildBlockClasses(type, props);

        return `<figure class="${blockClasses}" style="display: flex; justify-content: ${justifyContent}; margin: 0 0 16px 0;">${wrapper}</figure>`;
    }

    _generateButtonHtml(props) {
        const buttonStyles = [
            `background-color: ${props.backgroundColor || '#635bff'}`,
            `color: ${props.textColor || '#ffffff'}`,
            `padding: ${props.padding || '12px 24px'}`,
            `border-radius: ${props.borderRadius || '6px'}`,
            `font-size: ${props.fontSize || '16px'}`,
            `font-weight: ${props.fontWeight || '600'}`,
            'display: inline-block',
            'text-decoration: none',
            'border: none',
            'cursor: pointer',
            'transition: opacity 0.2s ease',
        ];

        return `
            <div class="lb-button-wrapper" style="text-align: ${props.align || 'center'}; padding: 10px 0;">
                <a href="${props.link || '#'}" target="_blank" rel="noopener noreferrer" class="lb-button" style="${buttonStyles.join('; ')}">${props.text || 'Click Here'}</a>
            </div>
        `;
    }

    _generateDividerHtml(props) {
        const styles = [
            'border: none',
            `border-top: ${props.thickness || '1px'} ${props.style || 'solid'} ${props.color || '#e5e7eb'}`,
            `width: ${props.width || '100%'}`,
            `margin: ${props.margin || '20px auto'}`,
        ];

        return `<hr class="lb-divider" style="${styles.join('; ')}" />`;
    }

    _generateColumnsHtml(props, options) {
        const gap = props.gap || '20px';
        const columns = props.columns || 2;

        const columnsHtml = (props.children || []).map((columnBlocks, index) => {
            const columnContent = columnBlocks.map(b => this.generateBlockHtml(b, options)).join('');
            return `<div class="lb-column" style="flex: 1; min-width: 0;">${columnContent || ''}</div>`;
        }).join('');

        return `
            <div class="lb-columns lb-columns-${columns}" style="display: flex; gap: ${gap}; flex-wrap: wrap;">
                ${columnsHtml}
            </div>
        `;
    }

    _generateSocialHtml(props) {
        const socialIcons = {
            facebook: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>',
            twitter: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
            instagram: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>',
            linkedin: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>',
            youtube: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>',
        };

        const iconSize = props.iconSize || '24px';
        const gap = props.gap || '12px';

        const linksHtml = Object.entries(props.links || {})
            .filter(([, url]) => url)
            .map(([platform, url]) => `
                <a href="${url}" target="_blank" rel="noopener noreferrer" class="lb-social-link lb-social-${platform}" style="display: inline-flex; width: ${iconSize}; height: ${iconSize}; color: inherit;">
                    ${socialIcons[platform] || ''}
                </a>
            `)
            .join('');

        return linksHtml ? `
            <div class="lb-social" style="text-align: ${props.align || 'center'}; display: flex; justify-content: ${props.align === 'left' ? 'flex-start' : props.align === 'right' ? 'flex-end' : 'center'}; gap: ${gap}; padding: 10px 0;">
                ${linksHtml}
            </div>
        ` : '';
    }

    _generateQuoteHtml(props) {
        const styles = [
            `padding: 20px`,
            `padding-left: 24px`,
            `background-color: ${props.backgroundColor || '#f8fafc'}`,
            `border-left: 4px solid ${props.borderColor || '#635bff'}`,
            `text-align: ${props.align || 'left'}`,
            `border-radius: 4px`,
            `margin: 10px 0`,
        ];

        return `
            <blockquote class="lb-quote" style="${styles.join('; ')}">
                <p style="color: ${props.textColor || '#475569'}; font-size: 1.125rem; font-style: italic; line-height: 1.6; margin: 0 0 12px 0;">"${props.text || ''}"</p>
                ${props.author ? `<cite style="color: ${props.authorColor || '#1e293b'}; font-size: 0.875rem; font-weight: 600; font-style: normal; display: block;">${props.author}</cite>` : ''}
                ${props.authorTitle ? `<span style="color: ${props.textColor || '#475569'}; font-size: 0.75rem;">${props.authorTitle}</span>` : ''}
            </blockquote>
        `;
    }

    _generateListHtml(props) {
        const listTag = props.listType === 'number' ? 'ol' : 'ul';
        const listStyles = [
            `color: ${props.color || '#333333'}`,
            `font-size: ${props.fontSize || '16px'}`,
            `line-height: 1.8`,
            `margin: 0`,
            props.listType === 'check' ? 'list-style: none; padding-left: 0;' : 'padding-left: 24px;',
        ];

        const items = (props.items || []).map(item => {
            if (props.listType === 'check') {
                return `<li style="display: flex; align-items: flex-start; gap: 8px; margin-bottom: 8px;">
                    <span style="color: ${props.iconColor || '#635bff'}; flex-shrink: 0;">✓</span>
                    <span>${item}</span>
                </li>`;
            }
            return `<li style="margin-bottom: 8px;">${item}</li>`;
        }).join('');

        return `<${listTag} class="lb-list lb-list-${props.listType || 'bullet'}" style="${listStyles.join('; ')}">${items}</${listTag}>`;
    }

    _generateVideoHtml(props, type = 'video') {
        const isDirectVideoFile = (url) => {
            if (!url) return false;
            const videoExtensions = /\.(mp4|webm|ogg|mov|avi|m4v)(\?.*)?$/i;
            return videoExtensions.test(url);
        };

        const parseVideoUrl = (url) => {
            if (!url) return null;
            const ytMatch = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/|youtube\.com\/shorts\/)([a-zA-Z0-9_-]{11})/);
            if (ytMatch) return { platform: 'youtube', id: ytMatch[1], embedUrl: `https://www.youtube.com/embed/${ytMatch[1]}?rel=0` };
            const vimeoMatch = url.match(/vimeo\.com\/(\d+)/);
            if (vimeoMatch) return { platform: 'vimeo', id: vimeoMatch[1], embedUrl: `https://player.vimeo.com/video/${vimeoMatch[1]}` };
            const dmMatch = url.match(/(?:dailymotion\.com\/video\/|dai\.ly\/)([a-zA-Z0-9]+)/);
            if (dmMatch) return { platform: 'dailymotion', id: dmMatch[1], embedUrl: `https://www.dailymotion.com/embed/video/${dmMatch[1]}` };
            return null;
        };

        const isDirectVideo = isDirectVideoFile(props.videoUrl);
        const vidInfo = parseVideoUrl(props.videoUrl);
        const width = props.width || '100%';
        const thumbnail = props.thumbnail || '';
        const align = props.align || 'center';
        const justifyContent = align === 'left' ? 'flex-start' : align === 'right' ? 'flex-end' : 'center';

        // Use buildBlockClasses for consistent naming
        const blockClasses = buildBlockClasses(type, props);
        const platformClass = vidInfo ? ` lb-video-${vidInfo.platform}` : '';

        // Direct video file - use native video element with poster
        if (isDirectVideo) {
            const posterAttr = thumbnail ? `poster="${thumbnail}"` : '';
            return `
                <div class="${blockClasses}" style="display: flex; justify-content: ${justifyContent};">
                    <video src="${props.videoUrl}" ${posterAttr} controls ${props.autoplay ? 'autoplay muted' : ''} ${props.loop ? 'loop' : ''} style="max-width: ${width}; width: 100%; height: auto; border-radius: 8px;" preload="metadata">
                        Your browser does not support the video tag.
                    </video>
                </div>
            `;
        }

        // Embedded video - use responsive iframe with optional custom thumbnail overlay
        if (vidInfo?.embedUrl) {
            const videoId = `lb-video-${Date.now()}-${Math.random().toString(36).substring(2, 9)}`;

            // If custom thumbnail is provided, show thumbnail with play button overlay
            if (thumbnail) {
                return `
                    <div class="${blockClasses}${platformClass}" style="display: flex; justify-content: ${justifyContent};">
                        <div id="${videoId}" class="lb-video-container" style="position: relative; max-width: ${width}; width: 100%; cursor: pointer;">
                            <div class="lb-video-thumbnail" style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 8px; background: #000;">
                                <img src="${thumbnail}" alt="Video thumbnail" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover;" />
                                <div class="lb-video-play-btn" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 68px; height: 48px; background: rgba(0,0,0,0.8); border-radius: 12px; display: flex; align-items: center; justify-content: center; transition: background 0.2s;">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="white">
                                        <path d="M8 5v14l11-7z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                    <script>
                        (function() {
                            var container = document.getElementById('${videoId}');
                            if (!container) return;
                            container.addEventListener('click', function() {
                                var wrapper = container.querySelector('.lb-video-thumbnail');
                                wrapper.innerHTML = '<iframe src="${vidInfo.embedUrl}&autoplay=1" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0; border-radius: 8px;" allowfullscreen allow="autoplay; encrypted-media; picture-in-picture"></iframe>';
                            });
                        })();
                    </script>
                `;
            }

            // No custom thumbnail - show iframe directly
            return `
                <div class="${blockClasses}${platformClass}" style="display: flex; justify-content: ${justifyContent};">
                    <div style="position: relative; max-width: ${width}; width: 100%;">
                        <div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 8px;">
                            <iframe src="${vidInfo.embedUrl}" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;" allowfullscreen allow="autoplay; encrypted-media; picture-in-picture"></iframe>
                        </div>
                    </div>
                </div>
            `;
        }

        // Fallback for unknown video URLs
        return `
            <div class="${blockClasses}" style="display: flex; justify-content: ${justifyContent};">
                <a href="${props.videoUrl}" target="_blank" rel="noopener noreferrer" class="lb-video-link">Watch Video</a>
            </div>
        `;
    }

    _generateFooterHtml(props) {
        const styles = [
            `padding: 24px 16px`,
            `text-align: ${props.align || 'center'}`,
            `border-top: 1px solid #e5e7eb`,
        ];

        return `
            <footer class="lb-footer" style="${styles.join('; ')}">
                ${props.companyName ? `<p style="color: ${props.textColor || '#6b7280'}; font-size: 14px; font-weight: 600; margin: 0 0 12px 0;">${props.companyName}</p>` : ''}
                ${props.address ? `<p style="color: ${props.textColor || '#6b7280'}; font-size: ${props.fontSize || '12px'}; margin: 0 0 8px 0;">${props.address}</p>` : ''}
                ${(props.phone || props.email) ? `
                    <p style="color: ${props.textColor || '#6b7280'}; font-size: ${props.fontSize || '12px'}; margin: 0 0 8px 0;">
                        ${props.phone || ''}
                        ${props.phone && props.email ? ' | ' : ''}
                        ${props.email ? `<a href="mailto:${props.email}" style="color: ${props.linkColor || '#635bff'};">${props.email}</a>` : ''}
                    </p>
                ` : ''}
                ${props.copyright ? `<p style="color: ${props.textColor || '#6b7280'}; font-size: 11px; margin: 12px 0 0 0;">${props.copyright}</p>` : ''}
            </footer>
        `;
    }

    _generateCountdownHtml(props) {
        const countdownId = `countdown-${Date.now()}-${Math.random().toString(36).substring(2, 9)}`;
        const targetDate = props.targetDate || new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
        const targetTime = props.targetTime || '23:59';
        const targetDateTime = `${targetDate}T${targetTime}:00`;

        return `
            <div class="lb-countdown" id="${countdownId}" data-target="${targetDateTime}" data-expired-message="${props.expiredMessage || ''}" style="padding: 24px; background-color: ${props.backgroundColor || '#1e293b'}; border-radius: 8px; text-align: ${props.align || 'center'};">
                ${props.title ? `<p style="color: ${props.textColor || '#ffffff'}; font-size: 18px; font-weight: 600; margin: 0 0 16px 0;">${props.title}</p>` : ''}
                <div style="display: flex; justify-content: center; gap: 16px; flex-wrap: wrap;">
                    <div style="background-color: rgba(255,255,255,0.1); border-radius: 8px; padding: 12px 16px; min-width: 60px;">
                        <span class="lb-countdown-days" style="color: ${props.numberColor || '#635bff'}; font-size: 36px; font-weight: 700; display: block;">00</span>
                        <span style="color: ${props.textColor || '#ffffff'}; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">Days</span>
                    </div>
                    <div style="background-color: rgba(255,255,255,0.1); border-radius: 8px; padding: 12px 16px; min-width: 60px;">
                        <span class="lb-countdown-hours" style="color: ${props.numberColor || '#635bff'}; font-size: 36px; font-weight: 700; display: block;">00</span>
                        <span style="color: ${props.textColor || '#ffffff'}; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">Hours</span>
                    </div>
                    <div style="background-color: rgba(255,255,255,0.1); border-radius: 8px; padding: 12px 16px; min-width: 60px;">
                        <span class="lb-countdown-mins" style="color: ${props.numberColor || '#635bff'}; font-size: 36px; font-weight: 700; display: block;">00</span>
                        <span style="color: ${props.textColor || '#ffffff'}; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">Mins</span>
                    </div>
                    <div style="background-color: rgba(255,255,255,0.1); border-radius: 8px; padding: 12px 16px; min-width: 60px;">
                        <span class="lb-countdown-secs" style="color: ${props.numberColor || '#635bff'}; font-size: 36px; font-weight: 700; display: block;">00</span>
                        <span style="color: ${props.textColor || '#ffffff'}; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">Secs</span>
                    </div>
                </div>
            </div>
            <script>
                (function() {
                    const el = document.getElementById('${countdownId}');
                    if (!el) return;
                    const target = new Date(el.dataset.target);
                    const expiredMsg = el.dataset.expiredMessage;
                    function update() {
                        const now = new Date();
                        const diff = Math.max(0, target - now);
                        if (diff <= 0 && expiredMsg) {
                            el.innerHTML = '<p style="color: #ffffff; font-size: 18px; font-weight: 600; margin: 0;">' + expiredMsg + '</p>';
                            return;
                        }
                        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                        const hours = Math.floor((diff / (1000 * 60 * 60)) % 24);
                        const mins = Math.floor((diff / 1000 / 60) % 60);
                        const secs = Math.floor((diff / 1000) % 60);
                        el.querySelector('.lb-countdown-days').textContent = String(days).padStart(2, '0');
                        el.querySelector('.lb-countdown-hours').textContent = String(hours).padStart(2, '0');
                        el.querySelector('.lb-countdown-mins').textContent = String(mins).padStart(2, '0');
                        el.querySelector('.lb-countdown-secs').textContent = String(secs).padStart(2, '0');
                    }
                    update();
                    setInterval(update, 1000);
                })();
            </script>
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
            <div class="lb-table-wrapper" style="overflow-x: auto;">
                <table class="lb-table" style="width: 100%; font-size: ${props.fontSize || '14px'}; border-collapse: collapse;">
                    ${props.showHeader && tableHeaders ? `<thead><tr>${tableHeaders}</tr></thead>` : ''}
                    <tbody>${tableRows}</tbody>
                </table>
            </div>
        `;
    }

    _generateSectionHtml(props, options) {
        const styles = [
            `padding: ${props.padding || '40px 20px'}`,
            `background-color: ${props.backgroundColor || 'transparent'}`,
        ];

        if (props.backgroundImage) {
            styles.push(`background-image: url('${props.backgroundImage}')`);
            styles.push(`background-size: ${props.backgroundSize || 'cover'}`);
            styles.push(`background-position: ${props.backgroundPosition || 'center'}`);
        }

        const content = (props.children || []).map(block => this.generateBlockHtml(block, options)).join('');

        return `
            <section class="lb-section" style="${styles.join('; ')}">
                <div style="max-width: ${props.maxWidth || '1200px'}; margin: 0 auto;">
                    ${content}
                </div>
            </section>
        `;
    }

    _generateAccordionHtml(props) {
        const items = props.items || [{ title: 'Accordion Item', content: 'Content goes here...' }];
        const accordionId = `accordion-${Date.now()}-${Math.random().toString(36).substring(2, 9)}`;
        const borderColor = props.borderColor || '#e5e7eb';
        const borderRadius = props.borderRadius || '8px';
        const headerBgColor = props.headerBgColor || '#ffffff';
        const headerBgColorActive = props.headerBgColorActive || '#f9fafb';
        const headerPadding = props.headerPadding || '16px';
        const titleColor = props.titleColor || '#1f2937';
        const titleFontSize = props.titleFontSize || '16px';
        const titleFontWeight = props.titleFontWeight || '600';
        const contentBgColor = props.contentBgColor || '#ffffff';
        const contentColor = props.contentColor || '#4b5563';
        const contentFontSize = props.contentFontSize || '14px';
        const contentPadding = props.contentPadding || '16px';
        const iconColor = props.iconColor || '#6b7280';
        const iconPosition = props.iconPosition || 'right';
        const transitionDuration = props.transitionDuration || 200;
        const independentToggle = props.independentToggle || false;

        const accordionItems = items.map((item, index) => {
            const isLast = index === items.length - 1;
            const itemId = `${accordionId}-item-${index}`;
            return `
                <div class="lb-accordion-item" data-index="${index}" style="border-bottom: ${isLast ? 'none' : `1px solid ${borderColor}`};">
                    <button type="button" class="lb-accordion-header" data-target="${itemId}" style="display: flex; align-items: center; justify-content: space-between; width: 100%; padding: ${headerPadding}; background-color: ${headerBgColor}; border: none; cursor: pointer; text-align: left; transition: background-color 0.2s; flex-direction: ${iconPosition === 'left' ? 'row-reverse' : 'row'};">
                        <span style="font-weight: ${titleFontWeight}; font-size: ${titleFontSize}; color: ${titleColor}; flex: 1;">${item.title}</span>
                        <span class="lb-accordion-icon" style="color: ${iconColor}; transition: transform ${transitionDuration}ms ease; ${iconPosition === 'left' ? 'margin-right: 12px;' : 'margin-left: 12px;'}">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </span>
                    </button>
                    <div id="${itemId}" class="lb-accordion-content" style="max-height: 0; overflow: hidden; transition: max-height ${transitionDuration}ms ease-in-out;">
                        <div style="padding: ${contentPadding}; background-color: ${contentBgColor}; color: ${contentColor}; font-size: ${contentFontSize}; line-height: 1.6;">
                            ${item.content}
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        return `
            <div class="lb-accordion" id="${accordionId}" data-independent="${independentToggle}" style="border: 1px solid ${borderColor}; border-radius: ${borderRadius}; overflow: hidden;">
                ${accordionItems}
            </div>
            <script>
                (function() {
                    const accordion = document.getElementById('${accordionId}');
                    if (!accordion) return;
                    const isIndependent = accordion.dataset.independent === 'true';
                    const headers = accordion.querySelectorAll('.lb-accordion-header');

                    headers.forEach(header => {
                        header.addEventListener('click', function() {
                            const targetId = this.dataset.target;
                            const content = document.getElementById(targetId);
                            const icon = this.querySelector('.lb-accordion-icon');
                            const isOpen = content.style.maxHeight && content.style.maxHeight !== '0px';

                            if (!isIndependent) {
                                // Close all other items
                                accordion.querySelectorAll('.lb-accordion-content').forEach(c => {
                                    c.style.maxHeight = '0px';
                                });
                                accordion.querySelectorAll('.lb-accordion-icon').forEach(i => {
                                    i.style.transform = 'rotate(0deg)';
                                });
                                accordion.querySelectorAll('.lb-accordion-header').forEach(h => {
                                    h.style.backgroundColor = '${headerBgColor}';
                                });
                            }

                            if (isOpen) {
                                content.style.maxHeight = '0px';
                                icon.style.transform = 'rotate(0deg)';
                                this.style.backgroundColor = '${headerBgColor}';
                            } else {
                                content.style.maxHeight = content.scrollHeight + 'px';
                                icon.style.transform = 'rotate(180deg)';
                                this.style.backgroundColor = '${headerBgColorActive}';
                            }
                        });
                    });

                    // Open first item by default
                    const firstHeader = headers[0];
                    if (firstHeader) {
                        firstHeader.click();
                    }
                })();
            </script>
        `;
    }

    /**
     * Wrap the final HTML output for web pages
     * For post/page content, we return just the content without full HTML document wrapper
     */
    wrapOutput(content, settings) {
        // For post content, return just the inner HTML wrapped in a content div
        // This makes it compatible with existing CMS systems and frontend themes
        return `<div class="lb-content">${content}</div>`;
    }

    /**
     * Generate a full standalone HTML page (for previews, exports, etc.)
     */
    generateStandalonePage(blocks, settings = {}) {
        const mergedSettings = { ...this.getDefaultSettings(), ...settings };
        const blocksHtml = blocks
            .map((block) => this.generateBlockHtml(block, { settings: mergedSettings }))
            .join('');

        const fontFamily = mergedSettings.fontFamily || 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
        const fontSize = mergedSettings.fontSize || '16px';
        const lineHeight = mergedSettings.lineHeight || '1.6';
        const textColor = mergedSettings.textColor || '#1f2937';
        const backgroundColor = mergedSettings.backgroundColor || '#ffffff';
        const maxWidth = mergedSettings.maxWidth || '1200px';
        const contentPadding = mergedSettings.contentPadding || '24px';

        return `
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 0;
            font-family: ${fontFamily};
            font-size: ${fontSize};
            line-height: ${lineHeight};
            color: ${textColor};
            background-color: ${backgroundColor};
        }
        img { max-width: 100%; height: auto; }
        a { color: inherit; }
        .lb-content {
            max-width: ${maxWidth};
            margin: 0 auto;
            padding: ${contentPadding};
        }
        .lb-heading { margin: 0 0 16px 0; }
        .lb-text { margin: 0 0 16px 0; }
        .lb-image-wrapper { margin: 0 0 16px 0; }
        .lb-button:hover { opacity: 0.9; }
        .lb-columns { margin: 0 0 16px 0; }
        @media (max-width: 768px) {
            .lb-columns { flex-direction: column; }
            .lb-column { flex: none !important; width: 100% !important; }
        }
    </style>
</head>
<body>
    <div class="lb-content">
        ${blocksHtml}
    </div>
</body>
</html>
        `.trim();
    }

    /**
     * Get CSS styles for preview mode
     */
    getPreviewStyles() {
        return `
            .lb-heading { margin: 0 0 16px 0; }
            .lb-text { margin: 0 0 16px 0; }
            .lb-image-wrapper { margin: 0 0 16px 0; }
            .lb-button:hover { opacity: 0.9; }
            .lb-columns { margin: 0 0 16px 0; }
        `;
    }
}

export default WebAdapter;
