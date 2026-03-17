/**
 * Alert Banner Block - Save/Output Generator
 *
 * Page context: Returns placeholder for server-side rendering via render.php
 */

/**
 * Generate placeholder for server-side rendering (page context)
 */
export const page = (props, options = {}) => {
    const serverProps = {
        text: props.text || 'AI-powered module marketplace is live',
        badgeText: props.badgeText || 'NEW',
        linkText: props.linkText || '',
        linkUrl: props.linkUrl || '#',
        backgroundColor: props.backgroundColor || '#1e1b4b',
        textColor: props.textColor || '#e0e7ff',
        badgeColor: props.badgeColor || '#6366f1',
        badgeTextColor: props.badgeTextColor || '#ffffff',
        align: props.align || 'center',
        padding: props.padding || '12px 24px',
        layoutStyles: props.layoutStyles || {},
        customCSS: props.customCSS || '',
        customClass: props.customClass || '',
    };

    const blockId = options.blockId || props._blockId || '';
    const propsJson = JSON.stringify(serverProps).replace(/'/g, '&#39;');

    return `<div data-lara-block="alert-banner" data-block-id="${blockId}" data-props='${propsJson}'></div>`;
};

export default { page };
