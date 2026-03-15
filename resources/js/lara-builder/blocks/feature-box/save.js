/**
 * Feature Box Block - Save/Output Generators
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
        icon: props.icon || 'lucide:star',
        iconSize: props.iconSize || '32px',
        iconColor: props.iconColor || '#3b82f6',
        iconBackgroundColor: props.iconBackgroundColor || '#dbeafe',
        iconBackgroundShape: props.iconBackgroundShape || 'circle',
        title: props.title || 'Feature Title',
        titleColor: props.titleColor || '#111827',
        titleSize: props.titleSize || '18px',
        description: props.description || '',
        descriptionColor: props.descriptionColor || '#6b7280',
        descriptionSize: props.descriptionSize || '14px',
        align: props.align || 'center',
        gap: props.gap || '16px',
        layoutStyles: props.layoutStyles || {},
        customCSS: props.customCSS || '',
        customClass: props.customClass || '',
    };

    const blockId = options.blockId || props._blockId || '';
    const propsJson = JSON.stringify(serverProps).replace(/'/g, '&#39;');

    return `<div data-lara-block="feature-box" data-block-id="${blockId}" data-props='${propsJson}'></div>`;
};

/**
 * Generate placeholder for server-side rendering (email context)
 */
export const email = (props, options = {}) => {
    const serverProps = {
        icon: props.icon || 'lucide:star',
        iconSize: props.iconSize || '32px',
        iconColor: props.iconColor || '#3b82f6',
        iconBackgroundColor: props.iconBackgroundColor || '#dbeafe',
        iconBackgroundShape: props.iconBackgroundShape || 'circle',
        title: props.title || 'Feature Title',
        titleColor: props.titleColor || '#111827',
        titleSize: props.titleSize || '18px',
        description: props.description || '',
        descriptionColor: props.descriptionColor || '#6b7280',
        descriptionSize: props.descriptionSize || '14px',
        align: props.align || 'center',
        layoutStyles: props.layoutStyles || {},
    };

    const propsJson = JSON.stringify(serverProps).replace(/'/g, '&#39;');

    return `<div data-lara-block="feature-box" data-props='${propsJson}'></div>`;
};

export default {
    page,
    email,
};
