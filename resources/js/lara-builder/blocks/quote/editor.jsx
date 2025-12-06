/**
 * Quote Block - Property Editor
 *
 * Note: Background color and text color are controlled by Layout Styles.
 * Border color (accent) is specific to this block.
 */

const QuoteBlockEditor = ({ props, onUpdate }) => {
    const handleChange = (field, value) => {
        onUpdate({ ...props, [field]: value });
    };

    return (
        <div className="space-y-4">
            {/* Content Section */}
            <Section title="Content">
                <Label>Quote Text</Label>
                <textarea
                    value={props.text || ''}
                    onChange={(e) => handleChange('text', e.target.value)}
                    placeholder="Enter quote..."
                    className="form-control mb-3"
                    rows={3}
                />

                <Label>Author Name</Label>
                <input
                    type="text"
                    value={props.author || ''}
                    onChange={(e) => handleChange('author', e.target.value)}
                    placeholder="John Doe"
                    className="form-control mb-3"
                />

                <Label>Author Title</Label>
                <input
                    type="text"
                    value={props.authorTitle || ''}
                    onChange={(e) => handleChange('authorTitle', e.target.value)}
                    placeholder="CEO, Company"
                    className="form-control"
                />
            </Section>

            {/* Style Section */}
            <Section title="Style">
                <Label>Accent Border Color</Label>
                <ColorPicker
                    value={props.borderColor || '#635bff'}
                    onChange={(value) => handleChange('borderColor', value)}
                />
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
