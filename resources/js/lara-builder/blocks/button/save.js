/**
 * Button Block - Save/Output Generators
 *
 * Generates HTML output for different contexts (page/web and email).
 */

import { buildBlockClasses, mergeBlockStyles } from '@lara-builder/utils';

/**
 * Generate HTML for web/page context
 */
export const page = (props, options = {}) => {
    const type = 'button';

    // Button element styles (applied to the inner button/link)
    const buttonElementStyles = [
        'display: inline-block',
        'text-decoration: none',
        'border: none',
        'cursor: pointer',
        'transition: opacity 0.2s ease',
    ];

    // These are now controlled by layoutStyles (Typography, Background, Border)
    // But we keep defaults for backward compatibility if layoutStyles not set
    if (!props.layoutStyles?.background?.color) {
        buttonElementStyles.push(`background-color: ${props.backgroundColor || '#635bff'}`);
    }
    if (!props.layoutStyles?.typography?.color) {
        buttonElementStyles.push(`color: ${props.textColor || '#ffffff'}`);
    }
    if (!props.layoutStyles?.typography?.fontSize) {
        buttonElementStyles.push(`font-size: ${props.fontSize || '16px'}`);
    }
    if (!props.layoutStyles?.typography?.fontWeight) {
        buttonElementStyles.push(`font-weight: ${props.fontWeight || '600'}`);
    }
    if (!props.layoutStyles?.border) {
        buttonElementStyles.push(`border-radius: ${props.borderRadius || '6px'}`);
    }

    // Padding is specific to button
    buttonElementStyles.push(`padding: ${props.padding || '12px 24px'}`);

    const blockClasses = buildBlockClasses(type, props);
    const text = props.text || 'Click Here';
    const align = props.align || 'center';

    // Merge layout styles into the button element styles
    const mergedStyles = mergeBlockStyles(props, buttonElementStyles.join('; '));

    // Build the button/link element
    let buttonElement;

    if (props.link) {
        // Has link - render as <a> tag
        const target = props.target || '_self';

        // Build rel attribute
        const relParts = [];
        if (target === '_blank') {
            relParts.push('noopener', 'noreferrer');
        }
        if (props.nofollow) relParts.push('nofollow');
        if (props.sponsored) relParts.push('sponsored');

        const relAttr = relParts.length > 0 ? ` rel="${relParts.join(' ')}"` : '';
        const targetAttr = target !== '_self' ? ` target="${target}"` : '';

        buttonElement = `<a href="${props.link}"${targetAttr}${relAttr} class="${blockClasses}" style="${mergedStyles}">${text}</a>`;
    } else {
        // No link - render as <span> (styled as button)
        buttonElement = `<span class="${blockClasses}" style="${mergedStyles}">${text}</span>`;
    }

    // Wrapper only for alignment, no layout styles here
    return `
        <div class="lb-button-wrapper" style="text-align: ${align}; padding: 10px 0;">
            ${buttonElement}
        </div>
    `;
};

/**
 * Generate HTML for email context
 */
export const email = (props, options = {}) => {
    return `
        <div style="text-align: ${props.align || 'center'}; padding: 10px 0;">
            <a href="${props.link || '#'}" target="_blank" style="display: inline-block; background-color: ${props.backgroundColor || '#635bff'}; color: ${props.textColor || '#ffffff'}; padding: ${props.padding || '12px 24px'}; border-radius: ${props.borderRadius || '6px'}; text-decoration: none; font-size: ${props.fontSize || '16px'}; font-weight: ${props.fontWeight || '600'};">${props.text || 'Click Here'}</a>
        </div>
    `;
};

export default {
    page,
    email,
};
