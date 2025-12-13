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
 * Generate HTML for email context (no server rendering available)
 */
export const email = (props, options = {}) => {
    const text = props.text || 'Click Here';
    const link = props.link || '#';
    const align = props.align || 'center';
    const backgroundColor = props.backgroundColor || '#635bff';
    const textColor = props.textColor || '#ffffff';
    const borderRadius = props.borderRadius || '6px';
    const padding = props.padding || '12px 24px';
    const fontSize = props.fontSize || '16px';
    const fontWeight = props.fontWeight || '600';

    return `
        <div style="text-align: ${align}; padding: 10px 0;">
            <a href="${link}" target="_blank" style="display: inline-block; background-color: ${backgroundColor}; color: ${textColor}; padding: ${padding}; border-radius: ${borderRadius}; text-decoration: none; font-size: ${fontSize}; font-weight: ${fontWeight};">${text}</a>
        </div>
    `;
};

export default {
    page,
    email,
};
