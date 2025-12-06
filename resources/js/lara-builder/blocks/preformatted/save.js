/**
 * Preformatted Block - Save/Output Generators
 *
 * Generates HTML output for different contexts (page/web and email).
 */

import { buildBlockClasses, mergeBlockStyles } from '@lara-builder/utils';

/**
 * Escape HTML entities
 */
const escapeHtml = (text) => {
    if (!text) return '';
    return text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
};

/**
 * Generate HTML for web/page context
 */
export const page = (props, options = {}) => {
    const type = 'preformatted';
    const blockClasses = buildBlockClasses(type, props);
    const content = escapeHtml(props.content || '');

    const blockStyles = [
        `background-color: ${props.backgroundColor || '#f5f5f5'}`,
        `border-radius: ${props.borderRadius || '4px'}`,
        `padding: ${props.padding || '16px'}`,
        'overflow-x: auto',
        `font-family: ${props.fontFamily || '"Courier New", Courier, monospace'}`,
        `font-size: ${props.fontSize || '14px'}`,
        `line-height: ${props.lineHeight || '1.5'}`,
        `color: ${props.color || '#333333'}`,
    ];

    const mergedStyles = mergeBlockStyles(props, blockStyles.join('; '));

    return `
        <div class="${blockClasses}" style="${mergedStyles}">
            <pre style="margin: 0; white-space: pre-wrap; word-wrap: break-word;">${content}</pre>
        </div>
    `;
};

/**
 * Generate HTML for email context
 */
export const email = (props, options = {}) => {
    const content = escapeHtml(props.content || '');

    return `
        <div style="background-color: ${props.backgroundColor || '#f5f5f5'}; border-radius: ${props.borderRadius || '4px'}; padding: ${props.padding || '16px'}; overflow-x: auto; font-family: monospace; font-size: ${props.fontSize || '14px'}; line-height: ${props.lineHeight || '1.5'}; color: ${props.color || '#333333'};">
            <pre style="margin: 0; white-space: pre-wrap; word-wrap: break-word;">${content}</pre>
        </div>
    `;
};

export default {
    page,
    email,
};
