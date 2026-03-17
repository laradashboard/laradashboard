/**
 * Alert Banner Block - Properties Panel Editor
 *
 * Sidebar controls for configuring the alert banner block.
 */

import { __ } from "@lara-builder/i18n";

const AlertBannerEditor = ({ props, onUpdate }) => {
    const {
        text = "AI-powered module marketplace is live",
        badgeText = "NEW",
        linkText = "",
        linkUrl = "#",
        backgroundColor = "#1e1b4b",
        textColor = "#e0e7ff",
        badgeColor = "#6366f1",
        badgeTextColor = "#ffffff",
        align = "center",
        padding = "12px 24px",
    } = props;

    const handleChange = (key, value) => {
        onUpdate({ ...props, [key]: value });
    };

    return (
        <div className="space-y-5">
            {/* Text */}
            <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                    {__("Banner Text")}
                </label>
                <input
                    type="text"
                    value={text}
                    onChange={(e) => handleChange("text", e.target.value)}
                    placeholder={__("Announcement message")}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-transparent"
                />
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
                    placeholder={__("e.g. NEW, BETA")}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-transparent"
                />
                <p className="mt-1 text-xs text-gray-500">
                    {__("Leave empty to hide the badge")}
                </p>
            </div>

            {/* Link Text */}
            <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                    {__("Link Text")}
                </label>
                <input
                    type="text"
                    value={linkText}
                    onChange={(e) => handleChange("linkText", e.target.value)}
                    placeholder={__("e.g. Learn more")}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-transparent"
                />
            </div>

            {/* Link URL */}
            <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                    {__("Link URL")}
                </label>
                <input
                    type="text"
                    value={linkUrl}
                    onChange={(e) => handleChange("linkUrl", e.target.value)}
                    placeholder="https://"
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-transparent"
                />
            </div>

            {/* Colors */}
            <div className="border-t border-gray-200 pt-5">
                <h4 className="text-sm font-semibold text-gray-700 mb-4">
                    {__("Colors")}
                </h4>

                {/* Background Color */}
                <div className="mb-4">
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                        {__("Background")}
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
                        />
                    </div>
                </div>

                {/* Text Color */}
                <div className="mb-4">
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
                </div>

                {/* Badge Color */}
                <div className="mb-4">
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                        {__("Badge Color")}
                    </label>
                    <div className="flex gap-2">
                        <input
                            type="color"
                            value={badgeColor}
                            onChange={(e) => handleChange("badgeColor", e.target.value)}
                            className="h-10 w-12 border border-gray-300 rounded cursor-pointer"
                        />
                        <input
                            type="text"
                            value={badgeColor}
                            onChange={(e) => handleChange("badgeColor", e.target.value)}
                            className="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm"
                        />
                    </div>
                </div>

                {/* Badge Text Color */}
                <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                        {__("Badge Text Color")}
                    </label>
                    <div className="flex gap-2">
                        <input
                            type="color"
                            value={badgeTextColor}
                            onChange={(e) => handleChange("badgeTextColor", e.target.value)}
                            className="h-10 w-12 border border-gray-300 rounded cursor-pointer"
                        />
                        <input
                            type="text"
                            value={badgeTextColor}
                            onChange={(e) => handleChange("badgeTextColor", e.target.value)}
                            className="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm"
                        />
                    </div>
                </div>
            </div>

            {/* Layout */}
            <div className="border-t border-gray-200 pt-5">
                <h4 className="text-sm font-semibold text-gray-700 mb-4">
                    {__("Layout")}
                </h4>

                {/* Alignment */}
                <div className="mb-4">
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

                {/* Padding */}
                <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                        {__("Padding")}
                    </label>
                    <div className="grid grid-cols-3 gap-2">
                        {[
                            { value: "8px 16px", label: __("Small") },
                            { value: "12px 24px", label: __("Medium") },
                            { value: "16px 32px", label: __("Large") },
                        ].map((p) => (
                            <button
                                key={p.value}
                                onClick={() => handleChange("padding", p.value)}
                                className={`
                                    px-3 py-2 text-sm font-medium rounded-lg border-2 transition-all
                                    ${padding === p.value
                                        ? "border-primary bg-primary/10 text-primary"
                                        : "border-gray-200 text-gray-700 hover:border-gray-300"
                                    }
                                `}
                            >
                                {p.label}
                            </button>
                        ))}
                    </div>
                </div>
            </div>

            {/* Preview */}
            <div className="border-t border-gray-200 pt-4">
                <label className="block text-sm font-medium text-gray-700 mb-2">
                    {__("Preview")}
                </label>
                <div
                    style={{
                        display: "flex",
                        alignItems: "center",
                        justifyContent: align === "left" ? "flex-start" : align === "right" ? "flex-end" : "center",
                        gap: "8px",
                        backgroundColor,
                        color: textColor,
                        padding: "8px 16px",
                        borderRadius: "8px",
                        fontSize: "12px",
                        fontWeight: "500",
                    }}
                >
                    {badgeText && (
                        <span
                            style={{
                                backgroundColor: badgeColor,
                                color: badgeTextColor,
                                fontSize: "9px",
                                fontWeight: "700",
                                padding: "1px 6px",
                                borderRadius: "9999px",
                                letterSpacing: "0.05em",
                                textTransform: "uppercase",
                            }}
                        >
                            {badgeText}
                        </span>
                    )}
                    <span>{text}</span>
                    {(linkText || linkUrl !== "#") && (
                        <iconify-icon icon="lucide:arrow-right" width="12" height="12" style={{ opacity: 0.8 }} aria-hidden="true" />
                    )}
                </div>
            </div>
        </div>
    );
};

export default AlertBannerEditor;
