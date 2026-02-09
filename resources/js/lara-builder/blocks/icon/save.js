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
 * Generate HTML for email context
 * Note: Iconify doesn't work in email, we use a placeholder or image
 */
export const email = (props, options = {}) => {
    const {
        icon = 'lucide:star',
        size = '48px',
        color = '#3b82f6',
        align = 'center',
        backgroundColor = '',
        backgroundShape = 'none',
        backgroundPadding = '16px',
    } = props;

    const alignMap = {
        left: 'left',
        center: 'center',
        right: 'right',
    };

    const textAlign = alignMap[align] || 'center';

    // For email, we use the Iconify API to get an SVG/PNG
    // This URL generates an SVG that can be used as an image
    const iconParts = icon.split(':');
    const iconSet = iconParts[0] || 'lucide';
    const iconName = iconParts[1] || 'star';
    const iconUrl = `https://api.iconify.design/${iconSet}/${iconName}.svg?color=${encodeURIComponent(color)}`;

    // Size as number
    const sizeNum = parseInt(size) || 48;

    let iconHtml = `<img src="${iconUrl}" width="${sizeNum}" height="${sizeNum}" alt="" style="display: inline-block; vertical-align: middle;" />`;

    // Add background if needed
    if (backgroundColor && backgroundShape !== 'none') {
        const borderRadius = backgroundShape === 'circle' ? '50%' :
            backgroundShape === 'rounded' ? '12px' : '0';
        const padding = parseInt(backgroundPadding) || 16;

        iconHtml = `
            <div style="display: inline-block; background-color: ${backgroundColor}; padding: ${padding}px; border-radius: ${borderRadius};">
                ${iconHtml}
            </div>
        `;
    }

    return `
        <div style="text-align: ${textAlign}; padding: 8px 0;">
            ${iconHtml}
        </div>
    `;
};

export default {
    page,
    email,
};
