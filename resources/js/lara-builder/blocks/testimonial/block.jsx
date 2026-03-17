import { useState } from "react";
import { applyLayoutStyles } from "../../components/layout-styles/styleHelpers";
import { __ } from "@lara-builder/i18n";

const StarIcon = ({ filled, color }) => (
    <svg
        width="18"
        height="18"
        viewBox="0 0 24 24"
        fill={filled ? color : "none"}
        stroke={color}
        strokeWidth="2"
        strokeLinecap="round"
        strokeLinejoin="round"
        aria-hidden="true"
    >
        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
    </svg>
);

const TestimonialBlock = ({ props, isSelected, onUpdate }) => {
    const {
        quote = "This product has completely transformed our workflow. Highly recommended!",
        authorName = "John Doe",
        authorRole = "CEO, Company",
        avatarUrl = "",
        rating = 5,
        showRating = true,
        cardStyle = "shadow",
        backgroundColor = "#ffffff",
        textColor = "#374151",
        nameColor = "#111827",
        ratingColor = "#fbbf24",
        borderColor = "#e5e7eb",
    } = props;

    const [isEditingQuote, setIsEditingQuote] = useState(false);

    // Get initials from authorName
    const getInitials = (name) => {
        const parts = name.trim().split(/\s+/);
        if (parts.length >= 2) {
            return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
        }
        return name.charAt(0).toUpperCase();
    };

    // Card style mapping
    const getCardStyles = () => {
        const base = {
            backgroundColor,
            padding: "24px",
            borderRadius: "12px",
            transition: "all 0.2s",
        };

        switch (cardStyle) {
            case "bordered":
                return { ...base, border: `1px solid ${borderColor}` };
            case "shadow":
                return { ...base, boxShadow: "0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -2px rgba(0,0,0,0.1)" };
            case "minimal":
                return base;
            default:
                return { ...base, boxShadow: "0 4px 6px -1px rgba(0,0,0,0.1)" };
        }
    };

    const defaultContainerStyle = getCardStyles();
    const containerStyle = applyLayoutStyles(defaultContainerStyle, props.layoutStyles);

    const handleQuoteChange = (e) => {
        onUpdate({ ...props, quote: e.target.innerText });
    };

    return (
        <div
            className={`transition-all rounded-lg ${isSelected ? "ring-2 ring-primary ring-offset-2" : ""}`}
            style={containerStyle}
        >
            {/* Quote Icon */}
            <div style={{ marginBottom: "16px" }}>
                <svg
                    width="32"
                    height="32"
                    viewBox="0 0 24 24"
                    fill={textColor}
                    opacity="0.2"
                    aria-hidden="true"
                >
                    <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z" />
                </svg>
            </div>

            {/* Star Rating */}
            {showRating && (
                <div style={{ display: "flex", gap: "2px", marginBottom: "12px" }} role="img" aria-label={`${rating} out of 5 stars`}>
                    {[1, 2, 3, 4, 5].map((star) => (
                        <StarIcon key={star} filled={star <= rating} color={ratingColor} />
                    ))}
                </div>
            )}

            {/* Quote Text */}
            <p
                contentEditable={isSelected}
                suppressContentEditableWarning
                onClick={() => setIsEditingQuote(true)}
                onBlur={(e) => {
                    setIsEditingQuote(false);
                    handleQuoteChange(e);
                }}
                style={{
                    color: textColor,
                    fontSize: "16px",
                    lineHeight: "1.6",
                    margin: "0 0 20px 0",
                    fontStyle: "italic",
                    outline: "none",
                    cursor: isSelected ? "text" : "default",
                }}
                className={isEditingQuote ? "bg-blue-50/50 rounded px-1" : ""}
            >
                {quote}
            </p>

            {/* Author Info */}
            <div style={{ display: "flex", alignItems: "center", gap: "12px" }}>
                {/* Avatar */}
                {avatarUrl ? (
                    <img
                        src={avatarUrl}
                        alt={`${authorName} avatar`}
                        style={{
                            width: "48px",
                            height: "48px",
                            borderRadius: "50%",
                            objectFit: "cover",
                        }}
                    />
                ) : (
                    <div
                        style={{
                            width: "48px",
                            height: "48px",
                            borderRadius: "50%",
                            backgroundColor: nameColor,
                            color: backgroundColor,
                            display: "flex",
                            alignItems: "center",
                            justifyContent: "center",
                            fontWeight: "600",
                            fontSize: "16px",
                            flexShrink: 0,
                        }}
                    >
                        {getInitials(authorName)}
                    </div>
                )}

                {/* Name & Role */}
                <div>
                    <div
                        style={{
                            color: nameColor,
                            fontWeight: "600",
                            fontSize: "15px",
                            lineHeight: "1.3",
                        }}
                    >
                        {authorName}
                    </div>
                    <div
                        style={{
                            color: textColor,
                            fontSize: "13px",
                            opacity: 0.7,
                            lineHeight: "1.3",
                        }}
                    >
                        {authorRole}
                    </div>
                </div>
            </div>
        </div>
    );
};

export default TestimonialBlock;
