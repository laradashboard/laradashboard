/**
 * Code Block - Save/Output Generators
 *
 * Generates HTML output for different contexts (page/web and email).
 */

import { buildBlockClasses, mergeBlockStyles } from '@lara-builder/utils';

/**
 * Escape HTML entities
 */
const escapeHtml = (text) => {
    if (!text) return '';
    return text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
};

/**
 * Generate HTML for web/page context
 */
export const page = (props, options = {}) => {
    const type = 'code';
    const blockClasses = buildBlockClasses(type, props);
    const language = props.language || 'plaintext';
    const code = escapeHtml(props.code || '');

    const blockStyles = [
        'background-color: #1e1e1e',
        'border-radius: 8px',
        'padding: 16px',
        'overflow-x: auto',
        'font-family: "Fira Code", "Monaco", "Menlo", "Ubuntu Mono", monospace',
        'font-size: 14px',
        'line-height: 1.5',
        'color: #d4d4d4',
    ];

    const mergedStyles = mergeBlockStyles(props, blockStyles.join('; '));

    return `
        <div class="${blockClasses}" style="${mergedStyles}">
            <pre style="margin: 0; white-space: pre-wrap; word-wrap: break-word;"><code class="language-${language}">${code}</code></pre>
        </div>
    `;
};

/**
 * Generate HTML for email context
 */
export const email = (props, options = {}) => {
    const code = escapeHtml(props.code || '');

    return `
        <div style="background-color: #1e1e1e; border-radius: 8px; padding: 16px; overflow-x: auto; font-family: monospace; font-size: 14px; line-height: 1.5; color: #d4d4d4;">
            <pre style="margin: 0; white-space: pre-wrap; word-wrap: break-word;">${code}</pre>
        </div>
    `;
};

export default {
    page,
    email,
};
