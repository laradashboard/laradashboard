/**
 * Quote Block - Save/Output Generators
 *
 * Generates HTML output for different contexts (page/web and email).
 */

import { buildBlockClasses, mergeBlockStyles } from '@lara-builder/utils';

/**
 * Generate HTML for web/page context
 */
export const page = (props, options = {}) => {
    const type = 'quote';
    const blockClasses = buildBlockClasses(type, props);

    // Block-specific styles (backward compatibility)
    const blockStyles = [
        `padding: 20px`,
        `padding-left: 24px`,
        `text-align: ${props.align || 'left'}`,
        `margin: 10px 0`,
    ];

    // Only add if not controlled by layoutStyles
    if (!props.layoutStyles?.background?.color) {
        blockStyles.push(`background-color: ${props.backgroundColor || '#f8fafc'}`);
    }
    if (!props.layoutStyles?.border) {
        blockStyles.push(`border-left: 4px solid ${props.borderColor || '#635bff'}`);
        blockStyles.push(`border-radius: 4px`);
    }

    const mergedStyles = mergeBlockStyles(props, blockStyles.join('; '));

    return `
        <blockquote class="${blockClasses}" style="${mergedStyles}">
            <p style="color: ${props.textColor || '#475569'}; font-size: 1.125rem; font-style: italic; line-height: 1.6; margin: 0 0 12px 0;">"${props.text || ''}"</p>
            ${props.author ? `<cite style="color: ${props.authorColor || '#1e293b'}; font-size: 0.875rem; font-weight: 600; font-style: normal; display: block;">${props.author}</cite>` : ''}
            ${props.authorTitle ? `<span style="color: ${props.textColor || '#475569'}; font-size: 0.75rem;">${props.authorTitle}</span>` : ''}
        </blockquote>
    `;
};

/**
 * Generate HTML for email context
 */
export const email = (props, options = {}) => {
    return `
        <div style="padding: 20px; padding-left: 24px; background-color: ${props.backgroundColor || '#f8fafc'}; border-left: 4px solid ${props.borderColor || '#635bff'}; text-align: ${props.align || 'left'}; border-radius: 4px; margin: 10px 0;">
            <p style="color: ${props.textColor || '#475569'}; font-size: 16px; font-style: italic; line-height: 1.6; margin: 0 0 12px 0;">"${props.text || ''}"</p>
            ${props.author ? `<p style="color: ${props.authorColor || '#1e293b'}; font-size: 14px; font-weight: 600; margin: 0;">${props.author}</p>` : ''}
            ${props.authorTitle ? `<p style="color: ${props.textColor || '#475569'}; font-size: 12px; margin: 0;">${props.authorTitle}</p>` : ''}
        </div>
    `;
};

export default {
    page,
    email,
};
