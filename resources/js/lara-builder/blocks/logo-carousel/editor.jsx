import { __ } from "@lara-builder/i18n";

const LogoCarouselEditor = ({ props, onUpdate }) => {
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

    const handleChange = (key, value) => {
        onUpdate({ ...props, [key]: value });
    };

    const handleImageChange = (index, field, value) => {
        const updated = [...images];
        updated[index] = { ...updated[index], [field]: value };
        handleChange("images", updated);
    };

    const addImage = () => {
        const newIndex = images.length + 1;
        handleChange("images", [
            ...images,
            {
                src: `https://placehold.co/150x50/e2e8f0/64748b?text=Logo+${newIndex}`,
                alt: `Logo ${newIndex}`,
                link: "",
            },
        ]);
    };

    const removeImage = (index) => {
        handleChange(
            "images",
            images.filter((_, i) => i !== index)
        );
    };

    const headingSizePresets = [
        { value: "12px", label: "XS" },
        { value: "14px", label: "SM" },
        { value: "16px", label: "MD" },
        { value: "18px", label: "LG" },
    ];

    return (
        <div className="space-y-5">
            {/* Images List */}
            <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                    {__("Logos / Images")}
                </label>
                <div className="space-y-3">
                    {images.map((image, index) => (
                        <div
                            key={index}
                            className="border border-gray-200 rounded-lg p-3 space-y-2"
                        >
                            <div className="flex items-center justify-between">
                                <span className="text-xs font-medium text-gray-500">
                                    {__("Logo")} {index + 1}
                                </span>
                                <button
                                    onClick={() => removeImage(index)}
                                    className="text-red-500 hover:text-red-700 text-xs"
                                    title={__("Remove")}
                                >
                                    <iconify-icon icon="lucide:trash-2" width="14" height="14" />
                                </button>
                            </div>

                            {/* Preview */}
                            {image.src && (
                                <div className="flex justify-center p-2 bg-gray-50 rounded">
                                    <img
                                        src={image.src}
                                        alt={image.alt || ""}
                                        style={{
                                            height: "30px",
                                            width: "auto",
                                            objectFit: "contain",
                                        }}
                                        onError={(e) => {
                                            e.target.style.display = "none";
                                        }}
                                    />
                                </div>
                            )}

                            <input
                                type="text"
                                value={image.src}
                                onChange={(e) =>
                                    handleImageChange(index, "src", e.target.value)
                                }
                                placeholder={__("Image URL")}
                                className="w-full px-3 py-1.5 border border-gray-300 rounded text-xs"
                            />
                            <div className="grid grid-cols-2 gap-2">
                                <input
                                    type="text"
                                    value={image.alt}
                                    onChange={(e) =>
                                        handleImageChange(index, "alt", e.target.value)
                                    }
                                    placeholder={__("Alt text")}
                                    className="w-full px-3 py-1.5 border border-gray-300 rounded text-xs"
                                />
                                <input
                                    type="text"
                                    value={image.link || ""}
                                    onChange={(e) =>
                                        handleImageChange(index, "link", e.target.value)
                                    }
                                    placeholder={__("Link URL")}
                                    className="w-full px-3 py-1.5 border border-gray-300 rounded text-xs"
                                />
                            </div>
                        </div>
                    ))}
                </div>
                <button
                    onClick={addImage}
                    className="mt-2 w-full px-3 py-2 border-2 border-dashed border-gray-300 rounded-lg text-sm text-gray-600 hover:border-primary hover:text-primary transition-all"
                >
                    + {__("Add Logo")}
                </button>
            </div>

            {/* Animation Settings */}
            <div className="border-t border-gray-200 pt-5">
                <h4 className="text-sm font-semibold text-gray-700 mb-4">
                    {__("Animation")}
                </h4>

                {/* Speed Slider */}
                <div className="mb-4">
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                        {__("Speed")} ({speed}s)
                    </label>
                    <input
                        type="range"
                        min="10"
                        max="60"
                        step="1"
                        value={speed}
                        onChange={(e) => handleChange("speed", parseInt(e.target.value))}
                        className="w-full"
                    />
                    <div className="flex justify-between text-xs text-gray-400 mt-1">
                        <span>{__("Fast")}</span>
                        <span>{__("Slow")}</span>
                    </div>
                </div>

                {/* Direction Toggle */}
                <div className="mb-4">
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                        {__("Direction")}
                    </label>
                    <div className="grid grid-cols-2 gap-2">
                        {[
                            { value: "left", label: __("Left") + " \u2190" },
                            { value: "right", label: __("Right") + " \u2192" },
                        ].map((opt) => (
                            <button
                                key={opt.value}
                                onClick={() => handleChange("direction", opt.value)}
                                className={`
                                    px-3 py-2 text-sm font-medium rounded-lg border-2 transition-all
                                    ${direction === opt.value
                                        ? "border-primary bg-primary/10 text-primary"
                                        : "border-gray-200 text-gray-700 hover:border-gray-300"
                                    }
                                `}
                            >
                                {opt.label}
                            </button>
                        ))}
                    </div>
                </div>

                {/* Pause on Hover */}
                <div className="mb-4">
                    <label className="flex items-center gap-3 cursor-pointer">
                        <input
                            type="checkbox"
                            checked={pauseOnHover}
                            onChange={(e) => handleChange("pauseOnHover", e.target.checked)}
                            className="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary"
                        />
                        <span className="text-sm font-medium text-gray-700">
                            {__("Pause on Hover")}
                        </span>
                    </label>
                </div>
            </div>

            {/* Image Settings */}
            <div className="border-t border-gray-200 pt-5">
                <h4 className="text-sm font-semibold text-gray-700 mb-4">
                    {__("Image Style")}
                </h4>

                {/* Gap */}
                <div className="mb-4">
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                        {__("Gap")}
                    </label>
                    <input
                        type="text"
                        value={gap}
                        onChange={(e) => handleChange("gap", e.target.value)}
                        className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                        placeholder="48px"
                    />
                </div>

                {/* Image Height */}
                <div className="mb-4">
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                        {__("Image Height")}
                    </label>
                    <input
                        type="text"
                        value={imageHeight}
                        onChange={(e) => handleChange("imageHeight", e.target.value)}
                        className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                        placeholder="40px"
                    />
                </div>

                {/* Grayscale Toggle */}
                <div>
                    <label className="flex items-center gap-3 cursor-pointer">
                        <input
                            type="checkbox"
                            checked={grayscale}
                            onChange={(e) => handleChange("grayscale", e.target.checked)}
                            className="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary"
                        />
                        <span className="text-sm font-medium text-gray-700">
                            {__("Grayscale Logos")}
                        </span>
                    </label>
                </div>
            </div>

            {/* Heading Settings */}
            <div className="border-t border-gray-200 pt-5">
                <h4 className="text-sm font-semibold text-gray-700 mb-4">
                    {__("Heading")}
                </h4>

                {/* Heading Text */}
                <div className="mb-4">
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                        {__("Heading Text")}
                    </label>
                    <input
                        type="text"
                        value={headingText}
                        onChange={(e) => handleChange("headingText", e.target.value)}
                        className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                        placeholder={__("Leave empty to hide")}
                    />
                </div>

                {/* Heading Color */}
                <div className="mb-4">
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                        {__("Heading Color")}
                    </label>
                    <div className="flex gap-2">
                        <input
                            type="color"
                            value={headingColor}
                            onChange={(e) => handleChange("headingColor", e.target.value)}
                            className="h-10 w-12 border border-gray-300 rounded cursor-pointer"
                        />
                        <input
                            type="text"
                            value={headingColor}
                            onChange={(e) => handleChange("headingColor", e.target.value)}
                            className="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm"
                        />
                    </div>
                </div>

                {/* Heading Size */}
                <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                        {__("Heading Size")}
                    </label>
                    <select
                        value={headingSize}
                        onChange={(e) => handleChange("headingSize", e.target.value)}
                        className="w-full px-2 py-1.5 border border-gray-300 rounded text-sm"
                    >
                        {headingSizePresets.map((preset) => (
                            <option key={preset.value} value={preset.value}>
                                {preset.label} ({preset.value})
                            </option>
                        ))}
                    </select>
                </div>
            </div>
        </div>
    );
};

export default LogoCarouselEditor;
