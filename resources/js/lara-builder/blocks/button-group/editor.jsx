/**
 * Button Group Block - Properties Panel Editor
 *
 * Sidebar controls for configuring the button group block.
 * Manages an array of buttons plus global styling options.
 */

import { useState } from "react";
import { __ } from "@lara-builder/i18n";

const ButtonGroupEditor = ({ props, onUpdate }) => {
    const {
        buttons = [],
        alignment = "center",
        gap = "12px",
        size = "md",
        stackOnMobile = true,
        borderRadius = "8px",
    } = props;

    const [expandedIndex, setExpandedIndex] = useState(0);

    const handleChange = (key, value) => {
        onUpdate({ ...props, [key]: value });
    };

    const handleButtonChange = (index, key, value) => {
        const updatedButtons = [...buttons];
        updatedButtons[index] = { ...updatedButtons[index], [key]: value };
        onUpdate({ ...props, buttons: updatedButtons });
    };

    const handleAddButton = () => {
        const newButton = {
            text: "Button",
            link: "#",
            variant: "outline",
            backgroundColor: "#3b82f6",
            textColor: "#3b82f6",
            icon: "",
            iconPosition: "left",
        };
        const updated = [...buttons, newButton];
        onUpdate({ ...props, buttons: updated });
        setExpandedIndex(updated.length - 1);
    };

    const handleRemoveButton = (index) => {
        if (buttons.length <= 1) return;
        const updated = buttons.filter((_, i) => i !== index);
        onUpdate({ ...props, buttons: updated });
        if (expandedIndex >= updated.length) {
            setExpandedIndex(updated.length - 1);
        }
    };

    const handleMoveButton = (index, direction) => {
        const newIndex = index + direction;
        if (newIndex < 0 || newIndex >= buttons.length) return;
        const updated = [...buttons];
        [updated[index], updated[newIndex]] = [updated[newIndex], updated[index]];
        onUpdate({ ...props, buttons: updated });
        setExpandedIndex(newIndex);
    };

    return (
        <div className="space-y-5">
            {/* Buttons List */}
            <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                    {__("Buttons")}
                </label>
                <div className="space-y-3">
                    {buttons.map((btn, index) => (
                        <div key={index} className="border border-gray-200 rounded-lg overflow-hidden">
                            {/* Button Header */}
                            <button
                                onClick={() => setExpandedIndex(expandedIndex === index ? -1 : index)}
                                className="w-full flex items-center justify-between px-3 py-2 bg-gray-50 hover:bg-gray-100 transition-colors"
                            >
                                <span className="text-sm font-medium text-gray-700 truncate">
                                    {btn.text || __("Button")} {index + 1}
                                </span>
                                <div className="flex items-center gap-1">
                                    {index > 0 && (
                                        <span
                                            onClick={(e) => { e.stopPropagation(); handleMoveButton(index, -1); }}
                                            className="p-1 text-gray-400 hover:text-gray-600 cursor-pointer"
                                            title={__("Move up")}
                                        >
                                            <iconify-icon icon="lucide:chevron-up" width="14" height="14" aria-hidden="true" />
                                        </span>
                                    )}
                                    {index < buttons.length - 1 && (
                                        <span
                                            onClick={(e) => { e.stopPropagation(); handleMoveButton(index, 1); }}
                                            className="p-1 text-gray-400 hover:text-gray-600 cursor-pointer"
                                            title={__("Move down")}
                                        >
                                            <iconify-icon icon="lucide:chevron-down" width="14" height="14" aria-hidden="true" />
                                        </span>
                                    )}
                                    {buttons.length > 1 && (
                                        <span
                                            onClick={(e) => { e.stopPropagation(); handleRemoveButton(index); }}
                                            className="p-1 text-red-400 hover:text-red-600 cursor-pointer"
                                            title={__("Remove")}
                                        >
                                            <iconify-icon icon="lucide:trash-2" width="14" height="14" aria-hidden="true" />
                                        </span>
                                    )}
                                    <iconify-icon
                                        icon={expandedIndex === index ? "lucide:chevron-up" : "lucide:chevron-down"}
                                        width="16"
                                        height="16"
                                        className="text-gray-400"
                                        aria-hidden="true"
                                    />
                                </div>
                            </button>

                            {/* Button Details */}
                            {expandedIndex === index && (
                                <div className="p-3 space-y-3">
                                    {/* Text */}
                                    <div>
                                        <label className="block text-xs font-medium text-gray-600 mb-1">
                                            {__("Text")}
                                        </label>
                                        <input
                                            type="text"
                                            value={btn.text}
                                            onChange={(e) => handleButtonChange(index, "text", e.target.value)}
                                            className="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-transparent"
                                        />
                                    </div>

                                    {/* Link */}
                                    <div>
                                        <label className="block text-xs font-medium text-gray-600 mb-1">
                                            {__("Link URL")}
                                        </label>
                                        <input
                                            type="text"
                                            value={btn.link}
                                            onChange={(e) => handleButtonChange(index, "link", e.target.value)}
                                            placeholder="https://"
                                            className="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-transparent"
                                        />
                                    </div>

                                    {/* Variant */}
                                    <div>
                                        <label className="block text-xs font-medium text-gray-600 mb-1">
                                            {__("Style")}
                                        </label>
                                        <div className="grid grid-cols-3 gap-1">
                                            {[
                                                { value: "solid", label: __("Solid") },
                                                { value: "outline", label: __("Outline") },
                                                { value: "ghost", label: __("Ghost") },
                                            ].map((v) => (
                                                <button
                                                    key={v.value}
                                                    onClick={() => handleButtonChange(index, "variant", v.value)}
                                                    className={`
                                                        px-2 py-1.5 text-xs font-medium rounded-lg border-2 transition-all
                                                        ${btn.variant === v.value
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

                                    {/* Colors */}
                                    <div className="grid grid-cols-2 gap-3">
                                        <div>
                                            <label className="block text-xs font-medium text-gray-600 mb-1">
                                                {__("Background")}
                                            </label>
                                            <div className="flex gap-1">
                                                <input
                                                    type="color"
                                                    value={btn.backgroundColor || "#3b82f6"}
                                                    onChange={(e) => handleButtonChange(index, "backgroundColor", e.target.value)}
                                                    className="h-8 w-10 border border-gray-300 rounded cursor-pointer"
                                                />
                                                <input
                                                    type="text"
                                                    value={btn.backgroundColor || ""}
                                                    onChange={(e) => handleButtonChange(index, "backgroundColor", e.target.value)}
                                                    className="flex-1 px-2 py-1 border border-gray-300 rounded-lg text-xs"
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
                                                    value={btn.textColor || "#ffffff"}
                                                    onChange={(e) => handleButtonChange(index, "textColor", e.target.value)}
                                                    className="h-8 w-10 border border-gray-300 rounded cursor-pointer"
                                                />
                                                <input
                                                    type="text"
                                                    value={btn.textColor || ""}
                                                    onChange={(e) => handleButtonChange(index, "textColor", e.target.value)}
                                                    className="flex-1 px-2 py-1 border border-gray-300 rounded-lg text-xs"
                                                />
                                            </div>
                                        </div>
                                    </div>

                                    {/* Icon */}
                                    <div>
                                        <label className="block text-xs font-medium text-gray-600 mb-1">
                                            {__("Icon (optional)")}
                                        </label>
                                        <input
                                            type="text"
                                            value={btn.icon || ""}
                                            onChange={(e) => handleButtonChange(index, "icon", e.target.value)}
                                            placeholder="lucide:arrow-right"
                                            className="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-transparent"
                                        />
                                    </div>

                                    {/* Icon Position */}
                                    {btn.icon && (
                                        <div>
                                            <label className="block text-xs font-medium text-gray-600 mb-1">
                                                {__("Icon Position")}
                                            </label>
                                            <div className="grid grid-cols-2 gap-1">
                                                {[
                                                    { value: "left", label: __("Left") },
                                                    { value: "right", label: __("Right") },
                                                ].map((p) => (
                                                    <button
                                                        key={p.value}
                                                        onClick={() => handleButtonChange(index, "iconPosition", p.value)}
                                                        className={`
                                                            px-2 py-1.5 text-xs font-medium rounded-lg border-2 transition-all
                                                            ${(btn.iconPosition || "left") === p.value
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
                                    )}
                                </div>
                            )}
                        </div>
                    ))}
                </div>

                {/* Add Button */}
                <button
                    onClick={handleAddButton}
                    className="mt-3 w-full px-3 py-2 border-2 border-dashed border-gray-300 rounded-lg text-sm text-gray-500 hover:border-primary hover:text-primary transition-colors"
                >
                    + {__("Add Button")}
                </button>
            </div>

            {/* Global Settings */}
            <div className="border-t border-gray-200 pt-5">
                <h4 className="text-sm font-semibold text-gray-700 mb-4">
                    {__("Group Settings")}
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
                                onClick={() => handleChange("alignment", a)}
                                className={`
                                    px-3 py-2 text-sm font-medium rounded-lg border-2 transition-all capitalize
                                    ${alignment === a
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

                {/* Gap */}
                <div className="mb-4">
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                        {__("Gap")}
                    </label>
                    <div className="grid grid-cols-4 gap-2">
                        {[
                            { value: "8px", label: "8" },
                            { value: "12px", label: "12" },
                            { value: "16px", label: "16" },
                            { value: "24px", label: "24" },
                        ].map((g) => (
                            <button
                                key={g.value}
                                onClick={() => handleChange("gap", g.value)}
                                className={`
                                    px-2 py-2 text-xs font-medium rounded-lg border-2 transition-all
                                    ${gap === g.value
                                        ? "border-primary bg-primary/10 text-primary"
                                        : "border-gray-200 text-gray-700 hover:border-gray-300"
                                    }
                                `}
                            >
                                {g.label}px
                            </button>
                        ))}
                    </div>
                </div>

                {/* Size */}
                <div className="mb-4">
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                        {__("Button Size")}
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

                {/* Border Radius */}
                <div className="mb-4">
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                        {__("Border Radius")}
                    </label>
                    <div className="grid grid-cols-4 gap-2">
                        {[
                            { value: "4px", label: "4" },
                            { value: "8px", label: "8" },
                            { value: "12px", label: "12" },
                            { value: "9999px", label: __("Pill") },
                        ].map((r) => (
                            <button
                                key={r.value}
                                onClick={() => handleChange("borderRadius", r.value)}
                                className={`
                                    px-2 py-2 text-xs font-medium rounded-lg border-2 transition-all
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

                {/* Stack on Mobile */}
                <div>
                    <label className="flex items-center gap-2 cursor-pointer">
                        <input
                            type="checkbox"
                            checked={stackOnMobile}
                            onChange={(e) => handleChange("stackOnMobile", e.target.checked)}
                            className="rounded border-gray-300 text-primary focus:ring-primary"
                        />
                        <span className="text-sm font-medium text-gray-700">
                            {__("Stack on mobile")}
                        </span>
                    </label>
                    <p className="mt-1 text-xs text-gray-500 ml-6">
                        {__("Buttons stack vertically on screens below 640px")}
                    </p>
                </div>
            </div>
        </div>
    );
};

export default ButtonGroupEditor;
