import { useState } from "react";
import { __ } from "@lara-builder/i18n";

// Popular icons for feature boxes
const popularIcons = [
    { icon: "lucide:lightbulb", label: "Lightbulb" },
    { icon: "lucide:heart", label: "Heart" },
    { icon: "lucide:users", label: "Users" },
    { icon: "lucide:shield", label: "Shield" },
    { icon: "lucide:zap", label: "Zap" },
    { icon: "lucide:target", label: "Target" },
    { icon: "lucide:rocket", label: "Rocket" },
    { icon: "lucide:star", label: "Star" },
    { icon: "lucide:check-circle", label: "Check Circle" },
    { icon: "lucide:award", label: "Award" },
    { icon: "lucide:globe", label: "Globe" },
    { icon: "lucide:settings", label: "Settings" },
];

const FeatureBoxEditor = ({ props, onUpdate }) => {
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

    const [customIcon, setCustomIcon] = useState(icon);

    const handleChange = (key, value) => {
        onUpdate({ ...props, [key]: value });
    };

    const handleCustomIconApply = () => {
        handleChange("icon", customIcon);
    };

    const iconSizePresets = [
        { value: "24px", label: "SM" },
        { value: "32px", label: "MD" },
        { value: "40px", label: "LG" },
        { value: "48px", label: "XL" },
    ];

    const textSizePresets = [
        { value: "14px", label: "SM" },
        { value: "16px", label: "MD" },
        { value: "18px", label: "LG" },
        { value: "20px", label: "XL" },
    ];

    return (
        <div className="space-y-5">
            {/* Icon Selection */}
            <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                    {__("Icon")}
                </label>

                {/* Custom Icon Input */}
                <div className="flex gap-2 mb-3">
                    <input
                        type="text"
                        value={customIcon}
                        onChange={(e) => setCustomIcon(e.target.value)}
                        onKeyDown={(e) => e.key === "Enter" && handleCustomIconApply()}
                        placeholder="lucide:icon-name"
                        className="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm"
                    />
                    <button
                        onClick={handleCustomIconApply}
                        className="px-3 py-2 bg-primary text-white rounded-lg text-sm hover:bg-primary/90"
                    >
                        {__("Apply")}
                    </button>
                </div>

                {/* Popular Icons Grid */}
                <div className="grid grid-cols-6 gap-2">
                    {popularIcons.map((item) => (
                        <button
                            key={item.icon}
                            onClick={() => {
                                handleChange("icon", item.icon);
                                setCustomIcon(item.icon);
                            }}
                            title={item.label}
                            className={`
                                p-2 rounded-lg border-2 transition-all flex items-center justify-center
                                ${icon === item.icon
                                    ? "border-primary bg-primary/10"
                                    : "border-gray-200 hover:border-gray-300"
                                }
                            `}
                        >
                            <iconify-icon icon={item.icon} width="20" height="20" />
                        </button>
                    ))}
                </div>
            </div>

            {/* Icon Settings */}
            <div className="border-t border-gray-200 pt-5">
                <h4 className="text-sm font-semibold text-gray-700 mb-4">
                    {__("Icon Style")}
                </h4>

                {/* Icon Size */}
                <div className="mb-4">
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                        {__("Icon Size")}
                    </label>
                    <div className="grid grid-cols-4 gap-2">
                        {iconSizePresets.map((preset) => (
                            <button
                                key={preset.value}
                                onClick={() => handleChange("iconSize", preset.value)}
                                className={`
                                    px-2 py-2 text-xs font-medium rounded-lg border-2 transition-all
                                    ${iconSize === preset.value
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

                {/* Icon Color */}
                <div className="mb-4">
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                        {__("Icon Color")}
                    </label>
                    <div className="flex gap-2">
                        <input
                            type="color"
                            value={iconColor}
                            onChange={(e) => handleChange("iconColor", e.target.value)}
                            className="h-10 w-12 border border-gray-300 rounded cursor-pointer"
                        />
                        <input
                            type="text"
                            value={iconColor}
                            onChange={(e) => handleChange("iconColor", e.target.value)}
                            className="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm"
                        />
                    </div>
                </div>

                {/* Icon Background Shape */}
                <div className="mb-4">
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                        {__("Icon Background")}
                    </label>
                    <div className="grid grid-cols-4 gap-2">
                        {[
                            { value: "none", label: __("None") },
                            { value: "circle", label: __("Circle") },
                            { value: "rounded", label: __("Rounded") },
                            { value: "square", label: __("Square") },
                        ].map((shape) => (
                            <button
                                key={shape.value}
                                onClick={() => handleChange("iconBackgroundShape", shape.value)}
                                className={`
                                    px-2 py-2 text-xs font-medium rounded-lg border-2 transition-all
                                    ${iconBackgroundShape === shape.value
                                        ? "border-primary bg-primary/10 text-primary"
                                        : "border-gray-200 text-gray-700 hover:border-gray-300"
                                    }
                                `}
                            >
                                {shape.label}
                            </button>
                        ))}
                    </div>
                </div>

                {/* Icon Background Color */}
                {iconBackgroundShape !== "none" && (
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-2">
                            {__("Background Color")}
                        </label>
                        <div className="flex gap-2">
                            <input
                                type="color"
                                value={iconBackgroundColor || "#dbeafe"}
                                onChange={(e) => handleChange("iconBackgroundColor", e.target.value)}
                                className="h-10 w-12 border border-gray-300 rounded cursor-pointer"
                            />
                            <input
                                type="text"
                                value={iconBackgroundColor}
                                onChange={(e) => handleChange("iconBackgroundColor", e.target.value)}
                                className="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm"
                            />
                        </div>
                    </div>
                )}
            </div>

            {/* Text Settings */}
            <div className="border-t border-gray-200 pt-5">
                <h4 className="text-sm font-semibold text-gray-700 mb-4">
                    {__("Text Style")}
                </h4>

                {/* Title */}
                <div className="mb-4">
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                        {__("Title")}
                    </label>
                    <input
                        type="text"
                        value={title}
                        onChange={(e) => handleChange("title", e.target.value)}
                        className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                    />
                </div>

                {/* Title Color & Size */}
                <div className="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label className="block text-xs font-medium text-gray-600 mb-1">
                            {__("Title Color")}
                        </label>
                        <div className="flex gap-1">
                            <input
                                type="color"
                                value={titleColor}
                                onChange={(e) => handleChange("titleColor", e.target.value)}
                                className="h-8 w-10 border border-gray-300 rounded cursor-pointer"
                            />
                            <input
                                type="text"
                                value={titleColor}
                                onChange={(e) => handleChange("titleColor", e.target.value)}
                                className="flex-1 px-2 py-1 border border-gray-300 rounded text-xs"
                            />
                        </div>
                    </div>
                    <div>
                        <label className="block text-xs font-medium text-gray-600 mb-1">
                            {__("Title Size")}
                        </label>
                        <select
                            value={titleSize}
                            onChange={(e) => handleChange("titleSize", e.target.value)}
                            className="w-full px-2 py-1.5 border border-gray-300 rounded text-sm"
                        >
                            {textSizePresets.map((preset) => (
                                <option key={preset.value} value={preset.value}>
                                    {preset.label} ({preset.value})
                                </option>
                            ))}
                        </select>
                    </div>
                </div>

                {/* Description */}
                <div className="mb-4">
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                        {__("Description")}
                    </label>
                    <textarea
                        value={description}
                        onChange={(e) => handleChange("description", e.target.value)}
                        rows={3}
                        className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                    />
                </div>

                {/* Description Color & Size */}
                <div className="grid grid-cols-2 gap-4">
                    <div>
                        <label className="block text-xs font-medium text-gray-600 mb-1">
                            {__("Description Color")}
                        </label>
                        <div className="flex gap-1">
                            <input
                                type="color"
                                value={descriptionColor}
                                onChange={(e) => handleChange("descriptionColor", e.target.value)}
                                className="h-8 w-10 border border-gray-300 rounded cursor-pointer"
                            />
                            <input
                                type="text"
                                value={descriptionColor}
                                onChange={(e) => handleChange("descriptionColor", e.target.value)}
                                className="flex-1 px-2 py-1 border border-gray-300 rounded text-xs"
                            />
                        </div>
                    </div>
                    <div>
                        <label className="block text-xs font-medium text-gray-600 mb-1">
                            {__("Description Size")}
                        </label>
                        <select
                            value={descriptionSize}
                            onChange={(e) => handleChange("descriptionSize", e.target.value)}
                            className="w-full px-2 py-1.5 border border-gray-300 rounded text-sm"
                        >
                            {[
                                { value: "12px", label: "XS" },
                                { value: "14px", label: "SM" },
                                { value: "16px", label: "MD" },
                            ].map((preset) => (
                                <option key={preset.value} value={preset.value}>
                                    {preset.label} ({preset.value})
                                </option>
                            ))}
                        </select>
                    </div>
                </div>
            </div>

            {/* Alignment */}
            <div className="border-t border-gray-200 pt-5">
                <label className="block text-sm font-medium text-gray-700 mb-2">
                    {__("Alignment")}
                </label>
                <div className="grid grid-cols-3 gap-2">
                    {["left", "center", "right"].map((a) => (
                        <button
                            key={a}
                            onClick={() => handleChange("align", a)}
                            className={`
                                px-3 py-2 text-sm font-medium rounded-lg border-2 transition-all capitalize
                                ${align === a
                                    ? "border-primary bg-primary/10 text-primary"
                                    : "border-gray-200 text-gray-700 hover:border-gray-300"
                                }
                            `}
                        >
                            {__(a.charAt(0).toUpperCase() + a.slice(1))}
                        </button>
                    ))}
                </div>
            </div>
        </div>
    );
};

export default FeatureBoxEditor;
