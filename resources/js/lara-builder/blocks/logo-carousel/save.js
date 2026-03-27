/**
 * Logo Carousel Block - Save/Output Generator
 *
 * Page context: Returns placeholder for server-side rendering.
 * The marquee animation is handled entirely in render.php.
 */

export const page = (props, options = {}) => {
    const serverProps = {
        images: props.images || [],
        speed: props.speed || 30,
        direction: props.direction || 'left',
        pauseOnHover: props.pauseOnHover !== false,
        gap: props.gap || '48px',
        imageHeight: props.imageHeight || '40px',
        grayscale: props.grayscale !== false,
        headingText: props.headingText || '',
        headingColor: props.headingColor || '#6b7280',
        headingSize: props.headingSize || '14px',
        layoutStyles: props.layoutStyles || {},
        customCSS: props.customCSS || '',
        customClass: props.customClass || '',
    };

    const blockId = options.blockId || props._blockId || '';
    const propsJson = JSON.stringify(serverProps).replace(/'/g, '&#39;');

    return `<div data-lara-block="logo-carousel" data-block-id="${blockId}" data-props='${propsJson}'></div>`;
};

export default { page };
