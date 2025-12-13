import { blockRegistry } from "../registry/BlockRegistry";
import { __ } from "@lara-builder/i18n";
import { AutoEditor } from "@lara-builder/factory";
import LayoutStylesSection from "./LayoutStylesSection";

/**
 * PropertiesPanel - Renders property editors for blocks
 *
 * This component orchestrates the rendering of property editors for blocks.
 * It delegates to:
 * 1. Block's custom `editor` component if provided
 * 2. Auto-generated editor from block's `fields` array
 * 3. Canvas settings when no block is selected
 *
 * Global styles (LayoutStylesSection) are appended to all blocks.
 */
const PropertiesPanel = ({
    selectedBlock,
    onUpdate,
    onImageUpload,
    onVideoUpload,
    canvasSettings,
    onCanvasSettingsUpdate,
}) => {
    // Handle canvas layout styles update
    const handleCanvasLayoutStylesUpdate = (newLayoutStyles) => {
        onCanvasSettingsUpdate({
            ...canvasSettings,
            layoutStyles: newLayoutStyles,
        });
    };

    // Show canvas settings when no block is selected
    if (!selectedBlock) {
        return (
            <div className="h-full overflow-y-auto px-1">
                <div className="mb-4 pb-3 border-b border-gray-200">
                    <span className="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        {__("Email Settings")}
                    </span>
                </div>

                {/* Width */}
                <div className="mb-4">
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                        {__("Email Width")}
                    </label>
                    <select
                        value={canvasSettings?.width || "700px"}
                        onChange={(e) =>
                            onCanvasSettingsUpdate({
                                ...canvasSettings,
                                width: e.target.value,
                            })
                        }
                        className="form-control"
                    >
                        <option value="500px">500px ({__("Narrow")})</option>
                        <option value="600px">600px ({__("Standard")})</option>
                        <option value="700px">700px ({__("Wide")})</option>
                        <option value="800px">800px ({__("Extra Wide")})</option>
                    </select>
                </div>

                {/* Content Layout Styles - Same as blocks */}
                <LayoutStylesSection
                    layoutStyles={canvasSettings?.layoutStyles || {}}
                    onUpdate={handleCanvasLayoutStylesUpdate}
                    onImageUpload={onImageUpload}
                    defaultCollapsed={false}
                />

                <div className="mt-6 pt-4 border-t border-gray-200">
                    <p className="text-xs text-gray-400 text-center">
                        {__("Click the block to edit")}
                    </p>
                </div>
            </div>
        );
    }

    const blockConfig = blockRegistry.get(selectedBlock.type);
    const props = selectedBlock.props || {};

    // Handle layout styles update for selected block
    const handleLayoutStylesUpdate = (newLayoutStyles) => {
        onUpdate(selectedBlock.id, { ...props, layoutStyles: newLayoutStyles });
    };

    // Get label for the block
    const blockLabel = blockConfig?.label || selectedBlock.type;

    // Check for custom property editor from registry
    const CustomPropertyEditor = blockConfig?.editor;
    const blockFields = blockConfig?.fields;

    // If block provides a custom property editor, use it
    if (CustomPropertyEditor) {
        return (
            <div className="h-full overflow-y-auto px-1">
                <div className="mb-2 pb-3 border-b border-gray-200">
                    <span className="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        {__(blockLabel)}
                    </span>
                </div>

                <CustomPropertyEditor
                    props={props}
                    onUpdate={(newProps) =>
                        onUpdate(selectedBlock.id, newProps)
                    }
                    onImageUpload={onImageUpload}
                    onVideoUpload={onVideoUpload}
                />

                {/* Layout Styles Section - Available for all blocks */}
                <LayoutStylesSection
                    layoutStyles={props.layoutStyles || {}}
                    onUpdate={handleLayoutStylesUpdate}
                    onImageUpload={onImageUpload}
                    defaultCollapsed={true}
                    customCSS={props.customCSS || ""}
                    customClass={props.customClass || ""}
                    onCustomCSSChange={(newCSS) =>
                        onUpdate(selectedBlock.id, {
                            ...props,
                            customCSS: newCSS,
                        })
                    }
                    onCustomClassChange={(newClass) =>
                        onUpdate(selectedBlock.id, {
                            ...props,
                            customClass: newClass,
                        })
                    }
                />
            </div>
        );
    }

    // If block has fields array, use auto-generated editor
    if (blockFields && blockFields.length > 0) {
        return (
            <div className="h-full overflow-y-auto px-1">
                <div className="mb-2 pb-3 border-b border-gray-200">
                    <span className="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        {__(blockLabel)}
                    </span>
                </div>

                <AutoEditor
                    fields={blockFields}
                    blockProps={props}
                    onUpdate={(newProps) =>
                        onUpdate(selectedBlock.id, newProps)
                    }
                />

                {/* Layout Styles Section - Available for all blocks */}
                <LayoutStylesSection
                    layoutStyles={props.layoutStyles || {}}
                    onUpdate={handleLayoutStylesUpdate}
                    onImageUpload={onImageUpload}
                    defaultCollapsed={true}
                    customCSS={props.customCSS || ""}
                    customClass={props.customClass || ""}
                    onCustomCSSChange={(newCSS) =>
                        onUpdate(selectedBlock.id, {
                            ...props,
                            customCSS: newCSS,
                        })
                    }
                    onCustomClassChange={(newClass) =>
                        onUpdate(selectedBlock.id, {
                            ...props,
                            customClass: newClass,
                        })
                    }
                />
            </div>
        );
    }

    // Fallback: No fields or editor defined - show message with layout styles only
    return (
        <div className="h-full overflow-y-auto px-1">
            <div className="mb-2 pb-3 border-b border-gray-200">
                <span className="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    {__(blockLabel)}
                </span>
            </div>

            <p className="text-gray-500 text-sm mb-4">
                {__("Click the block to edit it directly.")}
            </p>

            {/* Layout Styles Section - Available for all blocks */}
            <LayoutStylesSection
                layoutStyles={props.layoutStyles || {}}
                onUpdate={handleLayoutStylesUpdate}
                onImageUpload={onImageUpload}
                defaultCollapsed={true}
                customCSS={props.customCSS || ""}
                customClass={props.customClass || ""}
                onCustomCSSChange={(newCSS) =>
                    onUpdate(selectedBlock.id, { ...props, customCSS: newCSS })
                }
                onCustomClassChange={(newClass) =>
                    onUpdate(selectedBlock.id, {
                        ...props,
                        customClass: newClass,
                    })
                }
            />
        </div>
    );
};

export default PropertiesPanel;
