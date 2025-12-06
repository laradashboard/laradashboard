/**
 * List Block - Property Editor
 *
 * Note: Typography and colors are controlled by Layout Styles.
 * Icon color for check lists is specific to this block.
 */

const ListBlockEditor = ({ props, onUpdate }) => {
    const handleChange = (field, value) => {
        onUpdate({ ...props, [field]: value });
    };

    return (
        <div className="space-y-4">
            {/* List Type Section */}
            <Section title="List Type">
                <Label>Type</Label>
                <select
                    value={props.listType || 'bullet'}
                    onChange={(e) => handleChange('listType', e.target.value)}
                    className="form-control"
                >
                    <option value="bullet">Bullet List</option>
                    <option value="number">Numbered List</option>
                    <option value="check">Check List</option>
                </select>
            </Section>

            {/* Style Section - only for check lists */}
            {props.listType === 'check' && (
                <Section title="Style">
                    <Label>Check Icon Color</Label>
                    <ColorPicker
                        value={props.iconColor || '#635bff'}
                        onChange={(value) => handleChange('iconColor', value)}
                    />
                </Section>
            )}

            {/* Info Section */}
            <div className="p-3 bg-gray-100 dark:bg-gray-800 rounded-lg text-xs text-gray-600 dark:text-gray-400 leading-relaxed">
                <strong className="text-gray-700 dark:text-gray-300">Tip:</strong> Click the list in the canvas to edit items directly. Press Enter to add new items.
            </div>
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

export default ListBlockEditor;
