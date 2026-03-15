/**
 * Image Block - Save/Output Generators
 *
 * Page context: Returns placeholder for server-side rendering via render.php
 * Email context: Generates inline HTML (email clients can't call server)
 *
 * This approach ensures:
 * - Single source of truth (render.php) for page display
 * - CDN/optimization can be applied server-side
 * - Email still works without server calls
 */

/**
 * Generate placeholder for server-side rendering (page context)
 */
export const page = (props, options = {}) => {
    const serverProps = {
        src: props.src || '',
        alt: props.alt || 'Image',
        width: props.width || '100%',
        height: props.height || 'auto',
        customWidth: props.customWidth || '',
        customHeight: props.customHeight || '',
        align: props.align || 'center',
        link: props.link || '',
        layoutStyles: props.layoutStyles || {},
        customCSS: props.customCSS || '',
        customClass: props.customClass || '',
    };

    // Escape for HTML attribute
    const propsJson = JSON.stringify(serverProps).replace(/'/g, '&#39;');

    return `<div data-lara-block="image" data-props='${propsJson}'></div>`;
};

/**
 * Generate placeholder for server-side rendering (email context)
 */
export const email = (props, options = {}) => {
    const serverProps = {
        src: props.src || '',
        alt: props.alt || 'Image',
        width: props.width || '100%',
        height: props.height || 'auto',
        customWidth: props.customWidth || '',
        customHeight: props.customHeight || '',
        align: props.align || 'center',
        link: props.link || '',
        layoutStyles: props.layoutStyles || {},
    };

    const propsJson = JSON.stringify(serverProps).replace(/'/g, '&#39;');

    return `<div data-lara-block="image" data-props='${propsJson}'></div>`;
};

export default {
    page,
    email,
};
