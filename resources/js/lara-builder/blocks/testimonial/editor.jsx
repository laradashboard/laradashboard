import { __ } from "@lara-builder/i18n";

const TestimonialEditor = ({ props, onUpdate }) => {
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

    const handleChange = (key, value) => {
        onUpdate({ ...props, [key]: value });
    };

    const cardStyles = [
        { value: "bordered", label: __("Bordered") },
        { value: "shadow", label: __("Shadow") },
        { value: "minimal", label: __("Minimal") },
    ];

    return (
        <div className="space-y-5">
            {/* Quote */}
            <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                    {__("Quote")}
                </label>
                <textarea
                    value={quote}
                    onChange={(e) => handleChange("quote", e.target.value)}
                    rows={4}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                    placeholder={__("Enter testimonial quote...")}
                />
            </div>

            {/* Author Name */}
            <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                    {__("Author Name")}
                </label>
                <input
                    type="text"
                    value={authorName}
                    onChange={(e) => handleChange("authorName", e.target.value)}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                    placeholder={__("John Doe")}
                />
            </div>

            {/* Author Role */}
            <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                    {__("Author Role")}
                </label>
                <input
                    type="text"
                    value={authorRole}
                    onChange={(e) => handleChange("authorRole", e.target.value)}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                    placeholder={__("CEO, Company")}
                />
            </div>

            {/* Avatar URL */}
            <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                    {__("Avatar URL")}
                </label>
                <input
                    type="text"
                    value={avatarUrl}
                    onChange={(e) => handleChange("avatarUrl", e.target.value)}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                    placeholder={__("https://example.com/avatar.jpg")}
                />
                {avatarUrl && (
                    <div className="mt-2 flex items-center gap-2">
                        <img
                            src={avatarUrl}
                            alt={__("Avatar preview")}
                            className="w-10 h-10 rounded-full object-cover border border-gray-200"
                            onError={(e) => { e.target.style.display = "none"; }}
                        />
                        <span className="text-xs text-gray-500">{__("Preview")}</span>
                    </div>
                )}
            </div>

            {/* Rating */}
            <div className="border-t border-gray-200 pt-5">
                <h4 className="text-sm font-semibold text-gray-700 mb-4">
                    {__("Rating")}
                </h4>

                {/* Show Rating Toggle */}
                <div className="flex items-center justify-between mb-4">
                    <label className="block text-sm font-medium text-gray-700">
                        {__("Show Rating")}
                    </label>
                    <button
                        onClick={() => handleChange("showRating", !showRating)}
                        className={`
                            relative inline-flex h-6 w-11 items-center rounded-full transition-colors
                            ${showRating ? "bg-primary" : "bg-gray-300"}
                        `}
                    >
                        <span
                            className={`
                                inline-block h-4 w-4 transform rounded-full bg-white transition-transform
                                ${showRating ? "translate-x-6" : "translate-x-1"}
                            `}
                        />
                    </button>
                </div>

                {/* Rating Slider */}
                {showRating && (
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-2">
                            {__("Stars")}: {rating}
                        </label>
                        <input
                            type="range"
                            min="1"
                            max="5"
                            step="1"
                            value={rating}
                            onChange={(e) => handleChange("rating", parseInt(e.target.value, 10))}
                            className="w-full accent-primary"
                        />
                        <div className="flex justify-between text-xs text-gray-400 mt-1">
                            <span>1</span>
                            <span>2</span>
                            <span>3</span>
                            <span>4</span>
                            <span>5</span>
                        </div>
                    </div>
                )}
            </div>

            {/* Card Style */}
            <div className="border-t border-gray-200 pt-5">
                <h4 className="text-sm font-semibold text-gray-700 mb-4">
                    {__("Card Style")}
                </h4>

                <div className="grid grid-cols-3 gap-2 mb-4">
                    {cardStyles.map((style) => (
                        <button
                            key={style.value}
                            onClick={() => handleChange("cardStyle", style.value)}
                            className={`
                                px-3 py-2 text-sm font-medium rounded-lg border-2 transition-all
                                ${cardStyle === style.value
                                    ? "border-primary bg-primary/10 text-primary"
                                    : "border-gray-200 bg-white text-gray-700 hover:border-gray-300"
                                }
                            `}
                        >
                            {style.label}
                        </button>
                    ))}
                </div>
            </div>

            {/* Colors */}
            <div className="border-t border-gray-200 pt-5">
                <h4 className="text-sm font-semibold text-gray-700 mb-4">
                    {__("Colors")}
                </h4>

                {/* Background Color */}
                <div className="mb-4">
                    <label className="block text-xs font-medium text-gray-600 mb-1">
                        {__("Background")}
                    </label>
                    <div className="flex gap-2">
                        <input
                            type="color"
                            value={backgroundColor}
                            onChange={(e) => handleChange("backgroundColor", e.target.value)}
                            className="h-8 w-10 border border-gray-300 rounded cursor-pointer"
                        />
                        <input
                            type="text"
                            value={backgroundColor}
                            onChange={(e) => handleChange("backgroundColor", e.target.value)}
                            className="flex-1 px-2 py-1 border border-gray-300 rounded text-xs"
                        />
                    </div>
                </div>

                {/* Text Color */}
                <div className="mb-4">
                    <label className="block text-xs font-medium text-gray-600 mb-1">
                        {__("Text Color")}
                    </label>
                    <div className="flex gap-2">
                        <input
                            type="color"
                            value={textColor}
                            onChange={(e) => handleChange("textColor", e.target.value)}
                            className="h-8 w-10 border border-gray-300 rounded cursor-pointer"
                        />
                        <input
                            type="text"
                            value={textColor}
                            onChange={(e) => handleChange("textColor", e.target.value)}
                            className="flex-1 px-2 py-1 border border-gray-300 rounded text-xs"
                        />
                    </div>
                </div>

                {/* Name Color */}
                <div className="mb-4">
                    <label className="block text-xs font-medium text-gray-600 mb-1">
                        {__("Name Color")}
                    </label>
                    <div className="flex gap-2">
                        <input
                            type="color"
                            value={nameColor}
                            onChange={(e) => handleChange("nameColor", e.target.value)}
                            className="h-8 w-10 border border-gray-300 rounded cursor-pointer"
                        />
                        <input
                            type="text"
                            value={nameColor}
                            onChange={(e) => handleChange("nameColor", e.target.value)}
                            className="flex-1 px-2 py-1 border border-gray-300 rounded text-xs"
                        />
                    </div>
                </div>

                {/* Rating Color */}
                {showRating && (
                    <div className="mb-4">
                        <label className="block text-xs font-medium text-gray-600 mb-1">
                            {__("Rating Color")}
                        </label>
                        <div className="flex gap-2">
                            <input
                                type="color"
                                value={ratingColor}
                                onChange={(e) => handleChange("ratingColor", e.target.value)}
                                className="h-8 w-10 border border-gray-300 rounded cursor-pointer"
                            />
                            <input
                                type="text"
                                value={ratingColor}
                                onChange={(e) => handleChange("ratingColor", e.target.value)}
                                className="flex-1 px-2 py-1 border border-gray-300 rounded text-xs"
                            />
                        </div>
                    </div>
                )}

                {/* Border Color (for bordered style) */}
                {cardStyle === "bordered" && (
                    <div>
                        <label className="block text-xs font-medium text-gray-600 mb-1">
                            {__("Border Color")}
                        </label>
                        <div className="flex gap-2">
                            <input
                                type="color"
                                value={borderColor}
                                onChange={(e) => handleChange("borderColor", e.target.value)}
                                className="h-8 w-10 border border-gray-300 rounded cursor-pointer"
                            />
                            <input
                                type="text"
                                value={borderColor}
                                onChange={(e) => handleChange("borderColor", e.target.value)}
                                className="flex-1 px-2 py-1 border border-gray-300 rounded text-xs"
                            />
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
};

export default TestimonialEditor;
