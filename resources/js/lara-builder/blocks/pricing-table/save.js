/**
 * Pricing Table Block - Save/Output Generator
 *
 * Page context: Returns placeholder for server-side rendering.
 */

export const page = (props, options = {}) => {
    const serverProps = {
        planName: props.planName || 'Pro',
        price: props.price || '$29',
        period: props.period || '/month',
        description: props.description || '',
        features: props.features || [],
        buttonText: props.buttonText || 'Get Started',
        buttonLink: props.buttonLink || '#',
        buttonColor: props.buttonColor || '#3b82f6',
        buttonTextColor: props.buttonTextColor || '#ffffff',
        highlighted: props.highlighted || false,
        badgeText: props.badgeText || '',
        backgroundColor: props.backgroundColor || '#ffffff',
        headerColor: props.headerColor || '#111827',
        priceColor: props.priceColor || '#3b82f6',
        textColor: props.textColor || '#6b7280',
        borderColor: props.borderColor || '#e5e7eb',
        borderRadius: props.borderRadius || '16px',
        layoutStyles: props.layoutStyles || {},
        customCSS: props.customCSS || '',
        customClass: props.customClass || '',
    };

    const blockId = options.blockId || props._blockId || '';
    const propsJson = JSON.stringify(serverProps).replace(/'/g, '&#39;');

    return `<div data-lara-block="pricing-table" data-block-id="${blockId}" data-props='${propsJson}'></div>`;
};

export default { page };
