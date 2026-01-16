import { useState } from "react";
import { __ } from "@lara-builder/i18n";

// Popular icons for quick selection
const popularIcons = [
    { icon: "lucide:star", label: "Star" },
    { icon: "lucide:heart", label: "Heart" },
    { icon: "lucide:check", label: "Check" },
    { icon: "lucide:x", label: "X" },
    { icon: "lucide:plus", label: "Plus" },
    { icon: "lucide:minus", label: "Minus" },
    { icon: "lucide:arrow-right", label: "Arrow Right" },
    { icon: "lucide:arrow-left", label: "Arrow Left" },
    { icon: "lucide:chevron-right", label: "Chevron Right" },
    { icon: "lucide:chevron-down", label: "Chevron Down" },
    { icon: "lucide:user", label: "User" },
    { icon: "lucide:users", label: "Users" },
    { icon: "lucide:mail", label: "Mail" },
    { icon: "lucide:phone", label: "Phone" },
    { icon: "lucide:home", label: "Home" },
    { icon: "lucide:settings", label: "Settings" },
    { icon: "lucide:search", label: "Search" },
    { icon: "lucide:menu", label: "Menu" },
    { icon: "lucide:target", label: "Target" },
    { icon: "lucide:lightbulb", label: "Lightbulb" },
    { icon: "lucide:zap", label: "Zap" },
    { icon: "lucide:shield", label: "Shield" },
    { icon: "lucide:globe", label: "Globe" },
    { icon: "lucide:rocket", label: "Rocket" },
];

const IconEditor = ({ props, onUpdate }) => {
    const {
        icon = "lucide:star",
        size = "48px",
        color = "#3b82f6",
        align = "center",
        backgroundColor = "",
        backgroundShape = "none",
        backgroundPadding = "16px",
    } = props;

    const [customIcon, setCustomIcon] = useState(icon);

    const handleChange = (key, value) => {
        onUpdate({ ...props, [key]: value });
    };

    const handleCustomIconApply = () => {
        handleChange("icon", customIcon);
    };

    const sizePresets = [
        { value: "24px", label: "XS" },
        { value: "32px", label: "SM" },
        { value: "48px", label: "MD" },
        { value: "64px", label: "LG" },
        { value: "80px", label: "XL" },
        { value: "96px", label: "2XL" },
    ];

    const paddingPresets = [
        { value: "8px", label: "XS" },
        { value: "12px", label: "SM" },
        { value: "16px", label: "MD" },
        { value: "20px", label: "LG" },
        { value: "24px", label: "XL" },
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
                        className="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-transparent"
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

                <p className="mt-2 text-xs text-gray-500">
                    {__("Browse more icons at")}{" "}
                    <a
                        href="https://icon-sets.iconify.design/"
                        target="_blank"
                        rel="noopener noreferrer"
                        className="text-primary hover:underline"
                    >
                        Iconify
                    </a>
                </p>
            </div>

            {/* Size */}
            <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                    {__("Size")}
                </label>
                <div className="grid grid-cols-6 gap-2">
                    {sizePresets.map((preset) => (
                        <button
                            key={preset.value}
                            onClick={() => handleChange("size", preset.value)}
                            className={`
                                px-2 py-2 text-xs font-medium rounded-lg border-2 transition-all
                                ${size === preset.value
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

            {/* Color */}
            <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                    {__("Icon Color")}
                </label>
                <div className="flex gap-2">
                    <input
                        type="color"
                        value={color}
                        onChange={(e) => handleChange("color", e.target.value)}
                        className="h-10 w-12 border border-gray-300 rounded cursor-pointer"
                    />
                    <input
                        type="text"
                        value={color}
                        onChange={(e) => handleChange("color", e.target.value)}
                        className="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm"
                    />
                </div>
            </div>

            {/* Alignment */}
            <div>
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

            {/* Background Settings */}
            <div className="border-t border-gray-200 pt-5">
                <h4 className="text-sm font-semibold text-gray-700 mb-4">
                    {__("Background")}
                </h4>

                {/* Background Shape */}
                <div className="mb-4">
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                        {__("Background Shape")}
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
                                onClick={() => handleChange("backgroundShape", shape.value)}
                                className={`
                                    px-2 py-2 text-xs font-medium rounded-lg border-2 transition-all
                                    ${backgroundShape === shape.value
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

                {/* Background Color */}
                {backgroundShape !== "none" && (
                    <>
                        <div className="mb-4">
                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                {__("Background Color")}
                            </label>
                            <div className="flex gap-2">
                                <input
                                    type="color"
                                    value={backgroundColor || "#e5e7eb"}
                                    onChange={(e) => handleChange("backgroundColor", e.target.value)}
                                    className="h-10 w-12 border border-gray-300 rounded cursor-pointer"
                                />
                                <input
                                    type="text"
                                    value={backgroundColor}
                                    onChange={(e) => handleChange("backgroundColor", e.target.value)}
                                    placeholder="#e5e7eb"
                                    className="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm"
                                />
                            </div>
                        </div>

                        {/* Background Padding */}
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                {__("Background Padding")}
                            </label>
                            <div className="grid grid-cols-5 gap-2">
                                {paddingPresets.map((preset) => (
                                    <button
                                        key={preset.value}
                                        onClick={() => handleChange("backgroundPadding", preset.value)}
                                        className={`
                                            px-2 py-2 text-xs font-medium rounded-lg border-2 transition-all
                                            ${backgroundPadding === preset.value
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
                    </>
                )}
            </div>

            {/* Preview */}
            <div className="border-t border-gray-200 pt-4">
                <label className="block text-sm font-medium text-gray-700 mb-2">
                    {__("Preview")}
                </label>
                <div className="flex justify-center p-4 bg-gray-50 rounded-lg">
                    {backgroundShape !== "none" && backgroundColor ? (
                        <div
                            style={{
                                backgroundColor,
                                padding: backgroundPadding,
                                borderRadius: backgroundShape === "circle" ? "50%" :
                                    backgroundShape === "rounded" ? "12px" : "0",
                                display: "inline-flex",
                            }}
                        >
                            <iconify-icon icon={icon} width={size} height={size} style={{ color }} />
                        </div>
                    ) : (
                        <iconify-icon icon={icon} width={size} height={size} style={{ color }} />
                    )}
                </div>
            </div>
        </div>
    );
};

export default IconEditor;
