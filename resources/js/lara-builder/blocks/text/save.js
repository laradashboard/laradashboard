/**
 * Text Block - Save/Output Generators
 *
 * Page context: Returns placeholder for server-side rendering via render.php
 * Email context: Generates inline HTML (email clients can't call server)
 *
 * This approach ensures:
 * - Single source of truth (render.php) for page display
 * - Future shortcode/variable replacement server-side
 * - Email still works without server calls
 */

/**
 * Generate placeholder for server-side rendering (page context)
 */
export const page = (props, options = {}) => {
    const serverProps = {
        content: props.content || '',
        align: props.align || 'left',
        color: props.color || '#666666',
        fontSize: props.fontSize || '16px',
        lineHeight: props.lineHeight || '1.6',
        layoutStyles: props.layoutStyles || {},
        customCSS: props.customCSS || '',
        customClass: props.customClass || '',
    };

    // Escape for HTML attribute
    const propsJson = JSON.stringify(serverProps).replace(/'/g, '&#39;');

    return `<div data-lara-block="text" data-props='${propsJson}'></div>`;
};

/**
 * Generate HTML for email context (no server rendering available)
 */
export const email = (props, options = {}) => {
    const content = props.content || '';
    const align = props.align || 'left';
    const color = props.color || '#333333';
    const fontSize = props.fontSize || '16px';
    const lineHeight = props.lineHeight || '1.6';

    return `<div style="text-align: ${align}; color: ${color}; font-size: ${fontSize}; line-height: ${lineHeight};">${content}</div>`;
};

export default {
    page,
    email,
};
