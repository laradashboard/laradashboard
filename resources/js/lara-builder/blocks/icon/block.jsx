import { useEffect, useRef } from "react";
import { applyLayoutStyles } from "../../components/layout-styles/styleHelpers";

const IconBlock = ({ props, isSelected, onRegisterAlign }) => {
    const {
        icon = "lucide:star",
        size = "48px",
        color = "#3b82f6",
        align = "center",
        backgroundColor = "",
        backgroundShape = "none",
        backgroundPadding = "16px",
    } = props;

    // Use ref to avoid dependency issues
    const onRegisterAlignRef = useRef(onRegisterAlign);
    onRegisterAlignRef.current = onRegisterAlign;

    // Register alignment capability only when selected
    useEffect(() => {
        if (isSelected && onRegisterAlignRef.current) {
            onRegisterAlignRef.current({
                align,
                onAlignChange: () => {
                    // This will be handled through the props update mechanism
                },
            });
        }
        return () => {
            if (onRegisterAlignRef.current) {
                onRegisterAlignRef.current(null);
            }
        };
    }, [isSelected, align]);

    // Alignment mapping
    const alignMap = {
        left: "flex-start",
        center: "center",
        right: "flex-end",
    };

    // Background shape styles
    const getBackgroundStyles = () => {
        if (!backgroundColor || backgroundShape === "none") {
            return {};
        }

        const baseStyles = {
            backgroundColor,
            padding: backgroundPadding,
            display: "inline-flex",
            alignItems: "center",
            justifyContent: "center",
        };

        switch (backgroundShape) {
            case "circle":
                return { ...baseStyles, borderRadius: "50%" };
            case "rounded":
                return { ...baseStyles, borderRadius: "12px" };
            case "square":
                return { ...baseStyles, borderRadius: "0" };
            default:
                return baseStyles;
        }
    };

    // Container styles
    const defaultContainerStyle = {
        display: "flex",
        justifyContent: alignMap[align] || "center",
        padding: "8px 0",
    };

    const containerStyle = applyLayoutStyles(defaultContainerStyle, props.layoutStyles);

    const backgroundStyles = getBackgroundStyles();
    const hasBackground = backgroundColor && backgroundShape !== "none";

    return (
        <div
            className={`transition-all ${isSelected ? "ring-2 ring-primary ring-offset-2 rounded" : ""}`}
            style={containerStyle}
        >
            {hasBackground ? (
                <div style={backgroundStyles}>
                    <iconify-icon
                        icon={icon}
                        width={size}
                        height={size}
                        style={{ color }}
                    />
                </div>
            ) : (
                <iconify-icon
                    icon={icon}
                    width={size}
                    height={size}
                    style={{ color }}
                />
            )}
        </div>
    );
};

export default IconBlock;
