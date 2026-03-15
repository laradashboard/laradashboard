/**
 * Divider Block - Save/Output Generators
 *
 * Generates HTML output for different contexts (page/web and email).
 */

import { buildBlockClasses, mergeBlockStyles } from '@lara-builder/utils';

/**
 * Generate HTML for web/page context
 */
export const page = (props, options = {}) => {
    const type = 'divider';
    const blockClasses = buildBlockClasses(type, props);
    const blockStyles = [
        'border: none',
        `border-top: ${props.thickness || '1px'} ${props.style || 'solid'} ${props.color || '#e5e7eb'}`,
        `width: ${props.width || '100%'}`,
        `margin: ${props.margin || '20px auto'}`,
    ];

    const mergedStyles = mergeBlockStyles(props, blockStyles.join('; '));
    return `<hr class="${blockClasses}" style="${mergedStyles}" />`;
};

/**
 * Generate placeholder for server-side rendering (email context)
 */
export const email = (props, options = {}) => {
    const serverProps = {
        color: props.color || '#e5e7eb',
        thickness: props.thickness || '1px',
        width: props.width || '100%',
        margin: props.margin || '20px auto',
        style: props.style || 'solid',
        layoutStyles: props.layoutStyles || {},
    };

    const propsJson = JSON.stringify(serverProps).replace(/'/g, '&#39;');

    return `<div data-lara-block="divider" data-props='${propsJson}'></div>`;
};

export default {
    page,
    email,
};
