/**
 * Button Group Block - Save/Output Generator
 *
 * Page context: Returns placeholder for server-side rendering via render.php
 */

/**
 * Generate placeholder for server-side rendering (page context)
 */
export const page = (props, options = {}) => {
    const serverProps = {
        buttons: (props.buttons || []).map((btn) => ({
            text: btn.text || 'Button',
            link: btn.link || '#',
            variant: btn.variant || 'solid',
            backgroundColor: btn.backgroundColor || '#3b82f6',
            textColor: btn.textColor || '#ffffff',
            icon: btn.icon || '',
            iconPosition: btn.iconPosition || 'left',
        })),
        alignment: props.alignment || 'center',
        gap: props.gap || '12px',
        size: props.size || 'md',
        stackOnMobile: props.stackOnMobile !== false,
        borderRadius: props.borderRadius || '8px',
        layoutStyles: props.layoutStyles || {},
        customCSS: props.customCSS || '',
        customClass: props.customClass || '',
    };

    const blockId = options.blockId || props._blockId || '';
    const propsJson = JSON.stringify(serverProps).replace(/'/g, '&#39;');

    return `<div data-lara-block="button-group" data-block-id="${blockId}" data-props='${propsJson}'></div>`;
};

export default { page };
