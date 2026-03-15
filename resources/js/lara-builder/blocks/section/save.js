/**
 * Section Block - Save/Output Generators
 *
 * Generates HTML output for full-width sections with gradient backgrounds.
 * Note: This block requires the adapter to pass a generateBlockHtml function in options
 * to recursively render child blocks.
 */

import { buildBlockClasses, mergeBlockStyles } from '@lara-builder/utils';

// Gradient direction mapping
const gradientDirectionMap = {
    "to-t": "to top",
    "to-tr": "to top right",
    "to-r": "to right",
    "to-br": "to bottom right",
    "to-b": "to bottom",
    "to-bl": "to bottom left",
    "to-l": "to left",
    "to-tl": "to top left",
};

/**
 * Generate HTML for web/page context
 */
export const page = (props, options = {}) => {
    const { generateBlockHtml } = options;
    const type = 'section';
    const blockClasses = buildBlockClasses(type, props);

    const {
        fullWidth = true,
        containerMaxWidth = '1280px',
        contentAlign = 'center',
        backgroundType = 'solid',
        backgroundColor = '#ffffff',
        gradientFrom = '#f9fafb',
        gradientTo = '#f3f4f6',
        gradientDirection = 'to-br',
        children = [],
    } = props;

    // Build background style
    let backgroundStyle = '';
    if (backgroundType === 'gradient') {
        const direction = gradientDirectionMap[gradientDirection] || 'to bottom right';
        backgroundStyle = `background: linear-gradient(${direction}, ${gradientFrom}, ${gradientTo})`;
    } else {
        backgroundStyle = `background-color: ${backgroundColor}`;
    }

    // Container alignment
    const containerMargin = contentAlign === 'center' ? 'margin: 0 auto' :
        contentAlign === 'left' ? 'margin-left: 0' : 'margin-right: 0';

    // Default padding
    const defaultPadding = 'padding: 48px 16px';

    // Generate children HTML - section uses wrapped structure: [[block1, block2, ...]]
    const childBlocks = children[0] || [];
    const childrenHtml = childBlocks.map(block => {
        return generateBlockHtml ? generateBlockHtml(block, options) : '';
    }).join('');

    // Build section styles
    const sectionStyles = [
        backgroundStyle,
        defaultPadding,
    ].filter(Boolean).join('; ');

    const mergedStyles = mergeBlockStyles(props, sectionStyles);

    // Container styles
    const containerStyles = fullWidth
        ? `max-width: ${containerMaxWidth}; ${containerMargin}; width: 100%`
        : 'width: 100%';

    return `
        <section class="${blockClasses}" style="${mergedStyles}">
            <div class="lb-section-container" style="${containerStyles}">
                ${childrenHtml}
            </div>
        </section>
    `;
};

/**
 * Generate placeholder for server-side rendering (email context)
 * Children are serialized into props for recursive server-side rendering
 */
export const email = (props, options = {}) => {
    const serverProps = {
        fullWidth: props.fullWidth ?? true,
        containerMaxWidth: props.containerMaxWidth || '1280px',
        contentAlign: props.contentAlign || 'center',
        backgroundType: props.backgroundType || 'solid',
        backgroundColor: props.backgroundColor || '#ffffff',
        gradientFrom: props.gradientFrom || '#f9fafb',
        gradientTo: props.gradientTo || '#f3f4f6',
        gradientDirection: props.gradientDirection || 'to-br',
        children: props.children || [],
        layoutStyles: props.layoutStyles || {},
    };

    const propsJson = JSON.stringify(serverProps).replace(/'/g, '&#39;');

    return `<div data-lara-block="section" data-props='${propsJson}'></div>`;
};

export default {
    page,
    email,
};
