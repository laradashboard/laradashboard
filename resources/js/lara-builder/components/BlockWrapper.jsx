/**
 * BlockWrapper Component
 *
 * Wraps all blocks with consistent class naming and applies custom CSS.
 * This ensures all blocks have the `lb-{type}` class both in the editor and frontend.
 *
 * Classes applied:
 * - lb-block: Base class for all blocks
 * - lb-{type}: Block-specific class (e.g., lb-image, lb-video, lb-heading)
 * - Custom classes from block props (props.customClass)
 */

import { useMemo } from 'react';

/**
 * Get the block class name based on type
 * @param {string} type - Block type (e.g., 'image', 'video', 'heading')
 * @returns {string} - CSS class name (e.g., 'lb-image', 'lb-video', 'lb-heading')
 */
export const getBlockClassName = (type) => {
    if (!type) return 'lb-block';
    return `lb-${type.toLowerCase()}`;
};

/**
 * Build full class string for a block
 * @param {string} type - Block type
 * @param {object} props - Block props (may contain customClass)
 * @param {string} additionalClasses - Additional classes to add
 * @returns {string} - Full class string
 */
export const buildBlockClasses = (type, props = {}, additionalClasses = '') => {
    const classes = ['lb-block', getBlockClassName(type)];

    // Add custom class from props if present
    if (props.customClass) {
        classes.push(props.customClass);
    }

    // Add additional classes if provided
    if (additionalClasses) {
        classes.push(additionalClasses);
    }

    return classes.filter(Boolean).join(' ');
};

/**
 * Parse custom CSS string and return style object
 * Supports basic CSS properties in the format: property: value;
 * @param {string} customCSS - Custom CSS string
 * @returns {object} - React style object
 */
export const parseCustomCSS = (customCSS) => {
    if (!customCSS || typeof customCSS !== 'string') return {};

    const styles = {};
    const rules = customCSS.split(';').filter(rule => rule.trim());

    rules.forEach(rule => {
        const [property, value] = rule.split(':').map(s => s.trim());
        if (property && value) {
            // Convert CSS property to camelCase for React
            const camelCaseProperty = property.replace(/-([a-z])/g, (match, letter) => letter.toUpperCase());
            styles[camelCaseProperty] = value;
        }
    });

    return styles;
};

/**
 * BlockWrapper Component
 * Wraps block content with consistent class naming
 */
const BlockWrapper = ({
    type,
    props = {},
    children,
    className = '',
    style = {},
    isEditor = false,
    ...rest
}) => {
    // Build class string
    const blockClasses = useMemo(() => {
        return buildBlockClasses(type, props, className);
    }, [type, props.customClass, className]);

    // Parse and merge custom CSS
    const mergedStyle = useMemo(() => {
        const customStyles = parseCustomCSS(props.customCSS);
        return { ...style, ...customStyles };
    }, [style, props.customCSS]);

    // In editor mode, we might want to add additional visual indicators
    const editorClasses = isEditor ? 'lb-block-editor' : '';

    return (
        <div
            className={`${blockClasses} ${editorClasses}`.trim()}
            style={mergedStyle}
            data-block-type={type}
            {...rest}
        >
            {children}
        </div>
    );
};

export default BlockWrapper;
