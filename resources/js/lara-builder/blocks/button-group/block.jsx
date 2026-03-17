/**
 * Button Group Block - Canvas Preview Component
 *
 * Renders multiple buttons in a flex row in the LaraBuilder editor canvas.
 * Each button supports solid, outline, and ghost variants.
 */

import { useEffect, useRef } from "react";
import { applyLayoutStyles } from "../../components/layout-styles/styleHelpers";
import { __ } from "@lara-builder/i18n";

const ButtonGroupBlock = ({ props, isSelected, onUpdate, onRegisterAlign }) => {
    const {
        buttons = [],
        alignment = "center",
        gap = "12px",
        size = "md",
        stackOnMobile = true,
        borderRadius = "8px",
    } = props;

    const onRegisterAlignRef = useRef(onRegisterAlign);
    onRegisterAlignRef.current = onRegisterAlign;

    useEffect(() => {
        if (isSelected && onRegisterAlignRef.current) {
            onRegisterAlignRef.current({
                align: alignment,
                onAlignChange: () => {},
            });
        }
        return () => {
            if (onRegisterAlignRef.current) {
                onRegisterAlignRef.current(null);
            }
        };
    }, [isSelected, alignment]);

    const alignMap = {
        left: "flex-start",
        center: "center",
        right: "flex-end",
    };

    const sizeMap = {
        sm: { fontSize: "13px", padding: "8px 16px" },
        md: { fontSize: "15px", padding: "10px 20px" },
        lg: { fontSize: "17px", padding: "14px 28px" },
    };

    const currentSize = sizeMap[size] || sizeMap.md;

    const getButtonStyle = (btn) => {
        const baseStyle = {
            display: "inline-flex",
            alignItems: "center",
            gap: "6px",
            fontWeight: "600",
            textDecoration: "none",
            cursor: "pointer",
            transition: "all 0.2s",
            borderRadius,
            ...currentSize,
        };

        switch (btn.variant) {
            case "solid":
                return {
                    ...baseStyle,
                    backgroundColor: btn.backgroundColor || "#3b82f6",
                    color: btn.textColor || "#ffffff",
                    border: "2px solid transparent",
                };
            case "outline":
                return {
                    ...baseStyle,
                    backgroundColor: "transparent",
                    color: btn.textColor || btn.backgroundColor || "#3b82f6",
                    border: `2px solid ${btn.backgroundColor || "#3b82f6"}`,
                };
            case "ghost":
                return {
                    ...baseStyle,
                    backgroundColor: "transparent",
                    color: btn.textColor || btn.backgroundColor || "#3b82f6",
                    border: "2px solid transparent",
                };
            default:
                return {
                    ...baseStyle,
                    backgroundColor: btn.backgroundColor || "#3b82f6",
                    color: btn.textColor || "#ffffff",
                    border: "2px solid transparent",
                };
        }
    };

    const handleTextChange = (index, newText) => {
        if (onUpdate) {
            const updatedButtons = [...buttons];
            updatedButtons[index] = { ...updatedButtons[index], text: newText };
            onUpdate({ ...props, buttons: updatedButtons });
        }
    };

    const handleAddButton = () => {
        if (onUpdate) {
            const newButton = {
                text: "Button",
                link: "#",
                variant: "outline",
                backgroundColor: "#3b82f6",
                textColor: "#3b82f6",
                icon: "",
                iconPosition: "left",
            };
            onUpdate({ ...props, buttons: [...buttons, newButton] });
        }
    };

    const defaultContainerStyle = {
        display: "flex",
        flexWrap: "wrap",
        alignItems: "center",
        justifyContent: alignMap[alignment] || "center",
        gap,
        padding: "8px 0",
    };

    const containerStyle = applyLayoutStyles(defaultContainerStyle, props.layoutStyles);

    return (
        <div
            className={`transition-all ${isSelected ? "ring-2 ring-primary ring-offset-2 rounded" : ""}`}
        >
            <div style={containerStyle}>
                {buttons.map((btn, index) => {
                    const btnStyle = getButtonStyle(btn);
                    const iconEl = btn.icon ? (
                        <iconify-icon
                            icon={btn.icon}
                            width={currentSize.fontSize}
                            height={currentSize.fontSize}
                            aria-hidden="true"
                        />
                    ) : null;

                    return (
                        <span key={index} style={btnStyle}>
                            {btn.iconPosition === "left" && iconEl}
                            <span
                                contentEditable={isSelected}
                                suppressContentEditableWarning
                                onBlur={(e) => handleTextChange(index, e.currentTarget.textContent)}
                                style={{ outline: "none" }}
                            >
                                {btn.text}
                            </span>
                            {btn.iconPosition === "right" && iconEl}
                        </span>
                    );
                })}
            </div>

            {isSelected && (
                <div style={{ textAlign: "center", marginTop: "8px" }}>
                    <button
                        onClick={handleAddButton}
                        style={{
                            fontSize: "12px",
                            color: "#3b82f6",
                            background: "none",
                            border: "none",
                            cursor: "pointer",
                            textDecoration: "underline",
                        }}
                    >
                        + {__("Add Button")}
                    </button>
                </div>
            )}
        </div>
    );
};

export default ButtonGroupBlock;
