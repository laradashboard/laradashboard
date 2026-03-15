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
 *
 * Uses the DOM to safely extract text, avoiding regex-based tag
 * stripping (which can be bypassed with nested tags) and
 * manual entity decode/re-encode (which can double-unescape).
 */
const sanitizePreContent = (html) => {
    if (!html) return '';

    // Use a temporary DOM element to safely parse and extract text.
    // innerText preserves line breaks from <br> and <div> elements,
    // and automatically strips all HTML tags and inline styles.
    const tmp = document.createElement('div');
    tmp.innerHTML = html;
    const text = tmp.innerText || '';

    // Escape for safe HTML output
    return text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
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
 * Generate placeholder for server-side rendering (email context)
 */
export const email = (props) => {
    const serverProps = {
        text: props.text || '',
        layoutStyles: props.layoutStyles || {},
    };

    const propsJson = JSON.stringify(serverProps).replace(/'/g, '&#39;');

    return `<div data-lara-block="preformatted" data-props='${propsJson}'></div>`;
};

export default {
    page,
    email,
};
