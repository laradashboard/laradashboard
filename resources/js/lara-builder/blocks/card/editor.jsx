import { __ } from "@lara-builder/i18n";

const CardEditor = ({ props, onUpdate }) => {
    const {
        backgroundColor = "#ffffff",
        borderColor = "#e5e7eb",
        borderWidth = "1px",
        borderRadius = "12px",
        shadow = "sm",
        hoverShadow = "md",
        hoverScale = "none",
        padding = "24px",
    } = props;

    const handleChange = (key, value) => {
        onUpdate({ ...props, [key]: value });
    };

    const shadowOptions = [
        { value: "none", label: __("None") },
        { value: "sm", label: __("Small") },
        { value: "md", label: __("Medium") },
        { value: "lg", label: __("Large") },
        { value: "xl", label: __("Extra Large") },
    ];

    const borderWidthOptions = [
        { value: "0px", label: __("None") },
        { value: "1px", label: "1px" },
        { value: "2px", label: "2px" },
        { value: "3px", label: "3px" },
        { value: "4px", label: "4px" },
    ];

    const scaleOptions = [
        { value: "none", label: __("None") },
        { value: "1.02", label: "1.02" },
        { value: "1.05", label: "1.05" },
    ];

    return (
        <div className="space-y-5">
            {/* Background Color */}
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

            {/* Border Settings */}
            <div className="border-t border-gray-200 pt-5">
                <h4 className="text-sm font-semibold text-gray-700 mb-4">
                    {__("Border")}
                </h4>

                {/* Border Color */}
                <div className="mb-4">
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

                {/* Border Width */}
                <div className="mb-4">
                    <label className="block text-xs font-medium text-gray-600 mb-1">
                        {__("Border Width")}
                    </label>
                    <select
                        value={borderWidth}
                        onChange={(e) => handleChange("borderWidth", e.target.value)}
                        className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                    >
                        {borderWidthOptions.map((opt) => (
                            <option key={opt.value} value={opt.value}>
                                {opt.label}
                            </option>
                        ))}
                    </select>
                </div>

                {/* Border Radius */}
                <div>
                    <label className="block text-xs font-medium text-gray-600 mb-1">
                        {__("Border Radius")}
                    </label>
                    <input
                        type="text"
                        value={borderRadius}
                        onChange={(e) => handleChange("borderRadius", e.target.value)}
                        className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                        placeholder="12px"
                    />
                </div>
            </div>

            {/* Shadow Settings */}
            <div className="border-t border-gray-200 pt-5">
                <h4 className="text-sm font-semibold text-gray-700 mb-4">
                    {__("Shadow")}
                </h4>

                {/* Shadow */}
                <div className="mb-4">
                    <label className="block text-xs font-medium text-gray-600 mb-1">
                        {__("Shadow")}
                    </label>
                    <select
                        value={shadow}
                        onChange={(e) => handleChange("shadow", e.target.value)}
                        className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                    >
                        {shadowOptions.map((opt) => (
                            <option key={opt.value} value={opt.value}>
                                {opt.label}
                            </option>
                        ))}
                    </select>
                </div>

                {/* Hover Shadow */}
                <div>
                    <label className="block text-xs font-medium text-gray-600 mb-1">
                        {__("Hover Shadow")}
                    </label>
                    <select
                        value={hoverShadow}
                        onChange={(e) => handleChange("hoverShadow", e.target.value)}
                        className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                    >
                        {shadowOptions.map((opt) => (
                            <option key={opt.value} value={opt.value}>
                                {opt.label}
                            </option>
                        ))}
                    </select>
                </div>
            </div>

            {/* Hover Effects */}
            <div className="border-t border-gray-200 pt-5">
                <h4 className="text-sm font-semibold text-gray-700 mb-4">
                    {__("Hover Effects")}
                </h4>

                {/* Hover Scale */}
                <div>
                    <label className="block text-xs font-medium text-gray-600 mb-1">
                        {__("Hover Scale")}
                    </label>
                    <select
                        value={hoverScale}
                        onChange={(e) => handleChange("hoverScale", e.target.value)}
                        className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                    >
                        {scaleOptions.map((opt) => (
                            <option key={opt.value} value={opt.value}>
                                {opt.label}
                            </option>
                        ))}
                    </select>
                </div>
            </div>

            {/* Padding */}
            <div className="border-t border-gray-200 pt-5">
                <label className="block text-sm font-medium text-gray-700 mb-2">
                    {__("Padding")}
                </label>
                <input
                    type="text"
                    value={padding}
                    onChange={(e) => handleChange("padding", e.target.value)}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                    placeholder="24px"
                />
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
                            {__("Drag blocks inside the card to build your content. Use shadow and hover effects to create interactive cards.")}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default CardEditor;
