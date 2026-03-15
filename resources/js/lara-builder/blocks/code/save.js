/**
 * Code Block - Save/Output Generators
 *
 * Page context: Returns placeholder for server-side rendering via render.php
 * Email context: Generates inline HTML (email clients can't call server)
 *
 * This approach ensures:
 * - Single source of truth (render.php) for page display
 * - Proper XSS protection (server-side escaping)
 * - Future syntax highlighting support (server-side)
 * - Email still works without server calls
 */

/**
 * Generate placeholder for server-side rendering (page context)
 */
export const page = (props, options = {}) => {
    const serverProps = {
        code: props.code || '',
        language: props.language || 'plaintext',
        fontSize: props.fontSize || '14px',
        backgroundColor: props.backgroundColor || '#1e1e1e',
        textColor: props.textColor || '#d4d4d4',
        borderRadius: props.borderRadius || '8px',
        layoutStyles: props.layoutStyles || {},
        customCSS: props.customCSS || '',
        customClass: props.customClass || '',
    };

    // Escape for HTML attribute
    const propsJson = JSON.stringify(serverProps).replace(/'/g, '&#39;');

    return `<div data-lara-block="code" data-props='${propsJson}'></div>`;
};

/**
 * Generate placeholder for server-side rendering (email context)
 */
export const email = (props, options = {}) => {
    const serverProps = {
        code: props.code || '',
        language: props.language || 'plaintext',
        fontSize: props.fontSize || '14px',
        backgroundColor: props.backgroundColor || '#1e1e1e',
        textColor: props.textColor || '#d4d4d4',
        borderRadius: props.borderRadius || '8px',
        layoutStyles: props.layoutStyles || {},
    };

    const propsJson = JSON.stringify(serverProps).replace(/'/g, '&#39;');

    return `<div data-lara-block="code" data-props='${propsJson}'></div>`;
};

export default {
    page,
    email,
};
