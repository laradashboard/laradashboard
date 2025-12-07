/**
 * Quote Block - Save/Output Generators
 *
 * Generates HTML output for different contexts (page/web and email).
 * Supports HTML formatted content from inline editing (bold, italic, etc.)
 */

import { buildBlockClasses, mergeBlockStyles } from '@lara-builder/utils';

/**
 * Generate HTML for web/page context
 */
export const page = (props) => {
    const type = 'quote';
    const blockClasses = buildBlockClasses(type, props);

    // Block-specific styles
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

    // Note: Content may contain HTML formatting (bold, italic, etc.)
    // We wrap with quotes using CSS pseudo-elements or inline quotes
    const quoteText = props.text || '';
    const author = props.author || '';
    const authorTitle = props.authorTitle || '';

    return `
        <blockquote class="${blockClasses}" style="${mergedStyles}">
            <p style="color: ${props.textColor || '#475569'}; font-size: 1.125rem; font-style: italic; line-height: 1.6; margin: 0 0 12px 0;">"${quoteText}"</p>
            ${author ? `<cite style="color: ${props.authorColor || '#1e293b'}; font-size: 0.875rem; font-weight: 600; font-style: normal; display: block;">${author}</cite>` : ''}
            ${authorTitle ? `<span style="color: ${props.textColor || '#475569'}; font-size: 0.75rem;">${authorTitle}</span>` : ''}
        </blockquote>
    `;
};

/**
 * Generate HTML for email context
 */
export const email = (props) => {
    const quoteText = props.text || '';
    const author = props.author || '';
    const authorTitle = props.authorTitle || '';

    return `
        <div style="padding: 20px; padding-left: 24px; background-color: ${props.backgroundColor || '#f8fafc'}; border-left: 4px solid ${props.borderColor || '#635bff'}; text-align: ${props.align || 'left'}; border-radius: 4px; margin: 10px 0;">
            <p style="color: ${props.textColor || '#475569'}; font-size: 16px; font-style: italic; line-height: 1.6; margin: 0 0 12px 0;">"${quoteText}"</p>
            ${author ? `<p style="color: ${props.authorColor || '#1e293b'}; font-size: 14px; font-weight: 600; margin: 0;">${author}</p>` : ''}
            ${authorTitle ? `<p style="color: ${props.textColor || '#475569'}; font-size: 12px; margin: 0;">${authorTitle}</p>` : ''}
        </div>
    `;
};

export default {
    page,
    email,
};
