/**
 * Text Editor Block - Save/Output Generators
 *
 * Generates HTML output for different contexts (page/web and email).
 */

import { buildBlockClasses, mergeBlockStyles } from '@lara-builder/utils';

/**
 * Generate HTML for web/page context
 */
export const page = (props, options = {}) => {
    const type = 'text-editor';
    const blockClasses = buildBlockClasses(type, props);
    const styles = [];

    // Block-specific styles (backward compatibility)
    if (props.align) styles.push(`text-align: ${props.align}`);
    if (props.color && !props.layoutStyles?.typography?.color) styles.push(`color: ${props.color}`);
    if (props.fontSize && !props.layoutStyles?.typography?.fontSize) styles.push(`font-size: ${props.fontSize}`);
    if (props.lineHeight && !props.layoutStyles?.typography?.lineHeight) styles.push(`line-height: ${props.lineHeight}`);

    // Merge with layout styles
    const mergedStyles = mergeBlockStyles(props, styles.join('; '));

    // Text editor content is already HTML from TinyMCE
    return `<div class="${blockClasses}" style="${mergedStyles}">${props.content || ''}</div>`;
};

/**
 * Generate HTML for email context
 */
export const email = (props, options = {}) => {
    return `<div style="text-align: ${props.align || 'left'}; color: ${props.color || '#333333'}; font-size: ${props.fontSize || '16px'}; line-height: ${props.lineHeight || '1.6'};">${props.content || ''}</div>`;
};

export default {
    page,
    email,
};
