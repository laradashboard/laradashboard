/**
 * Badge Block - Canvas Preview Component
 *
 * Renders a small colored label/pill in the LaraBuilder editor canvas.
 * Supports solid, outline, and soft variants with optional icon.
 */

import { useEffect, useRef } from "react";
import { applyLayoutStyles } from "../../components/layout-styles/styleHelpers";
import { __ } from "@lara-builder/i18n";

const BadgeBlock = ({ props, isSelected, onUpdate, onRegisterAlign }) => {
    const {
        text = "NEW",
        variant = "soft",
        size = "sm",
        color = "#3b82f6",
        textColor = "#1e40af",
        icon = "",
        borderRadius = "pill",
        align = "center",
    } = props;

    const onRegisterAlignRef = useRef(onRegisterAlign);
    onRegisterAlignRef.current = onRegisterAlign;

    useEffect(() => {
        if (isSelected && onRegisterAlignRef.current) {
            onRegisterAlignRef.current({
                align,
                onAlignChange: () => {},
            });
        }
        return () => {
            if (onRegisterAlignRef.current) {
                onRegisterAlignRef.current(null);
            }
        };
    }, [isSelected, align]);

    const alignMap = {
        left: "flex-start",
        center: "center",
        right: "flex-end",
    };

    const sizeMap = {
        sm: { fontSize: "12px", padding: "2px 8px" },
        md: { fontSize: "14px", padding: "4px 12px" },
        lg: { fontSize: "16px", padding: "6px 16px" },
    };

    const radiusMap = {
        rounded: "6px",
        pill: "9999px",
    };

    const getVariantStyles = () => {
        switch (variant) {
            case "solid":
                return {
                    backgroundColor: color,
                    color: "#ffffff",
                    border: "none",
                };
            case "outline":
                return {
                    backgroundColor: "transparent",
                    color: textColor,
                    border: `1.5px solid ${color}`,
                };
            case "soft":
            default:
                return {
                    backgroundColor: color + "26",
                    color: textColor,
                    border: "none",
                };
        }
    };

    const currentSize = sizeMap[size] || sizeMap.sm;
    const variantStyles = getVariantStyles();

    const badgeStyle = {
        display: "inline-flex",
        alignItems: "center",
        gap: "4px",
        fontWeight: "600",
        lineHeight: "1.4",
        borderRadius: radiusMap[borderRadius] || radiusMap.pill,
        whiteSpace: "nowrap",
        letterSpacing: "0.025em",
        ...currentSize,
        ...variantStyles,
    };

    const defaultContainerStyle = {
        display: "flex",
        justifyContent: alignMap[align] || "center",
        padding: "8px 0",
    };

    const containerStyle = applyLayoutStyles(defaultContainerStyle, props.layoutStyles);

    const handleTextChange = (e) => {
        if (onUpdate) {
            onUpdate({ ...props, text: e.currentTarget.textContent });
        }
    };

    return (
        <div
            className={`transition-all ${isSelected ? "ring-2 ring-primary ring-offset-2 rounded" : ""}`}
            style={containerStyle}
        >
            <span style={badgeStyle}>
                {icon && (
                    <iconify-icon
                        icon={icon}
                        width={currentSize.fontSize}
                        height={currentSize.fontSize}
                        style={{ color: variantStyles.color }}
                        aria-hidden="true"
                    />
                )}
                <span
                    contentEditable={isSelected}
                    suppressContentEditableWarning
                    onBlur={handleTextChange}
                    style={{ outline: "none" }}
                >
                    {text}
                </span>
            </span>
        </div>
    );
};

export default BadgeBlock;
