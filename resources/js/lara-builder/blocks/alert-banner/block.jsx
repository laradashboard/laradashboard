/**
 * Alert Banner Block - Canvas Preview Component
 *
 * Renders a horizontal announcement bar with optional badge pill,
 * text, and arrow link icon in the LaraBuilder editor canvas.
 */

import { applyLayoutStyles } from "../../components/layout-styles/styleHelpers";
import { __ } from "@lara-builder/i18n";

const AlertBannerBlock = ({ props, isSelected, onUpdate }) => {
    const {
        text = "AI-powered module marketplace is live",
        badgeText = "NEW",
        linkText = "",
        linkUrl = "#",
        backgroundColor = "#1e1b4b",
        textColor = "#e0e7ff",
        badgeColor = "#6366f1",
        badgeTextColor = "#ffffff",
        align = "center",
        padding = "12px 24px",
    } = props;

    const alignMap = {
        left: "flex-start",
        center: "center",
        right: "flex-end",
    };

    const defaultContainerStyle = {
        display: "flex",
        alignItems: "center",
        justifyContent: alignMap[align] || "center",
        gap: "10px",
        backgroundColor,
        color: textColor,
        padding,
        fontSize: "14px",
        fontWeight: "500",
        lineHeight: "1.5",
    };

    const containerStyle = applyLayoutStyles(defaultContainerStyle, props.layoutStyles);

    const badgeStyle = {
        display: "inline-flex",
        alignItems: "center",
        backgroundColor: badgeColor,
        color: badgeTextColor,
        fontSize: "11px",
        fontWeight: "700",
        padding: "2px 8px",
        borderRadius: "9999px",
        letterSpacing: "0.05em",
        textTransform: "uppercase",
        whiteSpace: "nowrap",
        lineHeight: "1.4",
    };

    const handleTextChange = (e) => {
        if (onUpdate) {
            onUpdate({ ...props, text: e.currentTarget.textContent });
        }
    };

    const handleBadgeChange = (e) => {
        if (onUpdate) {
            onUpdate({ ...props, badgeText: e.currentTarget.textContent });
        }
    };

    const hasLink = linkText || linkUrl !== "#";

    return (
        <div
            className={`transition-all ${isSelected ? "ring-2 ring-primary ring-offset-2 rounded" : ""}`}
            style={containerStyle}
        >
            {badgeText && (
                <span style={badgeStyle}>
                    <span
                        contentEditable={isSelected}
                        suppressContentEditableWarning
                        onBlur={handleBadgeChange}
                        style={{ outline: "none" }}
                    >
                        {badgeText}
                    </span>
                </span>
            )}

            <span
                contentEditable={isSelected}
                suppressContentEditableWarning
                onBlur={handleTextChange}
                style={{ outline: "none" }}
            >
                {text}
            </span>

            {hasLink && (
                <span style={{ display: "inline-flex", alignItems: "center", gap: "4px", opacity: 0.8 }}>
                    {linkText && (
                        <span style={{ fontSize: "13px", textDecoration: "underline" }}>
                            {linkText}
                        </span>
                    )}
                    <iconify-icon
                        icon="lucide:arrow-right"
                        width="16"
                        height="16"
                        style={{ color: textColor }}
                        aria-hidden="true"
                    />
                </span>
            )}
        </div>
    );
};

export default AlertBannerBlock;
