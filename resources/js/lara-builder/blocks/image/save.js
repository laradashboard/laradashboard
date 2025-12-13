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
 * Generate HTML for email context (no server rendering available)
 */
export const email = (props, options = {}) => {
    const src = props.src || '';
    const alt = props.alt || 'Image';
    const align = props.align || 'center';
    const link = props.link || '';

    const isCustomWidth = props.width === 'custom' && props.customWidth;
    const isCustomHeight = props.height === 'custom' && props.customHeight;
    const imgWidth = isCustomWidth ? props.customWidth : (props.width || '100%');
    const imgHeight = isCustomHeight ? props.customHeight : (props.height || 'auto');

    const imgStyle = `max-width: ${imgWidth};${isCustomWidth ? ` width: ${props.customWidth};` : ''} height: ${imgHeight}; display: block; margin: 0 auto;${imgHeight !== 'auto' ? ' object-fit: cover;' : ''}`;
    const img = `<img src="${src}" alt="${alt}" style="${imgStyle}" />`;

    if (link) {
        return `<a href="${link}" target="_blank" style="display: block; text-align: ${align};">${img}</a>`;
    }

    return `<div style="text-align: ${align};">${img}</div>`;
};

export default {
    page,
    email,
};
