/**
 * Icon Block - Save/Output Generators
 *
 * Page context: Returns placeholder for server-side rendering
 * Email context: Generates inline HTML with image fallback
 */

import { buildBlockClasses, mergeBlockStyles } from '@lara-builder/utils';

/**
 * Generate placeholder for server-side rendering (page context)
 */
export const page = (props, options = {}) => {
    const serverProps = {
        icon: props.icon || 'lucide:star',
        size: props.size || '48px',
        color: props.color || '#3b82f6',
        align: props.align || 'center',
        backgroundColor: props.backgroundColor || '',
        backgroundShape: props.backgroundShape || 'none',
        backgroundPadding: props.backgroundPadding || '16px',
        layoutStyles: props.layoutStyles || {},
        customCSS: props.customCSS || '',
        customClass: props.customClass || '',
    };

    const blockId = options.blockId || props._blockId || '';
    const propsJson = JSON.stringify(serverProps).replace(/'/g, '&#39;');

    return `<div data-lara-block="icon" data-block-id="${blockId}" data-props='${propsJson}'></div>`;
};

/**
 * Generate placeholder for server-side rendering (email context)
 */
export const email = (props, options = {}) => {
    const serverProps = {
        icon: props.icon || 'lucide:star',
        size: props.size || '48px',
        color: props.color || '#3b82f6',
        align: props.align || 'center',
        backgroundColor: props.backgroundColor || '',
        backgroundShape: props.backgroundShape || 'none',
        backgroundPadding: props.backgroundPadding || '16px',
        layoutStyles: props.layoutStyles || {},
    };

    const propsJson = JSON.stringify(serverProps).replace(/'/g, '&#39;');

    return `<div data-lara-block="icon" data-props='${propsJson}'></div>`;
};

export default {
    page,
    email,
};
