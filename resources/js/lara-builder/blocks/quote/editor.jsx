/**
 * Quote Block - Property Editor
 *
 * Content (quote text, author, title) is now edited inline in the block.
 * Alignment is controlled via the toolbar.
 * This panel only contains color styling options.
 */

const QuoteBlockEditor = ({ props, onUpdate }) => {
    const handleChange = (field, value) => {
        onUpdate({ ...props, [field]: value });
    };

    return (
        <div className="space-y-4">
            {/* Colors Section */}
            <Section title="Colors">
                <Label>Quote Text Color</Label>
                <ColorPicker
                    value={props.textColor || '#475569'}
                    onChange={(value) => handleChange('textColor', value)}
                />

                <div className="mt-3">
                    <Label>Author Name Color</Label>
                    <ColorPicker
                        value={props.authorColor || '#1e293b'}
                        onChange={(value) => handleChange('authorColor', value)}
                    />
                </div>

                <div className="mt-3">
                    <Label>Accent Border Color</Label>
                    <ColorPicker
                        value={props.borderColor || '#635bff'}
                        onChange={(value) => handleChange('borderColor', value)}
                    />
                </div>

                <div className="mt-3">
                    <Label>Background Color</Label>
                    <ColorPicker
                        value={props.backgroundColor || '#f8fafc'}
                        onChange={(value) => handleChange('backgroundColor', value)}
                    />
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

export default QuoteBlockEditor;
