import { __ } from "@lara-builder/i18n";

const ColumnsEditor = ({ props, onUpdate }) => {
    const {
        columns = 2,
        gap = "20px",
        verticalAlign = "stretch",
        horizontalAlign = "stretch",
        stackOnMobile = true,
    } = props;

    const handleColumnsChange = (newColumns) => {
        const columnCount = Math.min(Math.max(parseInt(newColumns) || 1, 1), 6);
        const currentChildren = props.children || [];

        const newChildren = Array.from(
            { length: columnCount },
            (_, i) => currentChildren[i] || []
        );

        onUpdate({
            ...props,
            columns: columnCount,
            children: newChildren,
        });
    };

    const handleGapChange = (newGap) => {
        onUpdate({ ...props, gap: newGap });
    };

    const handleVerticalAlignChange = (value) => {
        onUpdate({ ...props, verticalAlign: value });
    };

    const handleHorizontalAlignChange = (value) => {
        onUpdate({ ...props, horizontalAlign: value });
    };

    const handleStackOnMobileChange = (value) => {
        onUpdate({ ...props, stackOnMobile: value });
    };

    const verticalAlignOptions = [
        { value: "start", label: __("Top"), icon: "align-start-vertical" },
        { value: "center", label: __("Center"), icon: "align-center-vertical" },
        { value: "end", label: __("Bottom"), icon: "align-end-vertical" },
        {
            value: "stretch",
            label: __("Stretch"),
            icon: "align-stretch-vertical",
        },
    ];

    const horizontalAlignOptions = [
        { value: "start", label: __("Start"), icon: "align-start" },
        { value: "center", label: __("Center"), icon: "align-center" },
        { value: "end", label: __("End"), icon: "align-end" },
        { value: "stretch", label: __("Stretch"), icon: "align-stretch" },
        {
            value: "space-between",
            label: __("Space Between"),
            icon: "space-between",
        },
        {
            value: "space-around",
            label: __("Space Around"),
            icon: "space-around",
        },
    ];

    // SVG icons for alignment
    const AlignIcon = ({ type, isActive }) => {
        const color = isActive ? "currentColor" : "#9ca3af";
        const icons = {
            "align-start-vertical": (
                <svg
                    width="20"
                    height="20"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke={color}
                    strokeWidth="2"
                >
                    <line x1="4" y1="4" x2="20" y2="4" />
                    <rect x="6" y="6" width="4" height="10" rx="1" />
                    <rect x="14" y="6" width="4" height="6" rx="1" />
                </svg>
            ),
            "align-center-vertical": (
                <svg
                    width="20"
                    height="20"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke={color}
                    strokeWidth="2"
                >
                    <line
                        x1="4"
                        y1="12"
                        x2="20"
                        y2="12"
                        strokeDasharray="2 2"
                    />
                    <rect x="6" y="7" width="4" height="10" rx="1" />
                    <rect x="14" y="9" width="4" height="6" rx="1" />
                </svg>
            ),
            "align-end-vertical": (
                <svg
                    width="20"
                    height="20"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke={color}
                    strokeWidth="2"
                >
                    <line x1="4" y1="20" x2="20" y2="20" />
                    <rect x="6" y="8" width="4" height="10" rx="1" />
                    <rect x="14" y="12" width="4" height="6" rx="1" />
                </svg>
            ),
            "align-stretch-vertical": (
                <svg
                    width="20"
                    height="20"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke={color}
                    strokeWidth="2"
                >
                    <line x1="4" y1="4" x2="20" y2="4" />
                    <line x1="4" y1="20" x2="20" y2="20" />
                    <rect x="6" y="6" width="4" height="12" rx="1" />
                    <rect x="14" y="6" width="4" height="12" rx="1" />
                </svg>
            ),
            "align-start": (
                <svg
                    width="20"
                    height="20"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke={color}
                    strokeWidth="2"
                >
                    <line x1="4" y1="4" x2="4" y2="20" />
                    <rect x="6" y="6" width="10" height="4" rx="1" />
                    <rect x="6" y="14" width="6" height="4" rx="1" />
                </svg>
            ),
            "align-center": (
                <svg
                    width="20"
                    height="20"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke={color}
                    strokeWidth="2"
                >
                    <line
                        x1="12"
                        y1="4"
                        x2="12"
                        y2="20"
                        strokeDasharray="2 2"
                    />
                    <rect x="7" y="6" width="10" height="4" rx="1" />
                    <rect x="9" y="14" width="6" height="4" rx="1" />
                </svg>
            ),
            "align-end": (
                <svg
                    width="20"
                    height="20"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke={color}
                    strokeWidth="2"
                >
                    <line x1="20" y1="4" x2="20" y2="20" />
                    <rect x="8" y="6" width="10" height="4" rx="1" />
                    <rect x="12" y="14" width="6" height="4" rx="1" />
                </svg>
            ),
            "align-stretch": (
                <svg
                    width="20"
                    height="20"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke={color}
                    strokeWidth="2"
                >
                    <line x1="4" y1="4" x2="4" y2="20" />
                    <line x1="20" y1="4" x2="20" y2="20" />
                    <rect x="6" y="6" width="12" height="4" rx="1" />
                    <rect x="6" y="14" width="12" height="4" rx="1" />
                </svg>
            ),
            "space-between": (
                <svg
                    width="20"
                    height="20"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke={color}
                    strokeWidth="2"
                >
                    <line x1="4" y1="4" x2="4" y2="20" />
                    <line x1="20" y1="4" x2="20" y2="20" />
                    <rect x="5" y="8" width="4" height="8" rx="1" />
                    <rect x="15" y="8" width="4" height="8" rx="1" />
                </svg>
            ),
            "space-around": (
                <svg
                    width="20"
                    height="20"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke={color}
                    strokeWidth="2"
                >
                    <rect x="6" y="8" width="4" height="8" rx="1" />
                    <rect x="14" y="8" width="4" height="8" rx="1" />
                </svg>
            ),
        };
        return icons[type] || null;
    };

    return (
        <div className="space-y-5">
            {/* Number of Columns */}
            <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                    {__("Number of Columns")}
                </label>
                <div className="grid grid-cols-6 gap-2">
                    {[1, 2, 3, 4, 5, 6].map((count) => (
                        <button
                            key={count}
                            onClick={() => handleColumnsChange(count)}
                            className={`
                                px-3 py-2 text-sm font-medium rounded-lg border-2 transition-all
                                ${
                                    columns === count
                                        ? "border-primary bg-primary/10 text-primary"
                                        : "border-gray-200 bg-white text-gray-700 hover:border-gray-300"
                                }
                            `}
                        >
                            {count}
                        </button>
                    ))}
                </div>
            </div>

            {/* Gap Between Columns */}
            <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                    {__("Gap Between Columns")}
                </label>
                <select
                    value={gap}
                    onChange={(e) => handleGapChange(e.target.value)}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                >
                    <option value="0px">{__("None")}</option>
                    <option value="8px">{__("Small")} (8px)</option>
                    <option value="12px">{__("Medium")} (12px)</option>
                    <option value="16px">{__("Normal")} (16px)</option>
                    <option value="20px">{__("Large")} (20px)</option>
                    <option value="24px">{__("Extra Large")} (24px)</option>
                    <option value="32px">{__("2X Large")} (32px)</option>
                </select>
            </div>

            {/* Vertical Alignment */}
            <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                    {__("Vertical Alignment")}
                </label>
                <div className="grid grid-cols-4 gap-2">
                    {verticalAlignOptions.map((option) => (
                        <button
                            key={option.value}
                            onClick={() =>
                                handleVerticalAlignChange(option.value)
                            }
                            title={option.label}
                            className={`
                                flex flex-col items-center justify-center p-2 rounded-lg border-2 transition-all
                                ${
                                    verticalAlign === option.value
                                        ? "border-primary bg-primary/10 text-primary"
                                        : "border-gray-200 bg-white text-gray-500 hover:border-gray-300"
                                }
                            `}
                        >
                            <AlignIcon
                                type={option.icon}
                                isActive={verticalAlign === option.value}
                            />
                            <span className="text-xs mt-1">{option.label}</span>
                        </button>
                    ))}
                </div>
            </div>

            {/* Horizontal Alignment */}
            <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                    {__("Horizontal Alignment")}
                </label>
                <div className="grid grid-cols-3 gap-2">
                    {horizontalAlignOptions.map((option) => (
                        <button
                            key={option.value}
                            onClick={() =>
                                handleHorizontalAlignChange(option.value)
                            }
                            title={option.label}
                            className={`
                                flex flex-col items-center justify-center p-2 rounded-lg border-2 transition-all
                                ${
                                    horizontalAlign === option.value
                                        ? "border-primary bg-primary/10 text-primary"
                                        : "border-gray-200 bg-white text-gray-500 hover:border-gray-300"
                                }
                            `}
                        >
                            <AlignIcon
                                type={option.icon}
                                isActive={horizontalAlign === option.value}
                            />
                            <span className="text-xs mt-1">{option.label}</span>
                        </button>
                    ))}
                </div>
            </div>

            {/* Stack on Mobile */}
            <div className="flex items-center justify-between py-2">
                <div>
                    <label className="block text-sm font-medium text-gray-700">
                        {__("Stack on Mobile")}
                    </label>
                    <p className="text-xs text-gray-500 mt-0.5">
                        {__("Columns will stack vertically on smaller screens")}
                    </p>
                </div>
                <button
                    onClick={() => handleStackOnMobileChange(!stackOnMobile)}
                    className={`
                        relative inline-flex h-6 w-11 items-center rounded-full transition-colors
                        ${stackOnMobile ? "bg-primary" : "bg-gray-300"}
                    `}
                >
                    <span
                        className={`
                            inline-block h-4 w-4 transform rounded-full bg-white transition-transform
                            ${stackOnMobile ? "translate-x-6" : "translate-x-1"}
                        `}
                    />
                </button>
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
                        <p className="font-medium text-gray-700">
                            {__("Tip")}:
                        </p>
                        <p className="mt-1">
                            {__(
                                "Drag blocks from the sidebar into the column areas to build your layout. Each column can contain multiple blocks."
                            )}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default ColumnsEditor;
