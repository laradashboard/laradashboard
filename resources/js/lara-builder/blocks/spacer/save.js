/**
 * Spacer Block - Save/Output Generators
 *
 * Uses the new factory helpers for cleaner code.
 */

import { buildBlockClasses, mergeBlockStyles } from '@lara-builder/utils';

/**
 * Generate HTML for web/page context
 */
export const page = (props, options = {}) => {
    const classes = buildBlockClasses('spacer', props);
    const blockStyles = `height: ${props.height || '20px'}`;
    const mergedStyles = mergeBlockStyles(props, blockStyles);
    return `<div class="${classes}" style="${mergedStyles}"></div>`;
};

/**
 * Generate placeholder for server-side rendering (email context)
 */
export const email = (props, options = {}) => {
    const serverProps = {
        height: props.height || '20px',
        layoutStyles: props.layoutStyles || {},
    };

    const propsJson = JSON.stringify(serverProps).replace(/'/g, '&#39;');

    return `<div data-lara-block="spacer" data-props='${propsJson}'></div>`;
};

export default { page, email };
