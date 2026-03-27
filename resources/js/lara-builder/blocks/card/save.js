/**
 * Card Block - Save/Output Generators
 *
 * Generates HTML output for styled card containers with nested blocks.
 * Note: This block requires the adapter to pass a generateBlockHtml function in options
 * to recursively render child blocks.
 */

import { buildBlockClasses, mergeBlockStyles } from '@lara-builder/utils';

// Shadow mapping
const shadowMap = {
    none: 'none',
    sm: '0 1px 2px rgba(0,0,0,0.05)',
    md: '0 4px 6px -1px rgba(0,0,0,0.1)',
    lg: '0 10px 15px -3px rgba(0,0,0,0.1)',
    xl: '0 20px 25px -5px rgba(0,0,0,0.1)',
};

/**
 * Generate HTML for web/page context
 */
export const page = (props, options = {}) => {
    const { generateBlockHtml } = options;
    const type = 'card';
    const blockClasses = buildBlockClasses(type, props);

    const {
        backgroundColor = '#ffffff',
        borderColor = '#e5e7eb',
        borderWidth = '1px',
        borderRadius = '12px',
        shadow = 'sm',
        hoverShadow = 'md',
        hoverScale = 'none',
        padding = '24px',
        children = [],
    } = props;

    // Generate children HTML - card uses wrapped structure: [[block1, block2, ...]]
    const childBlocks = children[0] || [];
    const childrenHtml = childBlocks.map(block => {
        return generateBlockHtml ? generateBlockHtml(block, options) : '';
    }).join('');

    // Build card styles
    const cardStyles = [
        `background-color: ${backgroundColor}`,
        `border: ${borderWidth} solid ${borderColor}`,
        `border-radius: ${borderRadius}`,
        `box-shadow: ${shadowMap[shadow] || shadowMap.sm}`,
        `padding: ${padding}`,
        'transition: all 0.2s ease',
    ].filter(Boolean).join('; ');

    const mergedStyles = mergeBlockStyles(props, cardStyles);

    // Build hover styles
    const blockId = options.blockId || props._blockId || '';
    const cardClass = blockId ? `lb-card-${blockId}` : '';
    const fullClasses = cardClass ? `${blockClasses} ${cardClass}` : blockClasses;

    let hoverStyleTag = '';
    if (cardClass) {
        const hoverRules = [];
        if (hoverShadow && hoverShadow !== 'none') {
            hoverRules.push(`box-shadow: ${shadowMap[hoverShadow] || shadowMap.md}`);
        }
        if (hoverScale && hoverScale !== 'none') {
            hoverRules.push(`transform: scale(${hoverScale})`);
        }
        if (hoverRules.length > 0) {
            hoverStyleTag = `<style>.${cardClass}:hover { ${hoverRules.join('; ')} }</style>`;
        }
    }

    return `
        ${hoverStyleTag}
        <div class="${fullClasses}" style="${mergedStyles}">
            ${childrenHtml}
        </div>
    `;
};

export default {
    page,
};
