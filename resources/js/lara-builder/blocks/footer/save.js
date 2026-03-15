/**
 * Footer Block - Save/Output Generators
 *
 * Generates HTML output for different contexts (page/web and email).
 */

import { buildBlockClasses, mergeBlockStyles } from '@lara-builder/utils';

/**
 * Generate HTML for web/page context
 */
export const page = (props, options = {}) => {
    const type = 'footer';
    const blockClasses = buildBlockClasses(type, props);
    const blockStyles = [
        `padding: 24px 16px`,
        `text-align: ${props.align || 'center'}`,
    ];

    // Only add if not controlled by layoutStyles
    if (!props.layoutStyles?.border) {
        blockStyles.push(`border-top: 1px solid #e5e7eb`);
    }

    const mergedStyles = mergeBlockStyles(props, blockStyles.join('; '));

    // Text color - use layoutStyles if available, otherwise props or default
    const textColor = props.layoutStyles?.typography?.color || props.textColor || '#6b7280';
    const fontSize = props.layoutStyles?.typography?.fontSize || props.fontSize || '12px';

    return `
        <footer class="${blockClasses}" style="${mergedStyles}">
            ${props.companyName ? `<p style="color: ${textColor}; font-size: 14px; font-weight: 600; margin: 0 0 12px 0;">${props.companyName}</p>` : ''}
            ${props.address ? `<p style="color: ${textColor}; font-size: ${fontSize}; margin: 0 0 8px 0;">${props.address}</p>` : ''}
            ${(props.phone || props.email) ? `
                <p style="color: ${textColor}; font-size: ${fontSize}; margin: 0 0 8px 0;">
                    ${props.phone || ''}
                    ${props.phone && props.email ? ' | ' : ''}
                    ${props.email ? `<a href="mailto:${props.email}" style="color: ${props.linkColor || '#635bff'};">${props.email}</a>` : ''}
                </p>
            ` : ''}
            ${props.copyright ? `<p style="color: ${textColor}; font-size: 11px; margin: 12px 0 0 0;">${props.copyright}</p>` : ''}
        </footer>
    `;
};

/**
 * Generate placeholder for server-side rendering (email context)
 */
export const email = (props, options = {}) => {
    const serverProps = {
        companyName: props.companyName || '',
        address: props.address || '',
        email: props.email || '',
        phone: props.phone || '',
        unsubscribeText: props.unsubscribeText || 'Unsubscribe',
        unsubscribeUrl: props.unsubscribeUrl || '#unsubscribe',
        copyright: props.copyright || '',
        textColor: props.textColor || '#6b7280',
        linkColor: props.linkColor || '#635bff',
        fontSize: props.fontSize || '12px',
        align: props.align || 'center',
        layoutStyles: props.layoutStyles || {},
    };

    const propsJson = JSON.stringify(serverProps).replace(/'/g, '&#39;');

    return `<div data-lara-block="footer" data-props='${propsJson}'></div>`;
};

export default {
    page,
    email,
};
