/**
 * Button Block - Save/Output Generators
 *
 * Page context: Returns placeholder for server-side rendering via render.php
 * Email context: Generates inline HTML (email clients can't call server)
 *
 * This approach ensures:
 * - Single source of truth (render.php) for page display
 * - Security: URL sanitization happens server-side
 * - Email still works without server calls
 */

/**
 * Generate placeholder for server-side rendering (page context)
 */
export const page = (props, options = {}) => {
    const serverProps = {
        text: props.text || 'Click Here',
        link: props.link || '',
        target: props.target || '_self',
        align: props.align || 'center',
        backgroundColor: props.backgroundColor || '#635bff',
        textColor: props.textColor || '#ffffff',
        borderRadius: props.borderRadius || '6px',
        padding: props.padding || '12px 24px',
        fontSize: props.fontSize || '16px',
        fontWeight: props.fontWeight || '600',
        nofollow: props.nofollow || false,
        sponsored: props.sponsored || false,
        layoutStyles: props.layoutStyles || {},
        customCSS: props.customCSS || '',
        customClass: props.customClass || '',
    };

    // Escape for HTML attribute
    const propsJson = JSON.stringify(serverProps).replace(/'/g, '&#39;');

    return `<div data-lara-block="button" data-props='${propsJson}'></div>`;
};

/**
 * Generate placeholder for server-side rendering (email context)
 */
export const email = (props, options = {}) => {
    const serverProps = {
        text: props.text || 'Click Here',
        link: props.link || '',
        target: props.target || '_self',
        align: props.align || 'center',
        backgroundColor: props.backgroundColor || '#635bff',
        textColor: props.textColor || '#ffffff',
        borderRadius: props.borderRadius || '6px',
        padding: props.padding || '12px 24px',
        fontSize: props.fontSize || '16px',
        fontWeight: props.fontWeight || '600',
        layoutStyles: props.layoutStyles || {},
    };

    const propsJson = JSON.stringify(serverProps).replace(/'/g, '&#39;');

    return `<div data-lara-block="button" data-props='${propsJson}'></div>`;
};

export default {
    page,
    email,
};
