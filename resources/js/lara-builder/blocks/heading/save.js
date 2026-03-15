/**
 * Heading Block - Save/Output Generators
 *
 * Page context: Returns placeholder for server-side rendering via render.php
 * Email context: Generates inline HTML (email clients can't call server)
 *
 * This approach ensures:
 * - Single source of truth (render.php) for page display
 * - No duplicate logic to maintain
 * - Email still works without server calls
 */

/**
 * Generate placeholder for server-side rendering (page context)
 */
export const page = (props, options = {}) => {
    const serverProps = {
        text: props.text || '',
        level: props.level || 'h2',
        align: props.align || 'left',
        color: props.color || '#333333',
        fontSize: props.fontSize || '32px',
        fontWeight: props.fontWeight || 'bold',
        lineHeight: props.lineHeight || '1.2',
        letterSpacing: props.letterSpacing || '0',
        layoutStyles: props.layoutStyles || {},
        customCSS: props.customCSS || '',
        customClass: props.customClass || '',
    };

    // Include blockId for anchor generation
    const blockId = options.blockId || props._blockId || '';

    // Escape for HTML attribute
    const propsJson = JSON.stringify(serverProps).replace(/'/g, '&#39;');

    return `<div data-lara-block="heading" data-block-id="${blockId}" data-props='${propsJson}'></div>`;
};

/**
 * Generate placeholder for server-side rendering (email context)
 * render.php handles inline-styled email output
 */
export const email = (props, options = {}) => {
    const serverProps = {
        text: props.text || '',
        level: props.level || 'h2',
        align: props.align || 'left',
        color: props.color || '#333333',
        fontSize: props.fontSize || '32px',
        fontWeight: props.fontWeight || 'bold',
        lineHeight: props.lineHeight || '1.2',
        letterSpacing: props.letterSpacing || '0',
        layoutStyles: props.layoutStyles || {},
    };

    const blockId = options.blockId || props._blockId || '';
    const propsJson = JSON.stringify(serverProps).replace(/'/g, '&#39;');

    return `<div data-lara-block="heading" data-block-id="${blockId}" data-props='${propsJson}'></div>`;
};

export default {
    page,
    email,
};
