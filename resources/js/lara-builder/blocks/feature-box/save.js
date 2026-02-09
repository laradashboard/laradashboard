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
 * Generate HTML for email context
 */
export const email = (props, options = {}) => {
    const {
        icon = 'lucide:star',
        iconSize = '32px',
        iconColor = '#3b82f6',
        iconBackgroundColor = '#dbeafe',
        iconBackgroundShape = 'circle',
        title = 'Feature Title',
        titleColor = '#111827',
        titleSize = '18px',
        description = '',
        descriptionColor = '#6b7280',
        descriptionSize = '14px',
        align = 'center',
    } = props;

    const alignMap = {
        left: 'left',
        center: 'center',
        right: 'right',
    };

    const textAlign = alignMap[align] || 'center';

    // Generate icon using Iconify API
    const iconParts = icon.split(':');
    const iconSet = iconParts[0] || 'lucide';
    const iconName = iconParts[1] || 'star';
    const iconUrl = `https://api.iconify.design/${iconSet}/${iconName}.svg?color=${encodeURIComponent(iconColor)}`;
    const sizeNum = parseInt(iconSize) || 32;

    let iconHtml = `<img src="${iconUrl}" width="${sizeNum}" height="${sizeNum}" alt="" style="display: inline-block;" />`;

    // Add background if needed
    if (iconBackgroundColor && iconBackgroundShape !== 'none') {
        const borderRadius = iconBackgroundShape === 'circle' ? '50%' :
            iconBackgroundShape === 'rounded' ? '12px' : '0';

        iconHtml = `
            <div style="display: inline-block; background-color: ${iconBackgroundColor}; padding: 16px; border-radius: ${borderRadius};">
                ${iconHtml}
            </div>
        `;
    }

    return `
        <div style="text-align: ${textAlign}; padding: 16px;">
            <div style="margin-bottom: 16px;">
                ${iconHtml}
            </div>
            <h3 style="color: ${titleColor}; font-size: ${titleSize}; font-weight: 600; margin: 0 0 8px 0;">
                ${title}
            </h3>
            <p style="color: ${descriptionColor}; font-size: ${descriptionSize}; margin: 0; line-height: 1.5;">
                ${description}
            </p>
        </div>
    `;
};

export default {
    page,
    email,
};
