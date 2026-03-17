/**
 * Badge Block - Properties Panel Editor
 *
 * Sidebar controls for configuring the badge block.
 */

import { __ } from "@lara-builder/i18n";

const BadgeEditor = ({ props, onUpdate }) => {
    const {
        text = "NEW",
        variant = "soft",
        size = "sm",
        color = "#3b82f6",
        textColor = "#1e40af",
        icon = "",
        borderRadius = "pill",
    } = props;

    const handleChange = (key, value) => {
        onUpdate({ ...props, [key]: value });
    };

    return (
        <div className="space-y-5">
            {/* Text */}
            <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                    {__("Text")}
                </label>
                <input
                    type="text"
                    value={text}
                    onChange={(e) => handleChange("text", e.target.value)}
                    placeholder={__("Badge text")}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-transparent"
                />
            </div>

            {/* Variant */}
            <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                    {__("Variant")}
                </label>
                <div className="grid grid-cols-3 gap-2">
                    {[
                        { value: "solid", label: __("Solid") },
                        { value: "outline", label: __("Outline") },
                        { value: "soft", label: __("Soft") },
                    ].map((v) => (
                        <button
                            key={v.value}
                            onClick={() => handleChange("variant", v.value)}
                            className={`
                                px-3 py-2 text-sm font-medium rounded-lg border-2 transition-all
                                ${variant === v.value
                                    ? "border-primary bg-primary/10 text-primary"
                                    : "border-gray-200 text-gray-700 hover:border-gray-300"
                                }
                            `}
                        >
                            {v.label}
                        </button>
                    ))}
                </div>
            </div>

            {/* Size */}
            <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                    {__("Size")}
                </label>
                <div className="grid grid-cols-3 gap-2">
                    {[
                        { value: "sm", label: __("Small") },
                        { value: "md", label: __("Medium") },
                        { value: "lg", label: __("Large") },
                    ].map((s) => (
                        <button
                            key={s.value}
                            onClick={() => handleChange("size", s.value)}
                            className={`
                                px-3 py-2 text-sm font-medium rounded-lg border-2 transition-all
                                ${size === s.value
                                    ? "border-primary bg-primary/10 text-primary"
                                    : "border-gray-200 text-gray-700 hover:border-gray-300"
                                }
                            `}
                        >
                            {s.label}
                        </button>
                    ))}
                </div>
            </div>

            {/* Color */}
            <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                    {__("Badge Color")}
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

            {/* Text Color */}
            <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                    {__("Text Color")}
                </label>
                <div className="flex gap-2">
                    <input
                        type="color"
                        value={textColor}
                        onChange={(e) => handleChange("textColor", e.target.value)}
                        className="h-10 w-12 border border-gray-300 rounded cursor-pointer"
                    />
                    <input
                        type="text"
                        value={textColor}
                        onChange={(e) => handleChange("textColor", e.target.value)}
                        className="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm"
                    />
                </div>
                <p className="mt-1 text-xs text-gray-500">
                    {__("Used for outline and soft variants")}
                </p>
            </div>

            {/* Icon */}
            <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                    {__("Icon (optional)")}
                </label>
                <input
                    type="text"
                    value={icon}
                    onChange={(e) => handleChange("icon", e.target.value)}
                    placeholder="lucide:check"
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-transparent"
                />
                <p className="mt-1 text-xs text-gray-500">
                    {__("Iconify icon name, e.g.")} <code>lucide:check</code>
                </p>
            </div>

            {/* Border Radius */}
            <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                    {__("Shape")}
                </label>
                <div className="grid grid-cols-2 gap-2">
                    {[
                        { value: "rounded", label: __("Rounded") },
                        { value: "pill", label: __("Pill") },
                    ].map((r) => (
                        <button
                            key={r.value}
                            onClick={() => handleChange("borderRadius", r.value)}
                            className={`
                                px-3 py-2 text-sm font-medium rounded-lg border-2 transition-all
                                ${borderRadius === r.value
                                    ? "border-primary bg-primary/10 text-primary"
                                    : "border-gray-200 text-gray-700 hover:border-gray-300"
                                }
                            `}
                        >
                            {r.label}
                        </button>
                    ))}
                </div>
            </div>

            {/* Preview */}
            <div className="border-t border-gray-200 pt-4">
                <label className="block text-sm font-medium text-gray-700 mb-2">
                    {__("Preview")}
                </label>
                <div className="flex justify-center p-4 bg-gray-50 rounded-lg">
                    <span
                        style={{
                            display: "inline-flex",
                            alignItems: "center",
                            gap: "4px",
                            fontWeight: "600",
                            fontSize: size === "sm" ? "12px" : size === "md" ? "14px" : "16px",
                            padding: size === "sm" ? "2px 8px" : size === "md" ? "4px 12px" : "6px 16px",
                            borderRadius: borderRadius === "pill" ? "9999px" : "6px",
                            letterSpacing: "0.025em",
                            ...(variant === "solid"
                                ? { backgroundColor: color, color: "#ffffff" }
                                : variant === "outline"
                                    ? { backgroundColor: "transparent", color: textColor, border: `1.5px solid ${color}` }
                                    : { backgroundColor: color + "26", color: textColor }
                            ),
                        }}
                    >
                        {icon && (
                            <iconify-icon
                                icon={icon}
                                width={size === "sm" ? "12" : size === "md" ? "14" : "16"}
                                height={size === "sm" ? "12" : size === "md" ? "14" : "16"}
                                aria-hidden="true"
                            />
                        )}
                        {text}
                    </span>
                </div>
            </div>
        </div>
    );
};

export default BadgeEditor;
