import { __ } from "@lara-builder/i18n";

const StatsItemEditor = ({ props, onUpdate }) => {
    const {
        value = "100+",
        valueColor = "#3b82f6",
        valueSize = "48px",
        label = "Happy Customers",
        labelColor = "#6b7280",
        labelSize = "14px",
        align = "center",
        prefix = "",
        suffix = "",
    } = props;

    const handleChange = (key, val) => {
        onUpdate({ ...props, [key]: val });
    };

    const valueSizePresets = [
        { value: "32px", label: "SM" },
        { value: "40px", label: "MD" },
        { value: "48px", label: "LG" },
        { value: "56px", label: "XL" },
        { value: "64px", label: "2XL" },
    ];

    const labelSizePresets = [
        { value: "12px", label: "XS" },
        { value: "14px", label: "SM" },
        { value: "16px", label: "MD" },
        { value: "18px", label: "LG" },
    ];

    return (
        <div className="space-y-5">
            {/* Value */}
            <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                    {__("Value")}
                </label>
                <input
                    type="text"
                    value={value}
                    onChange={(e) => handleChange("value", e.target.value)}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                    placeholder="100+"
                />
            </div>

            {/* Prefix & Suffix */}
            <div className="grid grid-cols-2 gap-4">
                <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                        {__("Prefix")}
                    </label>
                    <input
                        type="text"
                        value={prefix}
                        onChange={(e) => handleChange("prefix", e.target.value)}
                        className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                        placeholder="$"
                    />
                </div>
                <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                        {__("Suffix")}
                    </label>
                    <input
                        type="text"
                        value={suffix}
                        onChange={(e) => handleChange("suffix", e.target.value)}
                        className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                        placeholder="%"
                    />
                </div>
            </div>

            {/* Value Style */}
            <div className="border-t border-gray-200 pt-5">
                <h4 className="text-sm font-semibold text-gray-700 mb-4">
                    {__("Value Style")}
                </h4>

                {/* Value Size */}
                <div className="mb-4">
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                        {__("Value Size")}
                    </label>
                    <div className="grid grid-cols-5 gap-2">
                        {valueSizePresets.map((preset) => (
                            <button
                                key={preset.value}
                                onClick={() => handleChange("valueSize", preset.value)}
                                className={`
                                    px-2 py-2 text-xs font-medium rounded-lg border-2 transition-all
                                    ${valueSize === preset.value
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

                {/* Value Color */}
                <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                        {__("Value Color")}
                    </label>
                    <div className="flex gap-2">
                        <input
                            type="color"
                            value={valueColor}
                            onChange={(e) => handleChange("valueColor", e.target.value)}
                            className="h-10 w-12 border border-gray-300 rounded cursor-pointer"
                        />
                        <input
                            type="text"
                            value={valueColor}
                            onChange={(e) => handleChange("valueColor", e.target.value)}
                            className="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm"
                        />
                    </div>
                </div>
            </div>

            {/* Label */}
            <div className="border-t border-gray-200 pt-5">
                <h4 className="text-sm font-semibold text-gray-700 mb-4">
                    {__("Label")}
                </h4>

                {/* Label Text */}
                <div className="mb-4">
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                        {__("Label Text")}
                    </label>
                    <input
                        type="text"
                        value={label}
                        onChange={(e) => handleChange("label", e.target.value)}
                        className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                        placeholder="Happy Customers"
                    />
                </div>

                {/* Label Size */}
                <div className="mb-4">
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                        {__("Label Size")}
                    </label>
                    <div className="grid grid-cols-4 gap-2">
                        {labelSizePresets.map((preset) => (
                            <button
                                key={preset.value}
                                onClick={() => handleChange("labelSize", preset.value)}
                                className={`
                                    px-2 py-2 text-xs font-medium rounded-lg border-2 transition-all
                                    ${labelSize === preset.value
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

                {/* Label Color */}
                <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                        {__("Label Color")}
                    </label>
                    <div className="flex gap-2">
                        <input
                            type="color"
                            value={labelColor}
                            onChange={(e) => handleChange("labelColor", e.target.value)}
                            className="h-10 w-12 border border-gray-300 rounded cursor-pointer"
                        />
                        <input
                            type="text"
                            value={labelColor}
                            onChange={(e) => handleChange("labelColor", e.target.value)}
                            className="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm"
                        />
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

            {/* Preview */}
            <div className="border-t border-gray-200 pt-4">
                <label className="block text-sm font-medium text-gray-700 mb-2">
                    {__("Preview")}
                </label>
                <div className="p-4 bg-gray-50 rounded-lg text-center">
                    <div
                        style={{
                            color: valueColor,
                            fontSize: valueSize,
                            fontWeight: "bold",
                            lineHeight: "1.2",
                            marginBottom: "4px",
                        }}
                    >
                        {prefix}{value}{suffix}
                    </div>
                    <div
                        style={{
                            color: labelColor,
                            fontSize: labelSize,
                        }}
                    >
                        {label}
                    </div>
                </div>
            </div>
        </div>
    );
};

export default StatsItemEditor;
