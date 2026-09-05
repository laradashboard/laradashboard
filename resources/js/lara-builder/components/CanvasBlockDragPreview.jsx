/**
 * Compact drag preview for canvas blocks (reorder drag).
 */

import { blockRegistry } from "../registry/BlockRegistry";

export default function CanvasBlockDragPreview({ blockType }) {
    if (!blockType) {
        return null;
    }

    const config = blockRegistry.get(blockType);

    if (!config) {
        return null;
    }

    return (
        <div className="flex items-center gap-2 px-4 py-3 bg-white border-2 border-primary rounded-lg shadow-lg cursor-grabbing select-none">
            {config.icon ? (
                <iconify-icon
                    icon={config.icon}
                    width="20"
                    height="20"
                    class="text-primary shrink-0"
                />
            ) : null}
            <span className="text-sm font-medium text-gray-800">
                {config.label}
            </span>
        </div>
    );
}
