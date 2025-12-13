/**
 * Save Helpers - Reduce duplication in save.js files
 *
 * Provides utilities for generating HTML output for both
 * page (modern HTML5) and email (table-based) contexts.
 *
 * @example Simple usage:
 * ```js
 * import { createSave, emailTable, pageDiv } from '@lara-builder/factory';
 *
 * export default createSave({
 *     page: (props) => pageDiv(props, `<h2>${props.text}</h2>`),
 *     email: (props) => emailTable(props, `<h2>${props.text}</h2>`),
 * });
 * ```
 *
 * @example With shared content:
 * ```js
 * export default createSave({
 *     // Same content for both contexts
 *     content: (props) => `<h2 style="color: ${props.color}">${props.text}</h2>`,
 *     // Email wraps in table automatically
 *     emailWrapper: true,
 * });
 * ```
 */

import { buildBlockClasses, mergeBlockStyles } from '../utils/save';

/**
 * Wrap content in a div with proper block classes and styles
 * For page/web context
 *
 * @param {string} type - Block type
 * @param {Object} props - Block props
 * @param {string} content - Inner HTML content
 * @param {string} [extraStyles=''] - Additional inline styles
 * @returns {string} HTML string
 */
export const pageDiv = (type, props, content, extraStyles = '') => {
    const blockClasses = buildBlockClasses(type, props);
    const mergedStyles = mergeBlockStyles(props, extraStyles);

    return `<div class="${blockClasses}" style="${mergedStyles}">${content}</div>`;
};

/**
 * Wrap content in an email-safe table
 * For email context
 *
 * @param {Object} props - Block props
 * @param {string} content - Inner HTML content
 * @param {Object} [options={}] - Table options
 * @param {string} [options.width='100%'] - Table width
 * @param {string} [options.align] - Content alignment
 * @param {string} [options.padding='0'] - Cell padding
 * @param {string} [options.bgColor] - Background color
 * @param {string} [options.extraStyles=''] - Additional inline styles for td
 * @returns {string} HTML string
 */
export const emailTable = (props, content, options = {}) => {
    const {
        width = '100%',
        align = props.align || 'left',
        padding = '0',
        bgColor = '',
        extraStyles = '',
    } = options;

    const tdStyles = [
        `padding: ${padding}`,
        `text-align: ${align}`,
        bgColor ? `background-color: ${bgColor}` : '',
        extraStyles,
    ].filter(Boolean).join('; ');

    return `
<table width="${width}" cellpadding="0" cellspacing="0" border="0" role="presentation">
    <tr>
        <td style="${tdStyles}">
            ${content}
        </td>
    </tr>
</table>`.trim();
};

/**
 * Create email table with common layout styles applied
 *
 * @param {Object} props - Block props with layoutStyles
 * @param {string} content - Inner HTML content
 * @returns {string} HTML string
 */
export const emailTableWithLayout = (props, content) => {
    const styles = [];

    // Apply layout styles
    if (props.layoutStyles) {
        const ls = props.layoutStyles;

        // Margin (as table margin)
        if (ls.margin) {
            const { top, right, bottom, left } = ls.margin;
            if (top) styles.push(`margin-top: ${top}`);
            if (right) styles.push(`margin-right: ${right}`);
            if (bottom) styles.push(`margin-bottom: ${bottom}`);
            if (left) styles.push(`margin-left: ${left}`);
        }

        // Padding
        if (ls.padding) {
            const { top, right, bottom, left } = ls.padding;
            const paddingStr = `${top || '0'} ${right || '0'} ${bottom || '0'} ${left || '0'}`;
            styles.push(`padding: ${paddingStr}`);
        }

        // Background
        if (ls.background?.color) {
            styles.push(`background-color: ${ls.background.color}`);
        }

        // Border
        if (ls.border) {
            if (ls.border.width?.top) {
                styles.push(`border: ${ls.border.width.top} ${ls.border.style || 'solid'} ${ls.border.color || '#000'}`);
            }
            if (ls.border.radius?.topLeft) {
                styles.push(`border-radius: ${ls.border.radius.topLeft}`);
            }
        }
    }

    return emailTable(props, content, {
        extraStyles: styles.join('; '),
        align: props.align,
    });
};

/**
 * Create a complete save object with page and email generators
 *
 * @param {Object} config
 * @param {Function} [config.page] - Page HTML generator (props, options) => string
 * @param {Function} [config.email] - Email HTML generator (props, options) => string
 * @param {Function} [config.content] - Shared content generator (props) => string
 * @param {boolean} [config.emailWrapper=true] - Whether to wrap email content in table
 * @param {string} [config.type] - Block type for class names
 * @returns {Object} Save object { page, email }
 */
export const createSave = (config) => {
    const { page, email, content, emailWrapper = true, type } = config;

    const result = {};

    // Page generator
    if (page) {
        result.page = page;
    } else if (content) {
        result.page = (props, options) => {
            const inner = content(props, options);
            if (type) {
                return pageDiv(type, props, inner);
            }
            return inner;
        };
    }

    // Email generator
    if (email) {
        result.email = email;
    } else if (content) {
        result.email = (props, options) => {
            const inner = content(props, options);
            if (emailWrapper) {
                return emailTableWithLayout(props, inner);
            }
            return inner;
        };
    }

    return result;
};

/**
 * Common email-safe inline styles for text elements
 */
export const emailTextStyles = (props) => {
    const styles = [
        `color: ${props.color || '#333333'}`,
        `font-size: ${props.fontSize || '16px'}`,
        `font-weight: ${props.fontWeight || 'normal'}`,
        `line-height: ${props.lineHeight || '1.5'}`,
        `text-align: ${props.align || 'left'}`,
        'font-family: Arial, sans-serif',
        'margin: 0',
    ];

    if (props.letterSpacing) {
        styles.push(`letter-spacing: ${props.letterSpacing}`);
    }

    return styles.join('; ');
};

/**
 * Generate email-safe button HTML
 *
 * @param {Object} props - Button props
 * @param {string} props.text - Button text
 * @param {string} props.link - Button URL
 * @param {string} [props.backgroundColor='#635bff'] - Background color
 * @param {string} [props.textColor='#ffffff'] - Text color
 * @param {string} [props.borderRadius='6px'] - Border radius
 * @param {string} [props.padding='12px 24px'] - Button padding
 * @param {string} [props.align='center'] - Button alignment
 * @returns {string} Email-safe button HTML
 */
export const emailButton = (props) => {
    const {
        text = 'Click Here',
        link = '#',
        backgroundColor = '#635bff',
        textColor = '#ffffff',
        borderRadius = '6px',
        padding = '12px 24px',
        fontSize = '16px',
        fontWeight = '600',
        align = 'center',
    } = props;

    // Parse padding for MSO (vertical padding used for height calculation)
    const vPad = parseInt(padding.split(' ')[0]);

    return `
<table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation">
    <tr>
        <td align="${align}">
            <!--[if mso]>
            <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="${link}" style="height:${vPad * 2 + 20}px;v-text-anchor:middle;width:auto;" arcsize="${parseInt(borderRadius) * 2}%" strokecolor="${backgroundColor}" fillcolor="${backgroundColor}">
                <w:anchorlock/>
                <center style="color:${textColor};font-family:Arial,sans-serif;font-size:${fontSize};font-weight:${fontWeight};">${text}</center>
            </v:roundrect>
            <![endif]-->
            <!--[if !mso]><!-->
            <a href="${link}" style="display: inline-block; background-color: ${backgroundColor}; color: ${textColor}; font-size: ${fontSize}; font-weight: ${fontWeight}; font-family: Arial, sans-serif; text-decoration: none; padding: ${padding}; border-radius: ${borderRadius}; text-align: center; mso-hide: all;">
                ${text}
            </a>
            <!--<![endif]-->
        </td>
    </tr>
</table>`.trim();
};

/**
 * Generate email-safe image HTML
 *
 * @param {Object} props - Image props
 * @param {string} props.src - Image URL
 * @param {string} [props.alt=''] - Alt text
 * @param {string} [props.width='100%'] - Image width
 * @param {string} [props.align='center'] - Alignment
 * @param {string} [props.link] - Optional link URL
 * @returns {string} Email-safe image HTML
 */
export const emailImage = (props) => {
    const {
        src,
        alt = '',
        width = '100%',
        align = 'center',
        link,
        borderRadius = '0',
    } = props;

    if (!src) return '';

    const imgStyle = [
        'display: block',
        `max-width: ${width}`,
        'height: auto',
        borderRadius !== '0' ? `border-radius: ${borderRadius}` : '',
    ].filter(Boolean).join('; ');

    const img = `<img src="${src}" alt="${alt}" style="${imgStyle}" width="${width.replace('%', '')}" />`;

    const linkedImg = link
        ? `<a href="${link}" style="display: block;">${img}</a>`
        : img;

    return `
<table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation">
    <tr>
        <td align="${align}">
            ${linkedImg}
        </td>
    </tr>
</table>`.trim();
};

/**
 * Generate email-safe divider/separator
 *
 * @param {Object} props - Divider props
 * @param {string} [props.color='#e5e7eb'] - Line color
 * @param {string} [props.height='1px'] - Line height
 * @param {string} [props.width='100%'] - Divider width
 * @param {string} [props.style='solid'] - Line style
 * @returns {string} Email-safe divider HTML
 */
export const emailDivider = (props) => {
    const {
        color = '#e5e7eb',
        height = '1px',
        width = '100%',
        style = 'solid',
        margin = '20px 0',
    } = props;

    return `
<table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation" style="margin: ${margin};">
    <tr>
        <td>
            <div style="border-top: ${height} ${style} ${color}; width: ${width}; margin: 0 auto;"></div>
        </td>
    </tr>
</table>`.trim();
};

/**
 * Generate email-safe spacer
 *
 * @param {string} height - Spacer height
 * @returns {string} Email-safe spacer HTML
 */
export const emailSpacer = (height = '20px') => {
    return `
<table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation">
    <tr>
        <td style="height: ${height}; line-height: ${height}; font-size: 1px;">&nbsp;</td>
    </tr>
</table>`.trim();
};

export default {
    pageDiv,
    emailTable,
    emailTableWithLayout,
    createSave,
    emailTextStyles,
    emailButton,
    emailImage,
    emailDivider,
    emailSpacer,
};
