/**
 * List Block - Save/Output Generators
 *
 * Page context: Returns placeholder for server-side rendering via render.php
 * Email context: Generates inline HTML (email clients can't call server)
 *
 * This approach ensures:
 * - Single source of truth (render.php) for page display
 * - Semantic HTML with proper ul/ol/li elements
 * - Shortcode support in list items (server-side)
 * - Email still works without server calls
 */

/**
 * Generate placeholder for server-side rendering (page context)
 */
export const page = (props, options = {}) => {
    const serverProps = {
        items: props.items || [],
        listType: props.listType || 'bullet',
        color: props.color || '#333333',
        fontSize: props.fontSize || '16px',
        iconColor: props.iconColor || '#635bff',
        layoutStyles: props.layoutStyles || {},
        customCSS: props.customCSS || '',
        customClass: props.customClass || '',
    };

    // Escape for HTML attribute
    const propsJson = JSON.stringify(serverProps).replace(/'/g, '&#39;');

    return `<div data-lara-block="list" data-props='${propsJson}'></div>`;
};

/**
 * Generate HTML for email context (no server rendering available)
 */
export const email = (props, options = {}) => {
    const items = props.items || [];
    const listType = props.listType || 'bullet';
    const color = props.color || '#333333';
    const fontSize = props.fontSize || '16px';
    const iconColor = props.iconColor || '#635bff';

    // Check list uses table layout for email
    if (listType === 'check') {
        const listItems = items.map(item =>
            `<tr><td style="vertical-align: top; padding-right: 8px; color: ${iconColor};">&#10003;</td><td style="color: ${color}; font-size: ${fontSize}; padding-bottom: 8px;">${item}</td></tr>`
        ).join('');

        return `<table style="color: ${color}; font-size: ${fontSize}; line-height: 1.6;">${listItems}</table>`;
    }

    // Regular bullet or numbered list
    const listTag = listType === 'number' ? 'ol' : 'ul';
    const listItems = items.map(item =>
        `<li style="margin-bottom: 8px;">${item}</li>`
    ).join('');

    return `<${listTag} style="color: ${color}; font-size: ${fontSize}; line-height: 1.8; margin: 0; padding-left: 24px;">${listItems}</${listTag}>`;
};

export default {
    page,
    email,
};
