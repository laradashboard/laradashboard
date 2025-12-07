import { buildBlockClasses, mergeBlockStyles } from '@lara-builder/utils';

/**
 * Generate HTML for web/page context
 */
export const page = (props, options = {}) => {
    const type = 'list';
    const listTag = props.listType === 'number' ? 'ol' : 'ul';
    const blockClasses = buildBlockClasses(type, props);

    const blockStyles = [
        `line-height: 1.8`,
        `margin: 0`,
        props.listType === 'check' ? 'list-style: none; padding-left: 0;' : 'padding-left: 24px;',
    ];

    // Only add if not controlled by layoutStyles
    if (!props.layoutStyles?.typography?.color) {
        blockStyles.push(`color: ${props.color || '#333333'}`);
    }
    if (!props.layoutStyles?.typography?.fontSize) {
        blockStyles.push(`font-size: ${props.fontSize || '16px'}`);
    }

    const mergedStyles = mergeBlockStyles(props, blockStyles.join('; '));

    const items = (props.items || []).map(item => {
        if (props.listType === 'check') {
            return `<li style="display: flex; align-items: flex-start; gap: 8px; margin-bottom: 8px;">
                <span style="color: ${props.iconColor || '#635bff'}; flex-shrink: 0;">✓</span>
                <span>${item}</span>
            </li>`;
        }
        return `<li style="margin-bottom: 8px;">${item}</li>`;
    }).join('');

    return `<${listTag} class="${blockClasses} lb-list-${props.listType || 'bullet'}" style="${mergedStyles}">${items}</${listTag}>`;
};

/**
 * Generate HTML for email context
 */
export const email = (props, options = {}) => {
    const listItems = (props.items || []).map(item => {
        if (props.listType === 'check') {
            return `<tr><td style="vertical-align: top; padding-right: 8px; color: ${props.iconColor || '#635bff'};">&#10003;</td><td style="color: ${props.color || '#333333'}; font-size: ${props.fontSize || '16px'}; padding-bottom: 8px;">${item}</td></tr>`;
        }
        return `<li style="margin-bottom: 8px;">${item}</li>`;
    }).join('');

    if (props.listType === 'check') {
        return `<table style="color: ${props.color || '#333333'}; font-size: ${props.fontSize || '16px'}; line-height: 1.6;">${listItems}</table>`;
    }
    const listTag = props.listType === 'number' ? 'ol' : 'ul';
    return `<${listTag} style="color: ${props.color || '#333333'}; font-size: ${props.fontSize || '16px'}; line-height: 1.8; margin: 0; padding-left: 24px;">${listItems}</${listTag}>`;
};

export default {
    page,
    email,
};
