/**
 * Star Rating Block - Save/Output Generator
 *
 * Generates placeholder for server-side rendering via render.php.
 */

export const page = (props, options = {}) => {
    const serverProps = {
        rating: props.rating ?? 5,
        maxStars: props.maxStars || 5,
        size: props.size || 'md',
        filledColor: props.filledColor || '#fbbf24',
        emptyColor: props.emptyColor || '#d1d5db',
        showLabel: props.showLabel || false,
        labelText: props.labelText || '',
        align: props.align || 'center',
        layoutStyles: props.layoutStyles || {},
        customClass: props.customClass || '',
    };

    const blockId = options.blockId || props._blockId || '';
    const propsJson = JSON.stringify(serverProps).replace(/'/g, '&#39;');

    return `<div data-lara-block="star-rating" data-block-id="${blockId}" data-props='${propsJson}'></div>`;
};

export const email = (props, options = {}) => {
    const serverProps = {
        rating: props.rating ?? 5,
        maxStars: props.maxStars || 5,
        size: props.size || 'md',
        filledColor: props.filledColor || '#fbbf24',
        emptyColor: props.emptyColor || '#d1d5db',
        showLabel: props.showLabel || false,
        labelText: props.labelText || '',
        align: props.align || 'center',
        layoutStyles: props.layoutStyles || {},
    };

    const propsJson = JSON.stringify(serverProps).replace(/'/g, '&#39;');

    return `<div data-lara-block="star-rating" data-props='${propsJson}'></div>`;
};

export default { page, email };
