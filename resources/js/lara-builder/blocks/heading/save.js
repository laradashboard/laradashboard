/**
 * Heading Block - Save/Output Generators
 *
 * Generates HTML output for different contexts (page/web and email).
 */

import { buildBlockClasses, mergeBlockStyles } from '@lara-builder/utils';

/**
 * Generate HTML for web/page context
 */
export const page = (props, options = {}) => {
    const type = 'heading';
    const level = props.level || 'h2';
    const blockClasses = buildBlockClasses(type, props);
    const styles = [];

    // Block-specific styles (backward compatibility)
    if (props.align) styles.push(`text-align: ${props.align}`);
    if (props.color && !props.layoutStyles?.typography?.color) styles.push(`color: ${props.color}`);
    if (props.fontSize && !props.layoutStyles?.typography?.fontSize) styles.push(`font-size: ${props.fontSize}`);
    if (props.fontWeight && !props.layoutStyles?.typography?.fontWeight) styles.push(`font-weight: ${props.fontWeight}`);

    // Merge with layout styles
    const mergedStyles = mergeBlockStyles(props, styles.join('; '));

    return `<${level} class="${blockClasses}" style="${mergedStyles}">${props.text || ''}</${level}>`;
};

/**
 * Generate HTML for email context
 */
export const email = (props, options = {}) => {
    const level = props.level || 'h2';
    return `<${level} style="text-align: ${props.align || 'left'}; color: ${props.color || '#333333'}; font-size: ${props.fontSize || '24px'}; font-weight: ${props.fontWeight || '700'}; margin: 0 0 16px 0;">${props.text || ''}</${level}>`;
};

export default {
    page,
    email,
};
