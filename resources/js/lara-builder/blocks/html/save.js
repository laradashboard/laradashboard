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
 * Generate placeholder for server-side rendering (email context)
 */
export const email = (props, options = {}) => {
    const serverProps = {
        code: props.code || '',
        layoutStyles: props.layoutStyles || {},
    };

    const propsJson = JSON.stringify(serverProps).replace(/'/g, '&#39;');

    return `<div data-lara-block="html" data-props='${propsJson}'></div>`;
};

export default {
    page,
    email,
};
