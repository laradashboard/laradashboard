/**
 * HTML Block - Save/Output Generators
 *
 * Generates HTML output for different contexts (page/web and email).
 */

import { buildBlockClasses, mergeBlockStyles } from '@lara-builder/utils';

/**
 * Generate HTML for web/page context
 */
export const page = (props, options = {}) => {
    const type = 'html';
    const classes = buildBlockClasses(type, props);
    const mergedStyles = mergeBlockStyles(props);
    return `<div class="${classes}"${mergedStyles ? ` style="${mergedStyles}"` : ''}>${props.code || ''}</div>`;
};

/**
 * Generate HTML for email context
 */
export const email = (props, options = {}) => {
    return props.code || '';
};

export default {
    page,
    email,
};
