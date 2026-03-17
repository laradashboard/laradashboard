/**
 * Testimonial Block - Save/Output Generators
 *
 * Page context: Returns placeholder for server-side rendering
 */

/**
 * Generate placeholder for server-side rendering (page context)
 */
export const page = (props, options = {}) => {
    const serverProps = {
        quote: props.quote || 'This product has completely transformed our workflow. Highly recommended!',
        authorName: props.authorName || 'John Doe',
        authorRole: props.authorRole || 'CEO, Company',
        avatarUrl: props.avatarUrl || '',
        rating: props.rating ?? 5,
        showRating: props.showRating ?? true,
        cardStyle: props.cardStyle || 'shadow',
        backgroundColor: props.backgroundColor || '#ffffff',
        textColor: props.textColor || '#374151',
        nameColor: props.nameColor || '#111827',
        ratingColor: props.ratingColor || '#fbbf24',
        borderColor: props.borderColor || '#e5e7eb',
        layoutStyles: props.layoutStyles || {},
        customCSS: props.customCSS || '',
        customClass: props.customClass || '',
    };

    const blockId = options.blockId || props._blockId || '';
    const propsJson = JSON.stringify(serverProps).replace(/'/g, '&#39;');

    return `<div data-lara-block="testimonial" data-block-id="${blockId}" data-props='${propsJson}'></div>`;
};

export default {
    page,
};
