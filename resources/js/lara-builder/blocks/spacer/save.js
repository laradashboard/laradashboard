/**
 * Spacer Block - Save/Output Generators
 *
 * Generates HTML output for different contexts (page/web and email).
 */

import { buildBlockClasses, mergeBlockStyles } from '@lara-builder/utils';

/**
 * Generate HTML for web/page context
 */
export const page = (props, options = {}) => {
    const type = 'spacer';
    const classes = buildBlockClasses(type, props);
    const blockStyles = `height: ${props.height || '20px'}`;
    const mergedStyles = mergeBlockStyles(props, blockStyles);
    return `<div class="${classes}" style="${mergedStyles}"></div>`;
};

/**
 * Generate HTML for email context
 */
export const email = (props, options = {}) => {
    return `<div style="height: ${props.height || '20px'};"></div>`;
};

export default {
    page,
    email,
};
