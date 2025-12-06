/**
 * Text Block - Property Editor
 *
 * Renders the property fields for the text block in the properties panel.
 * Note: Typography and colors are controlled by the central Layout Styles section.
 * Alignment is controlled via the toolbar when the block is selected.
 */

const TextBlockEditor = ({ props, onUpdate }) => {
    const handleChange = (field, value) => {
        onUpdate({ ...props, [field]: value });
    };

    return (
        <div className="space-y-4">
            {/* Content Section */}
            <Section title="Content">
                <Label>Text Content</Label>
                <textarea
                    value={props.content || ''}
                    onChange={(e) => handleChange('content', e.target.value)}
                    placeholder="Enter your text..."
                    className="form-control"
                    rows={4}
                    style={{ resize: 'vertical' }}
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

export default TextBlockEditor;
