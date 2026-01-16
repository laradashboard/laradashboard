import { __ } from "@lara-builder/i18n";

const SectionEditor = ({ props, onUpdate }) => {
    const {
        fullWidth = true,
        containerMaxWidth = "1280px",
        contentAlign = "center",
        backgroundType = "solid",
        backgroundColor = "#ffffff",
        gradientFrom = "#f9fafb",
        gradientTo = "#f3f4f6",
        gradientDirection = "to-br",
    } = props;

    const handleChange = (key, value) => {
        onUpdate({ ...props, [key]: value });
    };

    const gradientDirections = [
        { value: "to-t", label: __("To Top") },
        { value: "to-tr", label: __("To Top Right") },
        { value: "to-r", label: __("To Right") },
        { value: "to-br", label: __("To Bottom Right") },
        { value: "to-b", label: __("To Bottom") },
        { value: "to-bl", label: __("To Bottom Left") },
        { value: "to-l", label: __("To Left") },
        { value: "to-tl", label: __("To Top Left") },
    ];

    const maxWidthPresets = [
        { value: "640px", label: "sm (640px)" },
        { value: "768px", label: "md (768px)" },
        { value: "1024px", label: "lg (1024px)" },
        { value: "1280px", label: "xl (1280px)" },
        { value: "1536px", label: "2xl (1536px)" },
        { value: "100%", label: __("Full") },
    ];

    return (
        <div className="space-y-5">
            {/* Background Type */}
            <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                    {__("Background Type")}
                </label>
                <div className="grid grid-cols-2 gap-2">
                    <button
                        onClick={() => handleChange("backgroundType", "solid")}
                        className={`
                            px-3 py-2 text-sm font-medium rounded-lg border-2 transition-all
                            ${backgroundType === "solid"
                                ? "border-primary bg-primary/10 text-primary"
                                : "border-gray-200 bg-white text-gray-700 hover:border-gray-300"
                            }
                        `}
                    >
                        {__("Solid Color")}
                    </button>
                    <button
                        onClick={() => handleChange("backgroundType", "gradient")}
                        className={`
                            px-3 py-2 text-sm font-medium rounded-lg border-2 transition-all
                            ${backgroundType === "gradient"
                                ? "border-primary bg-primary/10 text-primary"
                                : "border-gray-200 bg-white text-gray-700 hover:border-gray-300"
                            }
                        `}
                    >
                        {__("Gradient")}
                    </button>
                </div>
            </div>

            {/* Solid Color */}
            {backgroundType === "solid" && (
                <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                        {__("Background Color")}
                    </label>
                    <div className="flex gap-2">
                        <input
                            type="color"
                            value={backgroundColor}
                            onChange={(e) => handleChange("backgroundColor", e.target.value)}
                            className="h-10 w-12 border border-gray-300 rounded cursor-pointer"
                        />
                        <input
                            type="text"
                            value={backgroundColor}
                            onChange={(e) => handleChange("backgroundColor", e.target.value)}
                            className="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm"
                            placeholder="#ffffff"
                        />
                    </div>
                </div>
            )}

            {/* Gradient Colors */}
            {backgroundType === "gradient" && (
                <>
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-2">
                            {__("Gradient From")}
                        </label>
                        <div className="flex gap-2">
                            <input
                                type="color"
                                value={gradientFrom}
                                onChange={(e) => handleChange("gradientFrom", e.target.value)}
                                className="h-10 w-12 border border-gray-300 rounded cursor-pointer"
                            />
                            <input
                                type="text"
                                value={gradientFrom}
                                onChange={(e) => handleChange("gradientFrom", e.target.value)}
                                className="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm"
                                placeholder="#f9fafb"
                            />
                        </div>
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-2">
                            {__("Gradient To")}
                        </label>
                        <div className="flex gap-2">
                            <input
                                type="color"
                                value={gradientTo}
                                onChange={(e) => handleChange("gradientTo", e.target.value)}
                                className="h-10 w-12 border border-gray-300 rounded cursor-pointer"
                            />
                            <input
                                type="text"
                                value={gradientTo}
                                onChange={(e) => handleChange("gradientTo", e.target.value)}
                                className="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm"
                                placeholder="#f3f4f6"
                            />
                        </div>
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-2">
                            {__("Gradient Direction")}
                        </label>
                        <select
                            value={gradientDirection}
                            onChange={(e) => handleChange("gradientDirection", e.target.value)}
                            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                        >
                            {gradientDirections.map((dir) => (
                                <option key={dir.value} value={dir.value}>
                                    {dir.label}
                                </option>
                            ))}
                        </select>
                    </div>

                    {/* Gradient Preview */}
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-2">
                            {__("Preview")}
                        </label>
                        <div
                            className="h-16 rounded-lg border border-gray-200"
                            style={{
                                background: `linear-gradient(${
                                    gradientDirection === "to-br" ? "to bottom right" :
                                    gradientDirection === "to-b" ? "to bottom" :
                                    gradientDirection === "to-r" ? "to right" :
                                    gradientDirection === "to-tr" ? "to top right" :
                                    gradientDirection === "to-t" ? "to top" :
                                    gradientDirection === "to-tl" ? "to top left" :
                                    gradientDirection === "to-l" ? "to left" :
                                    "to bottom left"
                                }, ${gradientFrom}, ${gradientTo})`,
                            }}
                        />
                    </div>
                </>
            )}

            {/* Container Settings */}
            <div className="border-t border-gray-200 pt-5">
                <h4 className="text-sm font-semibold text-gray-700 mb-4">
                    {__("Container Settings")}
                </h4>

                {/* Full Width Toggle */}
                <div className="flex items-center justify-between mb-4">
                    <div>
                        <label className="block text-sm font-medium text-gray-700">
                            {__("Contained Content")}
                        </label>
                        <p className="text-xs text-gray-500">
                            {__("Limit content width with a max-width container")}
                        </p>
                    </div>
                    <button
                        onClick={() => handleChange("fullWidth", !fullWidth)}
                        className={`
                            relative inline-flex h-6 w-11 items-center rounded-full transition-colors
                            ${fullWidth ? "bg-primary" : "bg-gray-300"}
                        `}
                    >
                        <span
                            className={`
                                inline-block h-4 w-4 transform rounded-full bg-white transition-transform
                                ${fullWidth ? "translate-x-6" : "translate-x-1"}
                            `}
                        />
                    </button>
                </div>

                {/* Container Max Width */}
                {fullWidth && (
                    <div className="mb-4">
                        <label className="block text-sm font-medium text-gray-700 mb-2">
                            {__("Container Max Width")}
                        </label>
                        <select
                            value={containerMaxWidth}
                            onChange={(e) => handleChange("containerMaxWidth", e.target.value)}
                            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                        >
                            {maxWidthPresets.map((preset) => (
                                <option key={preset.value} value={preset.value}>
                                    {preset.label}
                                </option>
                            ))}
                        </select>
                    </div>
                )}

                {/* Content Alignment */}
                <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                        {__("Content Alignment")}
                    </label>
                    <div className="grid grid-cols-3 gap-2">
                        {["left", "center", "right"].map((align) => (
                            <button
                                key={align}
                                onClick={() => handleChange("contentAlign", align)}
                                className={`
                                    px-3 py-2 text-sm font-medium rounded-lg border-2 transition-all capitalize
                                    ${contentAlign === align
                                        ? "border-primary bg-primary/10 text-primary"
                                        : "border-gray-200 bg-white text-gray-700 hover:border-gray-300"
                                    }
                                `}
                            >
                                {__(align.charAt(0).toUpperCase() + align.slice(1))}
                            </button>
                        ))}
                    </div>
                </div>
            </div>

            {/* Tip */}
            <div className="pt-4 border-t border-gray-200">
                <div className="flex items-start space-x-2 text-sm text-gray-600">
                    <svg
                        className="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth={2}
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                    <div>
                        <p className="font-medium text-gray-700">{__("Tip")}:</p>
                        <p className="mt-1">
                            {__("Use sections to create full-width backgrounds with gradient colors. Drag blocks inside the section to build your content.")}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default SectionEditor;
