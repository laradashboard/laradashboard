/**
 * Heading Block - Save/Output Generators
 */

import {
    resolveHeadingEmailColor,
    serializeContentColorForSave,
} from "@lara-builder/tokens/contentTokens";

/**
 * Generate placeholder for server-side rendering (page context)
 */
export const page = (props, options = {}) => {
    const serverProps = {
        text: props.text || "",
        level: props.level || "h2",
        align: props.align || "left",
        color: serializeContentColorForSave(props.color),
        fontSize: props.fontSize || "32px",
        fontWeight: props.fontWeight || "bold",
        lineHeight: props.lineHeight || "1.2",
        letterSpacing: props.letterSpacing || "0",
        layoutStyles: props.layoutStyles || {},
        customCSS: props.customCSS || "",
        customClass: props.customClass || "",
    };

    const blockId = options.blockId || props._blockId || "";
    const propsJson = JSON.stringify(serverProps).replace(/'/g, "&#39;");

    return `<div data-lara-block="heading" data-block-id="${blockId}" data-props='${propsJson}'></div>`;
};

/**
 * Generate placeholder for server-side rendering (email context)
 */
export const email = (props, options = {}) => {
    const serverProps = {
        text: props.text || "",
        level: props.level || "h2",
        align: props.align || "left",
        color: resolveHeadingEmailColor(
            props.color,
            props.layoutStyles?.typography?.color
        ),
        fontSize: props.fontSize || "32px",
        fontWeight: props.fontWeight || "bold",
        lineHeight: props.lineHeight || "1.2",
        letterSpacing: props.letterSpacing || "0",
        layoutStyles: props.layoutStyles || {},
    };

    const blockId = options.blockId || props._blockId || "";
    const propsJson = JSON.stringify(serverProps).replace(/'/g, "&#39;");

    return `<div data-lara-block="heading" data-block-id="${blockId}" data-props='${propsJson}'></div>`;
};

export default {
    page,
    email,
};
