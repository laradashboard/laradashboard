/**
 * EmailAdapter - Email-safe HTML Output Adapter
 *
 * Generates email-compatible HTML using tables and inline styles.
 *
 * Block-specific HTML generation is delegated to each block's save.js file.
 * This adapter serves as a thin orchestration layer.
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
     * Delegates to block-registered HTML generators from save.js files
     */
    generateBlockHtml(block, options = {}) {
        const { type, props } = block;
        const layoutStyles = props?.layoutStyles;

        // Pass generateBlockHtml function to options for nested blocks (columns)
        const extendedOptions = {
            ...options,
            generateBlockHtml: (b, opts) => this.generateBlockHtml(b, opts),
        };

        // Check if block has a custom HTML generator registered
        const blockDef = blockRegistry.get(type);
        if (blockDef?.save?.email) {
            let html = blockDef.save.email(props, extendedOptions);

            // Wrap with layout styles if present
            html = this._wrapWithLayoutStyles(html, layoutStyles);

            // Apply filters for extensibility
            html = LaraHooks.applyFilters(getBlockHook(BuilderHooks.FILTER_HTML_BLOCK, type), html, props, extendedOptions);
            html = LaraHooks.applyFilters(`builder.email.block.${type}`, html, props, extendedOptions);

            return html;
        }

        // Fallback for blocks without registered generators
        console.warn(`[EmailAdapter] No HTML generator found for block type: ${type}`);
        return '';
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
     * Wrap the final HTML output
     */
    wrapOutput(content, settings) {
        // Basic settings
        const maxWidth = settings.width || '600px';
        const contentPadding = settings.contentPadding || '40px';
        const contentMargin = settings.contentMargin || '40px';

        // Extract values from layoutStyles or use defaults
        const background = settings.layoutStyles?.background || {};
        const border = settings.layoutStyles?.border || {};
        const boxShadow = settings.layoutStyles?.boxShadow || {};

        // Outer background (simplified)
        let outerBgStyle = 'background-color: #f4f4f4;';

        // Content background
        let contentBgStyle = `background-color: ${background.color || '#ffffff'};`;
        if (background.image) {
            contentBgStyle += ` background-image: url('${background.image}'); background-size: ${background.size || 'cover'}; background-position: ${background.position || 'center'}; background-repeat: ${background.repeat || 'no-repeat'};`;
        }

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
