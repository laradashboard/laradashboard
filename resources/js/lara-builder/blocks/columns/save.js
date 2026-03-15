/**
 * Columns Block - Save/Output Generators
 *
 * Generates HTML output for different contexts (page/web and email).
 * Note: This block requires the adapter to pass a generateBlockHtml function in options
 * to recursively render child blocks.
 */

import { buildBlockClasses, mergeBlockStyles } from '@lara-builder/utils';

// Map alignment values to CSS
const alignItemsMap = {
    'start': 'flex-start',
    'center': 'center',
    'end': 'flex-end',
    'stretch': 'stretch',
};

const justifyContentMap = {
    'start': 'flex-start',
    'center': 'center',
    'end': 'flex-end',
    'stretch': 'stretch',
    'space-between': 'space-between',
    'space-around': 'space-around',
};

/**
 * Generate HTML for web/page context
 */
export const page = (props, options = {}) => {
    const { generateBlockHtml } = options;
    const type = 'columns';
    const blockClasses = buildBlockClasses(type, props);
    const gap = props.gap || '20px';
    const columns = props.columns || 2;
    const verticalAlign = props.verticalAlign || 'stretch';
    const horizontalAlign = props.horizontalAlign || 'stretch';
    const stackOnMobile = props.stackOnMobile !== false;

    const alignItems = alignItemsMap[verticalAlign] || 'stretch';
    const justifyContent = justifyContentMap[horizontalAlign] || 'stretch';

    // Calculate column width
    const columnWidth = horizontalAlign === 'stretch'
        ? `flex: 1 1 calc(${100 / columns}% - ${gap})`
        : `flex: 0 0 auto; width: calc(${100 / columns}% - ${gap})`;

    const columnsHtml = (props.children || []).map((columnBlocks) => {
        const columnContent = columnBlocks.map(b => generateBlockHtml ? generateBlockHtml(b, options) : '').join('');
        return `<div class="lb-column" style="${columnWidth}; min-width: 0;">${columnContent || ''}</div>`;
    }).join('');

    const blockStyles = [
        'display: flex',
        'flex-wrap: wrap',
        `gap: ${gap}`,
        `align-items: ${alignItems}`,
        `justify-content: ${justifyContent}`,
    ].join('; ');

    const mergedStyles = mergeBlockStyles(props, blockStyles);

    // Add responsive class for mobile stacking
    const responsiveClass = stackOnMobile ? 'lb-columns-stack-mobile' : '';

    return `
        <div class="${blockClasses} lb-columns-${columns} ${responsiveClass}" style="${mergedStyles}">
            ${columnsHtml}
        </div>
    `;
};

/**
 * Generate placeholder for server-side rendering (email context)
 * Children are serialized into props for recursive server-side rendering
 */
export const email = (props, options = {}) => {
    const serverProps = {
        columns: props.columns || 2,
        gap: props.gap || '20px',
        verticalAlign: props.verticalAlign || 'stretch',
        horizontalAlign: props.horizontalAlign || 'stretch',
        children: props.children || [],
        layoutStyles: props.layoutStyles || {},
    };

    const propsJson = JSON.stringify(serverProps).replace(/'/g, '&#39;');

    return `<div data-lara-block="columns" data-props='${propsJson}'></div>`;
};

export default {
    page,
    email,
};
