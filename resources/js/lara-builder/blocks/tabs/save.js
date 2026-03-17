/**
 * Tabs Block - Save/Output Generator
 *
 * Generates placeholder for server-side rendering via render.php.
 */

export const page = (props, options = {}) => {
    const serverProps = {
        tabs: props.tabs || [
            { label: 'Tab 1', heading: 'First Tab', description: 'Content for the first tab.', items: [], badges: [] },
        ],
        activeTab: props.activeTab || 0,
        tabStyle: props.tabStyle || 'pills',
        tabAlignment: props.tabAlignment || 'center',
        accentColor: props.accentColor || '#3b82f6',
        contentPadding: props.contentPadding || '24px',
        backgroundColor: props.backgroundColor || '#ffffff',
        tabBgColor: props.tabBgColor || '#f3f4f6',
        layoutStyles: props.layoutStyles || {},
        customClass: props.customClass || '',
    };

    const blockId = options.blockId || props._blockId || '';
    const propsJson = JSON.stringify(serverProps).replace(/'/g, '&#39;');

    return `<div data-lara-block="tabs" data-block-id="${blockId}" data-props='${propsJson}'></div>`;
};

export default { page };
