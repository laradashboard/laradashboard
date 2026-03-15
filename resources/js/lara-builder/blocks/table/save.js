/**
 * Table Block - Save/Output Generators
 *
 * Generates HTML output for different contexts (page/web and email).
 */

import { buildBlockClasses, mergeBlockStyles } from '@lara-builder/utils';

/**
 * Generate HTML for web/page context
 */
export const page = (props, options = {}) => {
    const type = 'table';
    const blockClasses = buildBlockClasses(type, props);
    const tableHeaders = (props.headers || []).map(header =>
        `<th style="background-color: ${props.headerBgColor || '#f1f5f9'}; color: ${props.headerTextColor || '#1e293b'}; padding: ${props.cellPadding || '12px'}; text-align: left; font-weight: 600; border-bottom: 2px solid ${props.borderColor || '#e2e8f0'};">${header}</th>`
    ).join('');

    const textColor = props.layoutStyles?.typography?.color || '#374151';
    const tableRows = (props.rows || []).map(row =>
        `<tr>${row.map(cell => `<td style="padding: ${props.cellPadding || '12px'}; border-bottom: 1px solid ${props.borderColor || '#e2e8f0'}; color: ${textColor};">${cell}</td>`).join('')}</tr>`
    ).join('');

    const fontSize = props.layoutStyles?.typography?.fontSize || props.fontSize || '14px';
    const blockStyles = `overflow-x: auto`;
    const mergedStyles = mergeBlockStyles(props, blockStyles);

    return `
        <div class="${blockClasses}" style="${mergedStyles}">
            <table class="lb-table-inner" style="width: 100%; font-size: ${fontSize}; border-collapse: collapse;">
                ${props.showHeader && tableHeaders ? `<thead><tr>${tableHeaders}</tr></thead>` : ''}
                <tbody>${tableRows}</tbody>
            </table>
        </div>
    `;
};

/**
 * Generate placeholder for server-side rendering (email context)
 */
export const email = (props, options = {}) => {
    const serverProps = {
        headers: props.headers || [],
        rows: props.rows || [],
        showHeader: props.showHeader ?? true,
        headerBgColor: props.headerBgColor || '#f1f5f9',
        headerTextColor: props.headerTextColor || '#1e293b',
        borderColor: props.borderColor || '#e2e8f0',
        cellPadding: props.cellPadding || '12px',
        fontSize: props.fontSize || '14px',
        layoutStyles: props.layoutStyles || {},
    };

    const propsJson = JSON.stringify(serverProps).replace(/'/g, '&#39;');

    return `<div data-lara-block="table" data-props='${propsJson}'></div>`;
};

export default {
    page,
    email,
};
