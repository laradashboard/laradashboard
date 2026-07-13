import { __ } from "@lara-builder/i18n";

/**
 * BlockConvertSection - Quick convert actions for text ↔ heading blocks
 * Shown in the block inspector sidebar for easy discovery.
 */
const BlockConvertSection = ({ blockType, onConvert }) => {
    if (!onConvert) {
        return null;
    }

    if (blockType === "text") {
        return (
            <div className="mb-4 pb-4 border-b border-gray-200">
                <p className="text-sm font-medium text-gray-700 mb-2">
                    {__("Block Type")}
                </p>
                <button
                    type="button"
                    onClick={() => onConvert("heading")}
                    className="w-full px-3 py-2 text-sm text-left text-gray-700 bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-lg flex items-center gap-2 transition-colors"
                >
                    <iconify-icon
                        icon="mdi:format-header-1"
                        width="18"
                        height="18"
                        class="text-gray-500 shrink-0"
                    ></iconify-icon>
                    {__("Convert to Heading")}
                </button>
            </div>
        );
    }

    if (blockType === "heading") {
        return (
            <div className="mb-4 pb-4 border-b border-gray-200">
                <p className="text-sm font-medium text-gray-700 mb-2">
                    {__("Block Type")}
                </p>
                <button
                    type="button"
                    onClick={() => onConvert("text")}
                    className="w-full px-3 py-2 text-sm text-left text-gray-700 bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-lg flex items-center gap-2 transition-colors"
                >
                    <iconify-icon
                        icon="mdi:format-text"
                        width="18"
                        height="18"
                        class="text-gray-500 shrink-0"
                    ></iconify-icon>
                    {__("Convert to Text")}
                </button>
            </div>
        );
    }

    return null;
};

export default BlockConvertSection;
