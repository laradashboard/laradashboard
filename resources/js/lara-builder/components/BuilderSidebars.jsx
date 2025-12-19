/**
 * BuilderSidebars - Sidebar components for LaraBuilder
 *
 * Contains left sidebar (block palette), right sidebar (properties panel),
 * and mobile drawer variants.
 */

import { __ } from "@lara-builder/i18n";
import BlockPanel from "./BlockPanel";

/**
 * Left Sidebar - Block Palette (Desktop)
 */
export function LeftSidebar({ collapsed, setCollapsed, onAddBlock, context }) {
    if (collapsed) {
        return (
            <div className="flex flex-col items-center py-4">
                <button
                    onClick={() => setCollapsed(false)}
                    className="p-2 rounded-md hover:bg-gray-100 text-gray-600"
                    title={__("Show Blocks")}
                >
                    <iconify-icon
                        icon="mdi:chevron-right"
                        width="20"
                        height="20"
                    ></iconify-icon>
                </button>
                <button
                    onClick={() => setCollapsed(false)}
                    className="mt-2 p-2 rounded-md hover:bg-primary/10 text-primary"
                    title={__("Show Blocks")}
                >
                    <iconify-icon
                        icon="mdi:plus-box-multiple"
                        width="20"
                        height="20"
                    ></iconify-icon>
                </button>
            </div>
        );
    }

    return (
        <div className="flex flex-col h-full p-4">
            <div className="flex items-center justify-between mb-4">
                <h3 className="text-sm font-semibold text-gray-900">{__("Blocks")}</h3>
                <button
                    onClick={() => setCollapsed(true)}
                    className="p-1 rounded-md hover:bg-gray-100 text-gray-500"
                    title={__("Hide Blocks")}
                >
                    <iconify-icon
                        icon="mdi:chevron-left"
                        width="18"
                        height="18"
                    ></iconify-icon>
                </button>
            </div>
            <BlockPanel onAddBlock={onAddBlock} context={context} />
        </div>
    );
}

/**
 * Right Sidebar - Properties Panel (Desktop)
 */
export function RightSidebar({
    collapsed,
    setCollapsed,
    PropertiesPanel,
    propertiesPanelProps,
}) {
    if (collapsed) {
        return (
            <div className="flex flex-col items-center py-4">
                <button
                    onClick={() => setCollapsed(false)}
                    className="p-2 rounded-md hover:bg-gray-100 text-gray-600"
                    title={__("Show Properties")}
                >
                    <iconify-icon
                        icon="mdi:chevron-left"
                        width="20"
                        height="20"
                    ></iconify-icon>
                </button>
                <button
                    onClick={() => setCollapsed(false)}
                    className="mt-2 p-2 rounded-md hover:bg-gray-50 text-gray-600"
                    title={__("Show Properties")}
                >
                    <iconify-icon icon="mdi:cog" width="20" height="20"></iconify-icon>
                </button>
            </div>
        );
    }

    return (
        <div className="flex flex-col h-full pt-4 pr-4 pb-4 pl-2 overflow-hidden">
            <div className="flex items-center justify-between mb-4 pl-2">
                <h3 className="text-sm font-semibold text-gray-900">
                    {__("Properties")}
                </h3>
                <button
                    onClick={() => setCollapsed(true)}
                    className="p-1 rounded-md hover:bg-gray-100 text-gray-500"
                    title={__("Hide Properties")}
                >
                    <iconify-icon
                        icon="mdi:chevron-right"
                        width="18"
                        height="18"
                    ></iconify-icon>
                </button>
            </div>
            <div className="flex-1 overflow-y-auto pl-2">
                <PropertiesPanel {...propertiesPanelProps} />
            </div>
        </div>
    );
}

/**
 * Left Drawer - Block Palette (Mobile)
 */
export function LeftDrawer({ isOpen, onClose, onAddBlock, context }) {
    if (!isOpen) return null;

    return (
        <div className="lg:hidden fixed inset-0 z-50">
            <div
                className="absolute inset-0 bg-black/50"
                onClick={onClose}
            ></div>
            <div className="absolute left-0 top-0 bottom-0 w-72 bg-white shadow-xl flex flex-col animate-slide-in-left">
                <div className="flex items-center justify-between p-4 border-b border-gray-200">
                    <h3 className="text-sm font-semibold text-gray-900">
                        {__("Blocks")}
                    </h3>
                    <button
                        onClick={onClose}
                        className="p-1.5 rounded-md hover:bg-gray-100 text-gray-500"
                    >
                        <iconify-icon icon="mdi:close" width="20" height="20"></iconify-icon>
                    </button>
                </div>
                <div className="flex-1 p-4 overflow-hidden">
                    <BlockPanel
                        onAddBlock={(type) => {
                            onAddBlock(type);
                            onClose();
                        }}
                        context={context}
                    />
                </div>
            </div>
        </div>
    );
}

/**
 * Right Drawer - Properties Panel (Mobile)
 */
export function RightDrawer({
    isOpen,
    onClose,
    context,
    isEmailContext,
    isPostContext,
    // Email state
    templateName,
    setTemplateName,
    templateSubject,
    setTemplateSubject,
    // Post state
    title,
    setTitle,
    postTypeModel,
    // Properties panel
    PropertiesPanel,
    propertiesPanelProps,
}) {
    if (!isOpen) return null;

    return (
        <div className="lg:hidden fixed inset-0 z-50">
            <div
                className="absolute inset-0 bg-black/50"
                onClick={onClose}
            ></div>
            <div className="absolute right-0 top-0 bottom-0 w-80 bg-white shadow-xl flex flex-col animate-slide-in-right">
                <div className="flex items-center justify-between p-4 border-b border-gray-200">
                    <h3 className="text-sm font-semibold text-gray-900">
                        {__("Properties")}
                    </h3>
                    <button
                        onClick={onClose}
                        className="p-1.5 rounded-md hover:bg-gray-100 text-gray-500"
                    >
                        <iconify-icon icon="mdi:close" width="20" height="20"></iconify-icon>
                    </button>
                </div>
                <div className="flex-1 px-4 py-4 overflow-y-auto">
                    {/* Mobile-only template inputs for email context */}
                    {isEmailContext && (
                        <div className="md:hidden mb-4 pb-4 border-b border-gray-200">
                            <h4 className="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
                                {__("Template Details")}
                            </h4>
                            <div className="space-y-3">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">
                                        {__("Name")}
                                    </label>
                                    <input
                                        type="text"
                                        value={templateName}
                                        onChange={(e) => setTemplateName(e.target.value)}
                                        placeholder={__("Template name...")}
                                        className="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-primary focus:border-primary"
                                    />
                                </div>
                                {context === "email" && (
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-1">
                                            {__("Subject")}
                                        </label>
                                        <input
                                            type="text"
                                            value={templateSubject}
                                            onChange={(e) =>
                                                setTemplateSubject(e.target.value)
                                            }
                                            placeholder={__("Email subject...")}
                                            className="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-primary focus:border-primary"
                                        />
                                    </div>
                                )}
                            </div>
                        </div>
                    )}
                    {/* Mobile-only title input for post context */}
                    {isPostContext && (
                        <div className="md:hidden mb-4 pb-4 border-b border-gray-200">
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                {__("Title")}
                            </label>
                            <input
                                type="text"
                                value={title}
                                onChange={(e) => setTitle(e.target.value)}
                                placeholder={__(":type title...").replace(
                                    ":type",
                                    postTypeModel?.label_singular || "Post"
                                )}
                                className="form-control w-full"
                            />
                        </div>
                    )}
                    <PropertiesPanel {...propertiesPanelProps} />
                </div>
            </div>
        </div>
    );
}

/**
 * Mobile Toggle Buttons
 */
export function MobileToggleButtons({ onOpenLeftDrawer, onOpenRightDrawer }) {
    return (
        <div className="lg:hidden fixed bottom-4 left-4 right-4 z-40 flex justify-between pointer-events-none">
            <button
                onClick={onOpenLeftDrawer}
                className="pointer-events-auto flex items-center gap-2 px-4 py-2.5 bg-primary text-white rounded-lg shadow-lg hover:bg-primary/90 transition-colors"
            >
                <iconify-icon
                    icon="mdi:plus-box-multiple"
                    width="20"
                    height="20"
                ></iconify-icon>
                <span className="text-sm font-medium">{__("Blocks")}</span>
            </button>
            <button
                onClick={onOpenRightDrawer}
                className="pointer-events-auto flex items-center gap-2 px-4 py-2.5 bg-gray-700 text-white rounded-lg shadow-lg hover:bg-gray-800 transition-colors"
            >
                <iconify-icon icon="mdi:cog" width="20" height="20"></iconify-icon>
                <span className="text-sm font-medium">{__("Properties")}</span>
            </button>
        </div>
    );
}

export default {
    LeftSidebar,
    RightSidebar,
    LeftDrawer,
    RightDrawer,
    MobileToggleButtons,
};
