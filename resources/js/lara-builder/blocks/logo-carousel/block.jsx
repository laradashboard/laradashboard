import { useRef, useState } from "react";
import { applyLayoutStyles } from "../../components/layout-styles/styleHelpers";
import { __ } from "@lara-builder/i18n";

const LogoCarouselBlock = ({ props, isSelected, onUpdate }) => {
    const {
        images = [],
        speed = 30,
        direction = "left",
        pauseOnHover = true,
        gap = "48px",
        imageHeight = "40px",
        grayscale = true,
        headingText = "Trusted by Devs, Startups & Agencies worldwide",
        headingColor = "#6b7280",
        headingSize = "14px",
    } = props;

    const [isEditingHeading, setIsEditingHeading] = useState(false);
    const headingRef = useRef(null);

    const handleHeadingChange = (e) => {
        onUpdate({ ...props, headingText: e.target.innerText });
    };

    const defaultContainerStyle = {
        padding: "24px 16px",
        overflow: "hidden",
    };

    const containerStyle = applyLayoutStyles(defaultContainerStyle, props.layoutStyles);

    const imageStyle = {
        height: imageHeight,
        width: "auto",
        objectFit: "contain",
        flexShrink: 0,
        ...(grayscale
            ? { filter: "grayscale(100%) opacity(0.6)", transition: "filter 0.3s" }
            : {}),
    };

    return (
        <div
            className={`transition-all rounded-lg ${isSelected ? "ring-2 ring-primary ring-offset-2" : ""}`}
            style={containerStyle}
        >
            {/* Heading */}
            {headingText && (
                <p
                    ref={headingRef}
                    contentEditable={isSelected}
                    suppressContentEditableWarning
                    onClick={() => setIsEditingHeading(true)}
                    onBlur={(e) => {
                        setIsEditingHeading(false);
                        handleHeadingChange(e);
                    }}
                    onKeyDown={(e) => {
                        if (e.key === "Enter") {
                            e.preventDefault();
                            e.target.blur();
                        }
                    }}
                    style={{
                        color: headingColor,
                        fontSize: headingSize,
                        textAlign: "center",
                        marginBottom: "20px",
                        marginTop: 0,
                        fontWeight: "500",
                        letterSpacing: "0.025em",
                        textTransform: "uppercase",
                        outline: "none",
                        cursor: isSelected ? "text" : "default",
                    }}
                    className={isEditingHeading ? "bg-blue-50/50 rounded px-1" : ""}
                >
                    {headingText}
                </p>
            )}

            {/* Logos row (static in canvas) */}
            <div
                style={{
                    display: "flex",
                    alignItems: "center",
                    justifyContent: "center",
                    gap: gap,
                    flexWrap: "wrap",
                }}
            >
                {images.map((image, index) => (
                    <img
                        key={index}
                        src={image.src}
                        alt={image.alt || `Logo ${index + 1}`}
                        style={imageStyle}
                        onError={(e) => {
                            e.target.style.display = "none";
                        }}
                    />
                ))}
            </div>

            {/* Direction indicator */}
            {isSelected && (
                <div
                    style={{
                        textAlign: "center",
                        marginTop: "12px",
                        fontSize: "11px",
                        color: "#9ca3af",
                    }}
                >
                    {__("Marquee")}: {direction === "left" ? "\u2190" : "\u2192"} {speed}s
                    {pauseOnHover ? " \u00B7 " + __("Pause on hover") : ""}
                </div>
            )}
        </div>
    );
};

export default LogoCarouselBlock;
