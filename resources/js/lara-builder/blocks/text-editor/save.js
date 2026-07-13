/**
 * Text Editor Block - Save/Output Generators
 *
 * Page + email contexts emit server-side placeholders so render.php
 * remains the single source of truth (matches text/quote blocks).
 */

import { resolveEmailTextColor, serializeContentColorForSave } from '@lara-builder/tokens/contentTokens';

/**
 * Generate placeholder for server-side rendering (page context)
 */
export const page = (props) => {
    const serverProps = {
        content: props.content || '',
        align: props.align || 'left',
        color: serializeContentColorForSave(props.color),
        fontSize: props.fontSize || '16px',
        lineHeight: props.lineHeight || '1.6',
        layoutStyles: props.layoutStyles || {},
        customCSS: props.customCSS || '',
        customClass: props.customClass || '',
    };

    const propsJson = JSON.stringify(serverProps).replace(/'/g, '&#39;');

    return `<div data-lara-block="text-editor" data-props='${propsJson}'></div>`;
};

/**
 * Generate placeholder for server-side rendering (email context)
 */
export const email = (props) => {
    const serverProps = {
        content: props.content || '',
        align: props.align || 'left',
        color: resolveEmailTextColor(
            props.color,
            props.layoutStyles?.typography?.color
        ),
        fontSize: props.fontSize || '16px',
        lineHeight: props.lineHeight || '1.6',
        layoutStyles: props.layoutStyles || {},
    };

    const propsJson = JSON.stringify(serverProps).replace(/'/g, '&#39;');

    return `<div data-lara-block="text-editor" data-props='${propsJson}'></div>`;
};

export default {
    page,
    email,
};
