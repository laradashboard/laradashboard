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
 * Generate HTML for email context
 */
export const email = (props, options = {}) => {
    return `
        <div style="padding: 24px 16px; text-align: ${props.align || 'center'}; border-top: 1px solid #e5e7eb;">
            ${props.companyName ? `<p style="color: ${props.textColor || '#6b7280'}; font-size: 14px; font-weight: 600; margin: 0 0 12px 0;">${props.companyName}</p>` : ''}
            ${props.address ? `<p style="color: ${props.textColor || '#6b7280'}; font-size: ${props.fontSize || '12px'}; margin: 0 0 8px 0;">${props.address}</p>` : ''}
            ${(props.phone || props.email) ? `<p style="color: ${props.textColor || '#6b7280'}; font-size: ${props.fontSize || '12px'}; margin: 0 0 8px 0;">${props.phone || ''}${props.phone && props.email ? ' | ' : ''}${props.email ? `<a href="mailto:${props.email}" style="color: ${props.linkColor || '#635bff'}; text-decoration: underline;">${props.email}</a>` : ''}</p>` : ''}
            ${props.unsubscribeText ? `<p style="color: ${props.textColor || '#6b7280'}; font-size: ${props.fontSize || '12px'}; margin: 16px 0 0 0;"><a href="${props.unsubscribeUrl || '#'}" style="color: ${props.linkColor || '#635bff'}; text-decoration: underline;">${props.unsubscribeText}</a></p>` : ''}
            ${props.copyright ? `<p style="color: ${props.textColor || '#6b7280'}; font-size: 11px; margin: 12px 0 0 0;">${props.copyright}</p>` : ''}
        </div>
    `;
};

export default {
    page,
    email,
};
