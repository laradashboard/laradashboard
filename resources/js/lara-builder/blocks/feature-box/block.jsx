import { useEffect, useRef, useState } from "react";
import { applyLayoutStyles } from "../../components/layout-styles/styleHelpers";
import { __ } from "@lara-builder/i18n";

const FeatureBoxBlock = ({ props, isSelected, onUpdate, onRegisterAlign }) => {
    const {
        icon = "lucide:star",
        iconSize = "32px",
        iconColor = "#3b82f6",
        iconBackgroundColor = "#dbeafe",
        iconBackgroundShape = "circle",
        title = "Feature Title",
        titleColor = "#111827",
        titleSize = "18px",
        description = "A brief description of this feature or benefit.",
        descriptionColor = "#6b7280",
        descriptionSize = "14px",
        align = "center",
        gap = "16px",
    } = props;

    const titleRef = useRef(null);
    const descRef = useRef(null);
    const [isEditingTitle, setIsEditingTitle] = useState(false);
    const [isEditingDesc, setIsEditingDesc] = useState(false);

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

    const textAlignMap = {
        left: "left",
        center: "center",
        right: "right",
    };

    // Icon background shape styles
    const getIconBackgroundStyle = () => {
        if (!iconBackgroundColor || iconBackgroundShape === "none") {
            return {};
        }

        const baseStyles = {
            backgroundColor: iconBackgroundColor,
            padding: "16px",
            display: "inline-flex",
            alignItems: "center",
            justifyContent: "center",
            marginBottom: gap,
        };

        switch (iconBackgroundShape) {
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
        flexDirection: "column",
        alignItems: alignMap[align] || "center",
        textAlign: textAlignMap[align] || "center",
        padding: "16px",
    };

    const containerStyle = applyLayoutStyles(defaultContainerStyle, props.layoutStyles);

    const handleTitleChange = (e) => {
        onUpdate({ ...props, title: e.target.innerText });
    };

    const handleDescChange = (e) => {
        onUpdate({ ...props, description: e.target.innerText });
    };

    const hasIconBackground = iconBackgroundColor && iconBackgroundShape !== "none";

    return (
        <div
            className={`transition-all rounded-lg ${isSelected ? "ring-2 ring-primary ring-offset-2" : ""}`}
            style={containerStyle}
        >
            {/* Icon */}
            {hasIconBackground ? (
                <div style={getIconBackgroundStyle()}>
                    <iconify-icon
                        icon={icon}
                        width={iconSize}
                        height={iconSize}
                        style={{ color: iconColor }}
                    />
                </div>
            ) : (
                <div style={{ marginBottom: gap }}>
                    <iconify-icon
                        icon={icon}
                        width={iconSize}
                        height={iconSize}
                        style={{ color: iconColor }}
                    />
                </div>
            )}

            {/* Title */}
            <h3
                ref={titleRef}
                contentEditable={isSelected}
                suppressContentEditableWarning
                onClick={() => setIsEditingTitle(true)}
                onBlur={(e) => {
                    setIsEditingTitle(false);
                    handleTitleChange(e);
                }}
                onKeyDown={(e) => {
                    if (e.key === "Enter") {
                        e.preventDefault();
                        e.target.blur();
                    }
                }}
                style={{
                    color: titleColor,
                    fontSize: titleSize,
                    fontWeight: "600",
                    margin: 0,
                    marginBottom: "8px",
                    outline: "none",
                    cursor: isSelected ? "text" : "default",
                }}
                className={isEditingTitle ? "bg-blue-50/50 rounded px-1" : ""}
            >
                {title}
            </h3>

            {/* Description */}
            <p
                ref={descRef}
                contentEditable={isSelected}
                suppressContentEditableWarning
                onClick={() => setIsEditingDesc(true)}
                onBlur={(e) => {
                    setIsEditingDesc(false);
                    handleDescChange(e);
                }}
                style={{
                    color: descriptionColor,
                    fontSize: descriptionSize,
                    margin: 0,
                    lineHeight: "1.5",
                    outline: "none",
                    cursor: isSelected ? "text" : "default",
                }}
                className={isEditingDesc ? "bg-blue-50/50 rounded px-1" : ""}
            >
                {description}
            </p>
        </div>
    );
};

export default FeatureBoxBlock;
