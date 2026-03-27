import { __ } from "@lara-builder/i18n";

const PricingTableEditor = ({ props, onUpdate }) => {
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

    const handleChange = (key, value) => {
        onUpdate({ ...props, [key]: value });
    };

    const handleFeatureChange = (index, field, value) => {
        const updated = [...features];
        updated[index] = { ...updated[index], [field]: value };
        handleChange("features", updated);
    };

    const addFeature = () => {
        handleChange("features", [
            ...features,
            { text: "New feature", included: true },
        ]);
    };

    const removeFeature = (index) => {
        handleChange(
            "features",
            features.filter((_, i) => i !== index)
        );
    };

    const borderRadiusPresets = [
        { value: "0px", label: __("None") },
        { value: "8px", label: "8px" },
        { value: "12px", label: "12px" },
        { value: "16px", label: "16px" },
        { value: "24px", label: "24px" },
    ];

    return (
        <div className="space-y-5">
            {/* Plan Details */}
            <div>
                <h4 className="text-sm font-semibold text-gray-700 mb-4">
                    {__("Plan Details")}
                </h4>

                {/* Plan Name */}
                <div className="mb-4">
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                        {__("Plan Name")}
                    </label>
                    <input
                        type="text"
                        value={planName}
                        onChange={(e) => handleChange("planName", e.target.value)}
                        className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                    />
                </div>

                {/* Price & Period */}
                <div className="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-2">
                            {__("Price")}
                        </label>
                        <input
                            type="text"
                            value={price}
                            onChange={(e) => handleChange("price", e.target.value)}
                            className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                            placeholder="$29"
                        />
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-2">
                            {__("Period")}
                        </label>
                        <input
                            type="text"
                            value={period}
                            onChange={(e) => handleChange("period", e.target.value)}
                            className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                            placeholder="/month"
                        />
                    </div>
                </div>

                {/* Description */}
                <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                        {__("Description")}
                    </label>
                    <textarea
                        value={description}
                        onChange={(e) => handleChange("description", e.target.value)}
                        rows={2}
                        className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                    />
                </div>
            </div>

            {/* Features List */}
            <div className="border-t border-gray-200 pt-5">
                <h4 className="text-sm font-semibold text-gray-700 mb-4">
                    {__("Features")}
                </h4>
                <div className="space-y-2">
                    {features.map((feature, index) => (
                        <div
                            key={index}
                            className="flex items-center gap-2 p-2 border border-gray-200 rounded-lg"
                        >
                            {/* Included Toggle */}
                            <button
                                onClick={() =>
                                    handleFeatureChange(
                                        index,
                                        "included",
                                        !feature.included
                                    )
                                }
                                className={`
                                    w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 transition-all
                                    ${feature.included
                                        ? "bg-green-100 text-green-600"
                                        : "bg-gray-100 text-gray-400"
                                    }
                                `}
                                title={
                                    feature.included
                                        ? __("Click to mark as excluded")
                                        : __("Click to mark as included")
                                }
                            >
                                <iconify-icon
                                    icon={
                                        feature.included
                                            ? "mdi:check"
                                            : "mdi:close"
                                    }
                                    width="14"
                                    height="14"
                                />
                            </button>

                            {/* Feature Text */}
                            <input
                                type="text"
                                value={feature.text}
                                onChange={(e) =>
                                    handleFeatureChange(
                                        index,
                                        "text",
                                        e.target.value
                                    )
                                }
                                className="flex-1 px-2 py-1 border border-gray-300 rounded text-xs"
                            />

                            {/* Remove */}
                            <button
                                onClick={() => removeFeature(index)}
                                className="text-red-500 hover:text-red-700 flex-shrink-0"
                                title={__("Remove")}
                            >
                                <iconify-icon
                                    icon="lucide:trash-2"
                                    width="14"
                                    height="14"
                                />
                            </button>
                        </div>
                    ))}
                </div>
                <button
                    onClick={addFeature}
                    className="mt-2 w-full px-3 py-2 border-2 border-dashed border-gray-300 rounded-lg text-sm text-gray-600 hover:border-primary hover:text-primary transition-all"
                >
                    + {__("Add Feature")}
                </button>
            </div>

            {/* Button Settings */}
            <div className="border-t border-gray-200 pt-5">
                <h4 className="text-sm font-semibold text-gray-700 mb-4">
                    {__("Button")}
                </h4>

                {/* Button Text */}
                <div className="mb-4">
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                        {__("Button Text")}
                    </label>
                    <input
                        type="text"
                        value={buttonText}
                        onChange={(e) => handleChange("buttonText", e.target.value)}
                        className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                    />
                </div>

                {/* Button Link */}
                <div className="mb-4">
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                        {__("Button Link")}
                    </label>
                    <input
                        type="text"
                        value={buttonLink}
                        onChange={(e) => handleChange("buttonLink", e.target.value)}
                        className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                        placeholder="https://"
                    />
                </div>

                {/* Button Colors */}
                <div className="grid grid-cols-2 gap-4">
                    <div>
                        <label className="block text-xs font-medium text-gray-600 mb-1">
                            {__("Button Color")}
                        </label>
                        <div className="flex gap-1">
                            <input
                                type="color"
                                value={buttonColor}
                                onChange={(e) =>
                                    handleChange("buttonColor", e.target.value)
                                }
                                className="h-8 w-10 border border-gray-300 rounded cursor-pointer"
                            />
                            <input
                                type="text"
                                value={buttonColor}
                                onChange={(e) =>
                                    handleChange("buttonColor", e.target.value)
                                }
                                className="flex-1 px-2 py-1 border border-gray-300 rounded text-xs"
                            />
                        </div>
                    </div>
                    <div>
                        <label className="block text-xs font-medium text-gray-600 mb-1">
                            {__("Text Color")}
                        </label>
                        <div className="flex gap-1">
                            <input
                                type="color"
                                value={buttonTextColor}
                                onChange={(e) =>
                                    handleChange("buttonTextColor", e.target.value)
                                }
                                className="h-8 w-10 border border-gray-300 rounded cursor-pointer"
                            />
                            <input
                                type="text"
                                value={buttonTextColor}
                                onChange={(e) =>
                                    handleChange("buttonTextColor", e.target.value)
                                }
                                className="flex-1 px-2 py-1 border border-gray-300 rounded text-xs"
                            />
                        </div>
                    </div>
                </div>
            </div>

            {/* Highlight & Badge */}
            <div className="border-t border-gray-200 pt-5">
                <h4 className="text-sm font-semibold text-gray-700 mb-4">
                    {__("Highlight & Badge")}
                </h4>

                {/* Highlighted Toggle */}
                <div className="mb-4">
                    <label className="flex items-center gap-3 cursor-pointer">
                        <input
                            type="checkbox"
                            checked={highlighted}
                            onChange={(e) =>
                                handleChange("highlighted", e.target.checked)
                            }
                            className="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary"
                        />
                        <span className="text-sm font-medium text-gray-700">
                            {__("Highlighted Card")}
                        </span>
                    </label>
                </div>

                {/* Badge Text */}
                <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                        {__("Badge Text")}
                    </label>
                    <input
                        type="text"
                        value={badgeText}
                        onChange={(e) => handleChange("badgeText", e.target.value)}
                        className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                        placeholder={__("e.g. Most Popular")}
                    />
                </div>
            </div>

            {/* Card Colors */}
            <div className="border-t border-gray-200 pt-5">
                <h4 className="text-sm font-semibold text-gray-700 mb-4">
                    {__("Card Colors")}
                </h4>

                <div className="space-y-3">
                    {/* Background Color */}
                    <div>
                        <label className="block text-xs font-medium text-gray-600 mb-1">
                            {__("Background")}
                        </label>
                        <div className="flex gap-2">
                            <input
                                type="color"
                                value={backgroundColor}
                                onChange={(e) =>
                                    handleChange("backgroundColor", e.target.value)
                                }
                                className="h-8 w-10 border border-gray-300 rounded cursor-pointer"
                            />
                            <input
                                type="text"
                                value={backgroundColor}
                                onChange={(e) =>
                                    handleChange("backgroundColor", e.target.value)
                                }
                                className="flex-1 px-2 py-1 border border-gray-300 rounded text-xs"
                            />
                        </div>
                    </div>

                    {/* Header Color */}
                    <div>
                        <label className="block text-xs font-medium text-gray-600 mb-1">
                            {__("Header Color")}
                        </label>
                        <div className="flex gap-2">
                            <input
                                type="color"
                                value={headerColor}
                                onChange={(e) =>
                                    handleChange("headerColor", e.target.value)
                                }
                                className="h-8 w-10 border border-gray-300 rounded cursor-pointer"
                            />
                            <input
                                type="text"
                                value={headerColor}
                                onChange={(e) =>
                                    handleChange("headerColor", e.target.value)
                                }
                                className="flex-1 px-2 py-1 border border-gray-300 rounded text-xs"
                            />
                        </div>
                    </div>

                    {/* Price Color */}
                    <div>
                        <label className="block text-xs font-medium text-gray-600 mb-1">
                            {__("Price Color")}
                        </label>
                        <div className="flex gap-2">
                            <input
                                type="color"
                                value={priceColor}
                                onChange={(e) =>
                                    handleChange("priceColor", e.target.value)
                                }
                                className="h-8 w-10 border border-gray-300 rounded cursor-pointer"
                            />
                            <input
                                type="text"
                                value={priceColor}
                                onChange={(e) =>
                                    handleChange("priceColor", e.target.value)
                                }
                                className="flex-1 px-2 py-1 border border-gray-300 rounded text-xs"
                            />
                        </div>
                    </div>

                    {/* Text Color */}
                    <div>
                        <label className="block text-xs font-medium text-gray-600 mb-1">
                            {__("Text Color")}
                        </label>
                        <div className="flex gap-2">
                            <input
                                type="color"
                                value={textColor}
                                onChange={(e) =>
                                    handleChange("textColor", e.target.value)
                                }
                                className="h-8 w-10 border border-gray-300 rounded cursor-pointer"
                            />
                            <input
                                type="text"
                                value={textColor}
                                onChange={(e) =>
                                    handleChange("textColor", e.target.value)
                                }
                                className="flex-1 px-2 py-1 border border-gray-300 rounded text-xs"
                            />
                        </div>
                    </div>

                    {/* Border Color */}
                    <div>
                        <label className="block text-xs font-medium text-gray-600 mb-1">
                            {__("Border Color")}
                        </label>
                        <div className="flex gap-2">
                            <input
                                type="color"
                                value={borderColor}
                                onChange={(e) =>
                                    handleChange("borderColor", e.target.value)
                                }
                                className="h-8 w-10 border border-gray-300 rounded cursor-pointer"
                            />
                            <input
                                type="text"
                                value={borderColor}
                                onChange={(e) =>
                                    handleChange("borderColor", e.target.value)
                                }
                                className="flex-1 px-2 py-1 border border-gray-300 rounded text-xs"
                            />
                        </div>
                    </div>
                </div>
            </div>

            {/* Border Radius */}
            <div className="border-t border-gray-200 pt-5">
                <label className="block text-sm font-medium text-gray-700 mb-2">
                    {__("Border Radius")}
                </label>
                <div className="grid grid-cols-5 gap-2">
                    {borderRadiusPresets.map((preset) => (
                        <button
                            key={preset.value}
                            onClick={() => handleChange("borderRadius", preset.value)}
                            className={`
                                px-2 py-2 text-xs font-medium rounded-lg border-2 transition-all
                                ${borderRadius === preset.value
                                    ? "border-primary bg-primary/10 text-primary"
                                    : "border-gray-200 text-gray-700 hover:border-gray-300"
                                }
                            `}
                        >
                            {preset.label}
                        </button>
                    ))}
                </div>
            </div>
        </div>
    );
};

export default PricingTableEditor;
