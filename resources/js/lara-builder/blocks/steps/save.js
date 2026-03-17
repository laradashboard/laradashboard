/**
 * Steps Block - Save/Output Generator
 *
 * Generates placeholder for server-side rendering via render.php.
 */

export const page = (props, options = {}) => {
    const serverProps = {
        steps: props.steps || [
            { title: 'Step 1', description: 'Description', code: '', linkText: '', linkUrl: '' },
        ],
        layout: props.layout || 'vertical',
        showNumbers: props.showNumbers !== false,
        showConnector: props.showConnector !== false,
        numberColor: props.numberColor || '#ffffff',
        numberBgColor: props.numberBgColor || '#3b82f6',
        titleColor: props.titleColor || '#111827',
        titleSize: props.titleSize || '20px',
        descriptionColor: props.descriptionColor || '#6b7280',
        descriptionSize: props.descriptionSize || '14px',
        connectorColor: props.connectorColor || '#e5e7eb',
        codeBackgroundColor: props.codeBackgroundColor || '#1f2937',
        codeTextColor: props.codeTextColor || '#e5e7eb',
        layoutStyles: props.layoutStyles || {},
        customClass: props.customClass || '',
    };

    const blockId = options.blockId || props._blockId || '';
    const propsJson = JSON.stringify(serverProps).replace(/'/g, '&#39;');

    return `<div data-lara-block="steps" data-block-id="${blockId}" data-props='${propsJson}'></div>`;
};

export default { page };
