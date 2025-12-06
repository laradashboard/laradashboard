/**
 * Columns Block - Save/Output Generators
 *
 * Generates HTML output for different contexts (page/web and email).
 * Note: This block requires the adapter to pass a generateBlockHtml function in options
 * to recursively render child blocks.
 */

import { buildBlockClasses, mergeBlockStyles } from '@lara-builder/utils';

/**
 * Generate HTML for web/page context
 */
export const page = (props, options = {}) => {
    const { generateBlockHtml } = options;
    const type = 'columns';
    const blockClasses = buildBlockClasses(type, props);
    const gap = props.gap || '20px';
    const columns = props.columns || 2;

    const columnsHtml = (props.children || []).map((columnBlocks) => {
        const columnContent = columnBlocks.map(b => generateBlockHtml ? generateBlockHtml(b, options) : '').join('');
        return `<div class="lb-column" style="flex: 1; min-width: 0;">${columnContent || ''}</div>`;
    }).join('');

    const blockStyles = `display: flex; gap: ${gap}; flex-wrap: wrap`;
    const mergedStyles = mergeBlockStyles(props, blockStyles);

    return `
        <div class="${blockClasses} lb-columns-${columns}" style="${mergedStyles}">
            ${columnsHtml}
        </div>
    `;
};

/**
 * Generate HTML for email context
 */
export const email = (props, options = {}) => {
    const { generateBlockHtml } = options;
    const columnWidth = `${100 / (props.columns || 2)}%`;
    const columnsHtml = (props.children || []).map((columnBlocks, index) => {
        const columnContent = columnBlocks.map(b => generateBlockHtml ? generateBlockHtml(b, options) : '').join('');
        return `<td style="width: ${columnWidth}; vertical-align: top; padding: 0 ${index < (props.columns || 2) - 1 ? props.gap || '20px' : '0'} 0 0;">${columnContent || '&nbsp;'}</td>`;
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
