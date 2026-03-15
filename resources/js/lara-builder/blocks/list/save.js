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
 * Generate placeholder for server-side rendering (email context)
 */
export const email = (props, options = {}) => {
    const serverProps = {
        items: props.items || [],
        listType: props.listType || 'bullet',
        color: props.color || '#333333',
        fontSize: props.fontSize || '16px',
        iconColor: props.iconColor || '#635bff',
        layoutStyles: props.layoutStyles || {},
    };

    const propsJson = JSON.stringify(serverProps).replace(/'/g, '&#39;');

    return `<div data-lara-block="list" data-props='${propsJson}'></div>`;
};

export default {
    page,
    email,
};
