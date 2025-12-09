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
 * Generate HTML for email context
 */
export const email = (props, options = {}) => {
    const { generateBlockHtml } = options;
    const verticalAlign = props.verticalAlign || 'stretch';

    // Map vertical align to email-compatible values
    const emailVerticalAlign = {
        'start': 'top',
        'center': 'middle',
        'end': 'bottom',
        'stretch': 'top',
    };

    const columnWidth = `${100 / (props.columns || 2)}%`;
    const valign = emailVerticalAlign[verticalAlign] || 'top';

    const columnsHtml = (props.children || []).map((columnBlocks, index) => {
        const columnContent = columnBlocks.map(b => generateBlockHtml ? generateBlockHtml(b, options) : '').join('');
        return `<td style="width: ${columnWidth}; vertical-align: ${valign}; padding: 0 ${index < (props.columns || 2) - 1 ? props.gap || '20px' : '0'} 0 0;">${columnContent || '&nbsp;'}</td>`;
    }).join('');

    return `
        <table width="100%" cellpadding="0" cellspacing="0" border="0">
            <tr>${columnsHtml}</tr>
        </table>
    `;
};

export default {
    page,
    email,
};
