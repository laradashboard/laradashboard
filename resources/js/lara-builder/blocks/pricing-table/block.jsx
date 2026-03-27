import { useRef, useState } from "react";
import { applyLayoutStyles } from "../../components/layout-styles/styleHelpers";
import { __ } from "@lara-builder/i18n";

const PricingTableBlock = ({ props, isSelected, onUpdate }) => {
    const {
        planName = "Pro",
        price = "$29",
        period = "/month",
        description = "Perfect for growing businesses",
        features = [],
        buttonText = "Get Started",
        buttonLink = "#",
        buttonColor = "#3b82f6",
        buttonTextColor = "#ffffff",
        highlighted = false,
        badgeText = "",
        backgroundColor = "#ffffff",
        headerColor = "#111827",
        priceColor = "#3b82f6",
        textColor = "#6b7280",
        borderColor = "#e5e7eb",
        borderRadius = "16px",
    } = props;

    const [isEditingName, setIsEditingName] = useState(false);
    const [isEditingDesc, setIsEditingDesc] = useState(false);
    const nameRef = useRef(null);
    const descRef = useRef(null);

    const handleNameChange = (e) => {
        onUpdate({ ...props, planName: e.target.innerText });
    };

    const handleDescChange = (e) => {
        onUpdate({ ...props, description: e.target.innerText });
    };

    const defaultCardStyle = {
        backgroundColor,
        border: highlighted ? `2px solid ${priceColor}` : `1px solid ${borderColor}`,
        borderRadius,
        padding: "32px 24px",
        position: "relative",
        overflow: "hidden",
        maxWidth: "380px",
        margin: "0 auto",
        transform: highlighted ? "scale(1.02)" : "none",
        boxShadow: highlighted
            ? "0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04)"
            : "0 1px 3px rgba(0,0,0,0.1)",
        transition: "all 0.2s",
    };

    const cardStyle = applyLayoutStyles(defaultCardStyle, props.layoutStyles);

    return (
        <div
            className={`transition-all ${isSelected ? "ring-2 ring-primary ring-offset-2 rounded-2xl" : ""}`}
            style={cardStyle}
        >
            {/* Badge */}
            {badgeText && (
                <div
                    style={{
                        position: "absolute",
                        top: "12px",
                        right: "-32px",
                        backgroundColor: priceColor,
                        color: buttonTextColor,
                        fontSize: "11px",
                        fontWeight: "600",
                        padding: "4px 40px",
                        transform: "rotate(45deg)",
                        textTransform: "uppercase",
                        letterSpacing: "0.05em",
                    }}
                >
                    {badgeText}
                </div>
            )}

            {/* Plan Name */}
            <h3
                ref={nameRef}
                contentEditable={isSelected}
                suppressContentEditableWarning
                onClick={() => setIsEditingName(true)}
                onBlur={(e) => {
                    setIsEditingName(false);
                    handleNameChange(e);
                }}
                onKeyDown={(e) => {
                    if (e.key === "Enter") {
                        e.preventDefault();
                        e.target.blur();
                    }
                }}
                style={{
                    color: headerColor,
                    fontSize: "20px",
                    fontWeight: "700",
                    margin: "0 0 8px 0",
                    outline: "none",
                    cursor: isSelected ? "text" : "default",
                }}
                className={isEditingName ? "bg-blue-50/50 rounded px-1" : ""}
            >
                {planName}
            </h3>

            {/* Price */}
            <div style={{ margin: "0 0 8px 0" }}>
                <span
                    style={{
                        color: priceColor,
                        fontSize: "42px",
                        fontWeight: "800",
                        lineHeight: "1",
                    }}
                >
                    {price}
                </span>
                <span
                    style={{
                        color: textColor,
                        fontSize: "16px",
                        fontWeight: "400",
                    }}
                >
                    {period}
                </span>
            </div>

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
                    color: textColor,
                    fontSize: "14px",
                    margin: "0 0 24px 0",
                    lineHeight: "1.5",
                    outline: "none",
                    cursor: isSelected ? "text" : "default",
                }}
                className={isEditingDesc ? "bg-blue-50/50 rounded px-1" : ""}
            >
                {description}
            </p>

            {/* Features List */}
            <ul
                style={{
                    listStyle: "none",
                    padding: 0,
                    margin: "0 0 28px 0",
                }}
            >
                {features.map((feature, index) => (
                    <li
                        key={index}
                        style={{
                            display: "flex",
                            alignItems: "center",
                            gap: "10px",
                            padding: "8px 0",
                            borderBottom:
                                index < features.length - 1
                                    ? `1px solid ${borderColor}`
                                    : "none",
                        }}
                    >
                        <iconify-icon
                            icon={
                                feature.included
                                    ? "mdi:check-circle"
                                    : "mdi:close-circle"
                            }
                            width="20"
                            height="20"
                            style={{
                                color: feature.included ? "#22c55e" : "#d1d5db",
                                flexShrink: 0,
                            }}
                        />
                        <span
                            style={{
                                color: feature.included ? headerColor : "#9ca3af",
                                fontSize: "14px",
                                textDecoration: feature.included
                                    ? "none"
                                    : "line-through",
                            }}
                        >
                            {feature.text}
                        </span>
                    </li>
                ))}
            </ul>

            {/* CTA Button */}
            <div
                style={{
                    backgroundColor: buttonColor,
                    color: buttonTextColor,
                    padding: "12px 24px",
                    borderRadius: "8px",
                    textAlign: "center",
                    fontWeight: "600",
                    fontSize: "15px",
                    cursor: "pointer",
                    transition: "opacity 0.2s",
                }}
            >
                {buttonText}
            </div>
        </div>
    );
};

export default PricingTableBlock;
