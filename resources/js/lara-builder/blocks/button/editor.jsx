/**
 * Button Block - Property Editor
 */

import { __ } from "@lara-builder/i18n";

const ButtonBlockEditor = ({ props, onUpdate }) => {
    const handleChange = (field, value) => {
        onUpdate({ ...props, [field]: value });
    };

    return (
        <div className="space-y-4">
            <Section title={__("Link")}>
                <Label>{__("URL")}</Label>
                <input
                    type="url"
                    value={props.link || ""}
                    onChange={(e) => handleChange("link", e.target.value)}
                    placeholder="https://..."
                    className="form-control"
                />

                {props.link && (
                    <>
                        <div className="mt-3">
                            <Label>{__("Open In")}</Label>
                            <select
                                value={props.target || "_self"}
                                onChange={(e) =>
                                    handleChange("target", e.target.value)
                                }
                                className="form-control"
                            >
                                <option value="_self">
                                    {__("Same Window")}
                                </option>
                                <option value="_blank">{__("New Tab")}</option>
                            </select>
                        </div>

                        <div className="mt-3">
                            <Label>{__("Rel Attribute")}</Label>
                            <div className="space-y-2">
                                <label className="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        checked={props.nofollow || false}
                                        onChange={(e) =>
                                            handleChange(
                                                "nofollow",
                                                e.target.checked
                                            )
                                        }
                                        className="rounded border-gray-300"
                                    />
                                    nofollow
                                </label>
                                <label className="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        checked={props.sponsored || false}
                                        onChange={(e) =>
                                            handleChange(
                                                "sponsored",
                                                e.target.checked
                                            )
                                        }
                                        className="rounded border-gray-300"
                                    />
                                    sponsored
                                </label>
                            </div>
                            <p className="mt-1 text-xs text-gray-400">
                                {__(
                                    "noopener & noreferrer are auto-added for new tab links"
                                )}
                            </p>
                        </div>
                    </>
                )}
            </Section>

            <Section title={__("Colors")}>
                <Label>{__("Background Color")}</Label>
                <ColorPicker
                    value={props.backgroundColor || "#635bff"}
                    onChange={(value) => handleChange("backgroundColor", value)}
                />

                <div className="mt-3">
                    <Label>{__("Text Color")}</Label>
                    <ColorPicker
                        value={props.textColor || "#ffffff"}
                        onChange={(value) => handleChange("textColor", value)}
                    />
                </div>
            </Section>

            <Section title={__("Style")}>
                <Label>{__("Border Radius")}</Label>
                <select
                    value={props.borderRadius || "6px"}
                    onChange={(e) =>
                        handleChange("borderRadius", e.target.value)
                    }
                    className="form-control"
                >
                    <option value="0">{__("None")}</option>
                    <option value="4px">{__("Small")} (4px)</option>
                    <option value="6px">{__("Medium")} (6px)</option>
                    <option value="8px">{__("Large")} (8px)</option>
                    <option value="12px">{__("X-Large")} (12px)</option>
                    <option value="9999px">{__("Pill")}</option>
                </select>

                <div className="mt-3">
                    <Label>{__("Padding")}</Label>
                    <select
                        value={props.padding || "12px 24px"}
                        onChange={(e) =>
                            handleChange("padding", e.target.value)
                        }
                        className="form-control"
                    >
                        <option value="8px 16px">{__("Small")}</option>
                        <option value="12px 24px">{__("Medium")}</option>
                        <option value="16px 32px">{__("Large")}</option>
                        <option value="20px 40px">{__("X-Large")}</option>
                    </select>
                </div>
            </Section>
        </div>
    );
};

// Reusable Section Component
const Section = ({ title, children }) => (
    <div className="pb-4 border-b border-gray-200 dark:border-gray-700 last:border-b-0 last:pb-0">
        <h4 className="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3">
            {title}
        </h4>
        {children}
    </div>
);

// Reusable Label Component
const Label = ({ children }) => (
    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
        {children}
    </label>
);

// Reusable Color Picker Component
const ColorPicker = ({ value, onChange }) => (
    <div className="flex gap-2">
        <input
            type="color"
            value={value}
            onChange={(e) => onChange(e.target.value)}
            className="w-12 h-9 rounded border border-gray-300 cursor-pointer"
        />
        <input
            type="text"
            value={value}
            onChange={(e) => onChange(e.target.value)}
            className="form-control flex-1 font-mono text-sm"
        />
    </div>
);

export default ButtonBlockEditor;
