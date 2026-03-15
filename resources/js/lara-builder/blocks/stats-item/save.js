/**
 * Stats Item Block - Save/Output Generators
 *
 * Page context: Returns placeholder for server-side rendering
 * Email context: Generates inline HTML
 */

import { buildBlockClasses, mergeBlockStyles } from '@lara-builder/utils';

/**
 * Generate placeholder for server-side rendering (page context)
 */
export const page = (props, options = {}) => {
    const serverProps = {
        value: props.value || '100+',
        valueColor: props.valueColor || '#3b82f6',
        valueSize: props.valueSize || '48px',
        label: props.label || 'Happy Customers',
        labelColor: props.labelColor || '#6b7280',
        labelSize: props.labelSize || '14px',
        align: props.align || 'center',
        prefix: props.prefix || '',
        suffix: props.suffix || '',
        layoutStyles: props.layoutStyles || {},
        customCSS: props.customCSS || '',
        customClass: props.customClass || '',
    };

    const blockId = options.blockId || props._blockId || '';
    const propsJson = JSON.stringify(serverProps).replace(/'/g, '&#39;');

    return `<div data-lara-block="stats-item" data-block-id="${blockId}" data-props='${propsJson}'></div>`;
};

/**
 * Generate placeholder for server-side rendering (email context)
 */
export const email = (props, options = {}) => {
    const serverProps = {
        value: props.value || '100+',
        valueColor: props.valueColor || '#3b82f6',
        valueSize: props.valueSize || '48px',
        label: props.label || 'Happy Customers',
        labelColor: props.labelColor || '#6b7280',
        labelSize: props.labelSize || '14px',
        align: props.align || 'center',
        prefix: props.prefix || '',
        suffix: props.suffix || '',
        layoutStyles: props.layoutStyles || {},
    };

    const propsJson = JSON.stringify(serverProps).replace(/'/g, '&#39;');

    return `<div data-lara-block="stats-item" data-props='${propsJson}'></div>`;
};

export default {
    page,
    email,
};
