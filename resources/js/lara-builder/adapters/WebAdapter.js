/**
 * WebAdapter - Modern Web HTML Output Adapter
 *
 * Generates modern HTML5 output with CSS classes for web pages.
 * Uses semantic HTML, flexbox/grid layouts, and native video embeds.
 *
 * Block-specific HTML generation is delegated to each block's save.js file.
 * This adapter serves as a thin orchestration layer.
 */

import { BaseAdapter } from './BaseAdapter';
import { LaraHooks } from '../hooks-system/LaraHooks';
import { BuilderHooks, getBlockHook } from '../hooks-system/HookNames';
import { blockRegistry } from '../registry/BlockRegistry';
import { layoutStylesToInlineCSS } from '../components/LayoutStylesSection';

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
     * Delegates to block-registered HTML generators from save.js files
     */
    generateBlockHtml(block, options = {}) {
        const { type, props, id } = block;

        // Pass generateBlockHtml function, allBlocks, and blockId to options
        // blockId is needed for heading anchors (TOC links)
        const extendedOptions = {
            ...options,
            blockId: id,
            generateBlockHtml: (b, opts) => this.generateBlockHtml(b, { ...opts, allBlocks: options.allBlocks }),
        };

        // Check if block has a custom HTML generator registered
        const blockDef = blockRegistry.get(type);
        if (blockDef?.save?.page) {
            let html = blockDef.save.page(props, extendedOptions);

            // Apply filters for extensibility
            html = LaraHooks.applyFilters(getBlockHook(BuilderHooks.FILTER_HTML_BLOCK, type), html, props, extendedOptions);
            html = LaraHooks.applyFilters(`builder.page.block.${type}`, html, props, extendedOptions);

            return html;
        }

        // Fallback for blocks without registered generators (section block, custom blocks)
        let html = this._generateFallbackBlockHtml(block, extendedOptions);
        html = LaraHooks.applyFilters(getBlockHook(BuilderHooks.FILTER_HTML_BLOCK, type), html, props, extendedOptions);
        html = LaraHooks.applyFilters(`builder.page.block.${type}`, html, props, extendedOptions);

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
     * Fallback HTML generation for blocks without registered generators
     * Currently only handles the section block
     */
    _generateFallbackBlockHtml(block, options = {}) {
        const { type, props } = block;

        if (type === 'section') {
            return this._generateSectionHtml(props, options);
        }

        // Unknown block type - return empty
        console.warn(`[WebAdapter] No HTML generator found for block type: ${type}`);
        return '';
    }

    /**
     * Generate section block HTML (fallback - not yet moved to save.js)
     */
    _generateSectionHtml(props, options) {
        const blockClasses = `lb-block lb-section${props.customClass ? ` ${props.customClass}` : ''}`;
        const layoutCSS = layoutStylesToInlineCSS(props?.layoutStyles);
        const customCSS = props?.customCSS || '';

        const blockStyles = [
            `padding: ${props.padding || '40px 20px'}`,
        ];

        // Only add if not controlled by layoutStyles
        if (!props.layoutStyles?.background?.color) {
            blockStyles.push(`background-color: ${props.backgroundColor || 'transparent'}`);
        }

        // Legacy background image support (if not using layoutStyles)
        if (props.backgroundImage && !props.layoutStyles?.background?.image) {
            blockStyles.push(`background-image: url('${props.backgroundImage}')`);
            blockStyles.push(`background-size: ${props.backgroundSize || 'cover'}`);
            blockStyles.push(`background-position: ${props.backgroundPosition || 'center'}`);
        }

        const allStyles = [layoutCSS, blockStyles.join('; '), customCSS].filter(Boolean).join('; ');
        const content = (props.children || []).map(block => this.generateBlockHtml(block, options)).join('');

        return `
            <section class="${blockClasses}" style="${allStyles}">
                <div style="max-width: ${props.maxWidth || '1200px'}; margin: 0 auto;">
                    ${content}
                </div>
            </section>
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
