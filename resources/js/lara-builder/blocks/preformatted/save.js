/**
 * Preformatted Block - Save/Output Generators
 *
 * Generates HTML output for different contexts (page/web and email).
 * Preserves inline formatting (bold, italic, etc.) from the editor.
 */

import { buildBlockClasses, mergeBlockStyles } from '@lara-builder/utils';

/**
 * Generate HTML for web/page context
 */
export const page = (props) => {
    const type = 'preformatted';
    const blockClasses = buildBlockClasses(type, props);
    const content = props.text || '';

    // Only set defaults if not overridden by layoutStyles
    const styles = [
        'overflow-x: auto',
        'white-space: pre-wrap',
        'word-wrap: break-word',
    ];

    // Block-specific defaults (only if not in layoutStyles)
    if (!props.layoutStyles?.typography?.fontFamily) {
        styles.push('font-family: ui-monospace, SFMono-Regular, "SF Mono", Menlo, Consolas, "Liberation Mono", monospace');
    }
    if (!props.layoutStyles?.typography?.fontSize) {
        styles.push('font-size: 14px');
    }
    if (!props.layoutStyles?.typography?.lineHeight) {
        styles.push('line-height: 1.6');
    }
    if (!props.layoutStyles?.typography?.color) {
        styles.push('color: #333333');
    }
    if (!props.layoutStyles?.background?.color) {
        styles.push('background-color: #f5f5f5');
    }
    if (!props.layoutStyles?.border?.width) {
        styles.push('border: 1px solid #e0e0e0');
    }
    if (!props.layoutStyles?.border?.radius) {
        styles.push('border-radius: 4px');
    }
    if (!props.layoutStyles?.spacing?.padding) {
        styles.push('padding: 16px');
    }

    // Merge with layout styles (layoutStyles will override the defaults above)
    const mergedStyles = mergeBlockStyles(props, styles.join('; '));

    return `<pre class="${blockClasses}" style="margin: 0; ${mergedStyles}">${content}</pre>`;
};

/**
 * Generate HTML for email context
 */
export const email = (props) => {
    const content = props.text || '';
    const ls = props.layoutStyles || {};

    // Get values from layoutStyles or use defaults
    const bgColor = ls.background?.color || '#f5f5f5';
    const textColor = ls.typography?.color || '#333333';
    const fontSize = ls.typography?.fontSize || '14px';
    const lineHeight = ls.typography?.lineHeight || '1.6';
    const padding = ls.spacing?.padding || '16px';
    const borderRadius = ls.border?.radius || '4px';
    const borderWidth = ls.border?.width || '1px';
    const borderColor = ls.border?.color || '#e0e0e0';

    return `<pre style="margin: 0; background-color: ${bgColor}; border-radius: ${borderRadius}; padding: ${padding}; overflow-x: auto; white-space: pre-wrap; word-wrap: break-word; font-family: monospace; font-size: ${fontSize}; line-height: ${lineHeight}; color: ${textColor}; border: ${borderWidth} solid ${borderColor};">${content}</pre>`;
};

export default {
    page,
    email,
};
