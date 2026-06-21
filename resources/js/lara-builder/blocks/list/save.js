/**
 * List Block - Save/Output Generators
 */

import {
    resolveEmailTextColor,
    serializeContentColorForSave,
} from "@lara-builder/tokens/contentTokens";

export const page = (props, options = {}) => {
    const serverProps = {
        items: props.items || [],
        listType: props.listType || "bullet",
        color: serializeContentColorForSave(props.color),
        fontSize: props.fontSize || "16px",
        iconColor: props.iconColor || "#635bff",
        layoutStyles: props.layoutStyles || {},
        customCSS: props.customCSS || "",
        customClass: props.customClass || "",
    };

    const propsJson = JSON.stringify(serverProps).replace(/'/g, "&#39;");

    return `<div data-lara-block="list" data-props='${propsJson}'></div>`;
};

export const email = (props, options = {}) => {
    const serverProps = {
        items: props.items || [],
        listType: props.listType || "bullet",
        color: resolveEmailTextColor(
            props.color,
            props.layoutStyles?.typography?.color
        ),
        fontSize: props.fontSize || "16px",
        iconColor: props.iconColor || "#635bff",
        layoutStyles: props.layoutStyles || {},
    };

    const propsJson = JSON.stringify(serverProps).replace(/'/g, "&#39;");

    return `<div data-lara-block="list" data-props='${propsJson}'></div>`;
};

export default {
    page,
    email,
};
