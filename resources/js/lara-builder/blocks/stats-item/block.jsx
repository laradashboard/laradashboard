import { useEffect, useRef, useState } from "react";
import { applyLayoutStyles } from "../../components/layout-styles/styleHelpers";

const StatsItemBlock = ({ props, isSelected, onUpdate, onRegisterAlign }) => {
    const {
        value = "100+",
        valueColor = "#3b82f6",
        valueSize = "48px",
        label = "Happy Customers",
        labelColor = "#6b7280",
        labelSize = "14px",
        align = "center",
        prefix = "",
        suffix = "",
    } = props;

    const valueRef = useRef(null);
    const labelRef = useRef(null);
    const [isEditingValue, setIsEditingValue] = useState(false);
    const [isEditingLabel, setIsEditingLabel] = useState(false);

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
        left: "left",
        center: "center",
        right: "right",
    };

    // Container styles
    const defaultContainerStyle = {
        textAlign: alignMap[align] || "center",
        padding: "16px",
    };

    const containerStyle = applyLayoutStyles(defaultContainerStyle, props.layoutStyles);

    const handleValueChange = (e) => {
        onUpdate({ ...props, value: e.target.innerText });
    };

    const handleLabelChange = (e) => {
        onUpdate({ ...props, label: e.target.innerText });
    };

    // Display value with prefix/suffix
    const displayValue = `${prefix}${value}${suffix}`;

    return (
        <div
            className={`transition-all rounded-lg ${isSelected ? "ring-2 ring-primary ring-offset-2" : ""}`}
            style={containerStyle}
        >
            {/* Value */}
            <div
                ref={valueRef}
                contentEditable={isSelected}
                suppressContentEditableWarning
                onClick={() => setIsEditingValue(true)}
                onBlur={(e) => {
                    setIsEditingValue(false);
                    // Extract just the value without prefix/suffix
                    let newValue = e.target.innerText;
                    if (prefix && newValue.startsWith(prefix)) {
                        newValue = newValue.substring(prefix.length);
                    }
                    if (suffix && newValue.endsWith(suffix)) {
                        newValue = newValue.substring(0, newValue.length - suffix.length);
                    }
                    onUpdate({ ...props, value: newValue });
                }}
                onKeyDown={(e) => {
                    if (e.key === "Enter") {
                        e.preventDefault();
                        e.target.blur();
                    }
                }}
                style={{
                    color: valueColor,
                    fontSize: valueSize,
                    fontWeight: "bold",
                    lineHeight: "1.2",
                    marginBottom: "8px",
                    outline: "none",
                    cursor: isSelected ? "text" : "default",
                }}
                className={isEditingValue ? "bg-blue-50/50 rounded px-1 inline-block" : ""}
            >
                {displayValue}
            </div>

            {/* Label */}
            <div
                ref={labelRef}
                contentEditable={isSelected}
                suppressContentEditableWarning
                onClick={() => setIsEditingLabel(true)}
                onBlur={(e) => {
                    setIsEditingLabel(false);
                    handleLabelChange(e);
                }}
                onKeyDown={(e) => {
                    if (e.key === "Enter") {
                        e.preventDefault();
                        e.target.blur();
                    }
                }}
                style={{
                    color: labelColor,
                    fontSize: labelSize,
                    outline: "none",
                    cursor: isSelected ? "text" : "default",
                }}
                className={isEditingLabel ? "bg-blue-50/50 rounded px-1 inline-block" : ""}
            >
                {label}
            </div>
        </div>
    );
};

export default StatsItemBlock;
