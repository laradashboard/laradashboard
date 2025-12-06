/**
 * Block Save Utilities
 *
 * Shared helper functions for block save.js files
 * to generate consistent HTML output.
 */

import { layoutStylesToInlineCSS } from '../components/LayoutStylesSection';

/**
 * Generate block class name from type
 * @param {string} type - Block type (e.g., 'heading', 'text')
 * @returns {string} - Class name (e.g., 'lb-heading', 'lb-text')
 */
export const getBlockClass = (type) => `lb-${type.toLowerCase()}`;

/**
 * Build complete class string for a block
 * @param {string} type - Block type
 * @param {Object} props - Block props (may include customClass)
 * @returns {string} - Space-separated class string
 */
export const buildBlockClasses = (type, props = {}) => {
    const classes = ['lb-block', getBlockClass(type)];
    if (props.customClass) {
        classes.push(props.customClass);
    }
    return classes.join(' ');
};

/**
 * Merge layout styles, block-specific styles, and custom CSS into a single style string
 * @param {Object} props - Block props (includes layoutStyles and customCSS)
 * @param {string} blockStyles - Block-specific inline styles
 * @returns {string} - Combined style string
 */
export const mergeBlockStyles = (props, blockStyles = '') => {
    const layoutCSS = layoutStylesToInlineCSS(props?.layoutStyles);
    const customCSS = props?.customCSS || '';
    const allStyles = [layoutCSS, blockStyles, customCSS].filter(Boolean).join('; ');
    return allStyles;
};

/**
 * Re-export layoutStylesToInlineCSS for convenience
 */
export { layoutStylesToInlineCSS };
