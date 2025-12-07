/**
 * Preformatted Block - Property Editor
 *
 * Content is edited inline in the block itself.
 * Styling (colors, typography, spacing) is controlled via Layout Styles.
 * This panel is intentionally minimal.
 */

const PreformattedBlockEditor = () => {
    return (
        <div className="text-center py-6 text-gray-500">
            <iconify-icon icon="mdi:format-text-wrapping-wrap" width="32" height="32" class="mb-2 opacity-50"></iconify-icon>
            <p className="text-sm">
                Click the block to edit text directly.
            </p>
            <p className="text-xs text-gray-400 mt-1">
                Use Layout Styles for colors and typography.
            </p>
        </div>
    );
};

export default PreformattedBlockEditor;
