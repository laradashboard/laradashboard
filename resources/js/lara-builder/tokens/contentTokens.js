/**
 * Shared content typography tokens for Lara Builder.
 *
 * Page blocks inherit from `.lb-page-content` CSS variables by default.
 * Email output uses explicit hex values (no CSS inheritance in clients).
 */

export const CONTENT_COLORS = {
    text: "#111827",
    heading: "#111827",
    muted: "#6b7280",
    onDark: "#f9fafb",
    quoteText: "#374151",
    quoteAuthor: "#111827",
};

/** Legacy defaults that should inherit from page/section tokens. */
export const LEGACY_CONTENT_COLORS = new Set([
    "#666666",
    "#333333",
    "#374151",
    "#475569",
    "#64748b",
    "#6b7280",
    "#111827",
    "#1e293b",
    "#0f172a",
    "#94a3b8",
    "#d1d5db",
]);

export const CSS_VARS = {
    text: "--lb-color-text",
    heading: "--lb-color-heading",
    muted: "--lb-color-muted",
    onDark: "--lb-color-on-dark",
};

export function normalizeHexColor(color) {
    if (typeof color !== "string") {
        return "";
    }

    return color.trim().toLowerCase();
}

export function shouldInheritContentColor(color) {
    const normalized = normalizeHexColor(color);

    if (!normalized || normalized === "inherit" || normalized === "currentcolor") {
        return true;
    }

    return LEGACY_CONTENT_COLORS.has(normalized);
}

/**
 * Page/editor color: returns undefined when the block should inherit.
 */
export function resolvePageTextColor(color, typographyColor) {
    if (typographyColor && !shouldInheritContentColor(typographyColor)) {
        return typographyColor;
    }

    if (shouldInheritContentColor(color)) {
        return undefined;
    }

    return color;
}

/**
 * Email color: always returns an explicit hex value.
 */
export function resolveEmailTextColor(color, typographyColor, fallback = CONTENT_COLORS.text) {
    if (typographyColor && !shouldInheritContentColor(typographyColor)) {
        return typographyColor;
    }

    if (shouldInheritContentColor(color)) {
        return fallback;
    }

    return color;
}

export function resolveHeadingPageColor(color, typographyColor) {
    return resolvePageTextColor(color, typographyColor);
}

export function resolveHeadingEmailColor(color, typographyColor) {
    return resolveEmailTextColor(color, typographyColor, CONTENT_COLORS.heading);
}

export function buildSectionTextColorStyles(textColor) {
    if (shouldInheritContentColor(textColor)) {
        return {};
    }

    return {
        color: textColor,
        [CSS_VARS.text]: textColor,
        [CSS_VARS.heading]: textColor,
    };
}

export function serializeContentColorForSave(color) {
    if (shouldInheritContentColor(color)) {
        return "";
    }

    return color;
}

export function getCanvasContentStyleProperties() {
    return {
        [CSS_VARS.text]: CONTENT_COLORS.text,
        [CSS_VARS.heading]: CONTENT_COLORS.heading,
        [CSS_VARS.muted]: CONTENT_COLORS.muted,
        [CSS_VARS.onDark]: CONTENT_COLORS.onDark,
        color: "var(--lb-color-text)",
    };
}
