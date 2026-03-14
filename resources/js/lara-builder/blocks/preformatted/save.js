/**
 * Preformatted Block - Save/Output Generators
 *
 * Generates HTML output for different contexts (page/web and email).
 * Content is stored as plain text with newlines.
 */

import { buildBlockClasses, mergeBlockStyles } from '@lara-builder/utils';

/**
 * Sanitize preformatted content:
 * - Convert <div> and <br> to newlines
 * - Strip all HTML tags and inline styles
 * - HTML-escape the result for safe output
 */
const sanitizePreContent = (html) => {
    if (!html) return '';

    let text = html;
    // Convert <br> to newline
    text = text.replace(/<br\s*\/?>/gi, '\n');
    // Convert </div><div> to newline (contentEditable line wrapping)
    text = text.replace(/<\/div>\s*<div[^>]*>/gi, '\n');
    // Remove leading <div> and trailing </div>
    text = text.replace(/^<div[^>]*>/i, '');
    text = text.replace(/<\/div>$/i, '');
    // Remove any remaining HTML tags
    text = text.replace(/<[^>]+>/g, '');
    // Decode HTML entities
    text = text.replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&amp;/g, '&').replace(/&quot;/g, '"').replace(/&#039;/g, "'");
    // Re-escape for safe HTML output
    text = text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');

    return text;
};

/**
 * Generate HTML for web/page context
 */
export const page = (props) => {
    const type = 'preformatted';
    const blockClasses = buildBlockClasses(type, props);
    const content = sanitizePreContent(props.text || '');

    // Only set defaults if not overridden by layoutStyles
    const styles = [
        'overflow-x: auto',
        'white-space: pre-wrap',
        'word-wrap: break-word',
    ];

    // Block-specific defaults (only if not in layoutStyles)
    if (!props.layoutStyles?.typography?.fontFamily) {
        styles.push('font-family: ui-monospace, SFMono-Regular, SF Mono, Menlo, Consolas, Liberation Mono, monospace');
    }
    if (!props.layoutStyles?.typography?.fontSize) {
        styles.push('font-size: 14px');
    }
    if (!props.layoutStyles?.typography?.lineHeight) {
        styles.push('line-height: 1.6');
    }
    if (!props.layoutStyles?.typography?.color) {
        styles.push('color: var(--color-gray-800, #1f2937)');
    }
    if (!props.layoutStyles?.background?.color) {
        styles.push('background-color: var(--color-gray-100, #f3f4f6)');
    }
    if (!props.layoutStyles?.border?.width) {
        styles.push('border: 1px solid var(--color-gray-200, #e5e7eb)');
    }
    if (!props.layoutStyles?.border?.radius) {
        styles.push('border-radius: 4px');
    }
    if (!props.layoutStyles?.spacing?.padding) {
        styles.push('padding: 16px');
    }

    // Merge with layout styles (layoutStyles will override the defaults above)
    const mergedStyles = mergeBlockStyles(props, styles.join('; '));

    return `<pre class="${blockClasses}" style="margin: 1em 0; ${mergedStyles}">${content}</pre>`;
};

/**
 * Generate HTML for email context
 */
export const email = (props) => {
    const content = sanitizePreContent(props.text || '');
    const ls = props.layoutStyles || {};

    // Get values from layoutStyles or use defaults
    const bgColor = ls.background?.color || 'var(--color-gray-100, #f3f4f6)';
    const textColor = ls.typography?.color || 'var(--color-gray-800, #1f2937)';
    const fontSize = ls.typography?.fontSize || '14px';
    const lineHeight = ls.typography?.lineHeight || '1.6';
    const padding = ls.spacing?.padding || '16px';
    const borderRadius = ls.border?.radius || '4px';
    const borderWidth = ls.border?.width || '1px';
    const borderColor = ls.border?.color || 'var(--color-gray-200, #e5e7eb)';

    return `<pre style="margin: 1em 0; background-color: ${bgColor}; border-radius: ${borderRadius}; padding: ${padding}; overflow-x: auto; white-space: pre-wrap; word-wrap: break-word; font-family: monospace; font-size: ${fontSize}; line-height: ${lineHeight}; color: ${textColor}; border: ${borderWidth} solid ${borderColor};">${content}</pre>`;
};

export default {
    page,
    email,
};
