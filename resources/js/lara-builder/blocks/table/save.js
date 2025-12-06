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
 * Generate HTML for email context
 */
export const email = (props, options = {}) => {
    const tableHeaders = (props.headers || []).map(header =>
        `<th style="background-color: ${props.headerBgColor || '#f1f5f9'}; color: ${props.headerTextColor || '#1e293b'}; padding: ${props.cellPadding || '12px'}; text-align: left; font-weight: 600; border-bottom: 2px solid ${props.borderColor || '#e2e8f0'};">${header}</th>`
    ).join('');
    const tableRows = (props.rows || []).map(row =>
        `<tr>${row.map(cell => `<td style="padding: ${props.cellPadding || '12px'}; border-bottom: 1px solid ${props.borderColor || '#e2e8f0'}; color: #374151;">${cell}</td>`).join('')}</tr>`
    ).join('');

    return `
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="font-size: ${props.fontSize || '14px'}; border-collapse: collapse;">
            ${props.showHeader && tableHeaders ? `<thead><tr>${tableHeaders}</tr></thead>` : ''}
            <tbody>${tableRows}</tbody>
        </table>
    `;
};

export default {
    page,
    email,
};
