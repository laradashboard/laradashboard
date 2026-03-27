/**
 * Badge Block - Save/Output Generators
 *
 * Page context: Returns placeholder for server-side rendering via render.php
 * Email context: Returns placeholder for server-side rendering
 */

/**
 * Generate placeholder for server-side rendering (page context)
 */
export const page = (props, options = {}) => {
    const serverProps = {
        text: props.text || 'NEW',
        variant: props.variant || 'soft',
        size: props.size || 'sm',
        color: props.color || '#3b82f6',
        textColor: props.textColor || '#1e40af',
        icon: props.icon || '',
        borderRadius: props.borderRadius || 'pill',
        align: props.align || 'center',
        layoutStyles: props.layoutStyles || {},
        customCSS: props.customCSS || '',
        customClass: props.customClass || '',
    };

    const blockId = options.blockId || props._blockId || '';
    const propsJson = JSON.stringify(serverProps).replace(/'/g, '&#39;');

    return `<div data-lara-block="badge" data-block-id="${blockId}" data-props='${propsJson}'></div>`;
};

/**
 * Generate placeholder for server-side rendering (email context)
 */
export const email = (props, options = {}) => {
    const serverProps = {
        text: props.text || 'NEW',
        variant: props.variant || 'soft',
        size: props.size || 'sm',
        color: props.color || '#3b82f6',
        textColor: props.textColor || '#1e40af',
        icon: props.icon || '',
        borderRadius: props.borderRadius || 'pill',
        align: props.align || 'center',
        layoutStyles: props.layoutStyles || {},
    };

    const propsJson = JSON.stringify(serverProps).replace(/'/g, '&#39;');

    return `<div data-lara-block="badge" data-props='${propsJson}'></div>`;
};

export default { page, email };
