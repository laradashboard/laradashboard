/**
 * Heading Block - Property Editor
 *
 * Renders the property fields for the heading block in the properties panel.
 * Note: Typography and colors are controlled by the central Layout Styles section.
 * Alignment is controlled via the toolbar when the block is selected.
 */

const HeadingBlockEditor = ({ props, onUpdate }) => {
    const handleChange = (field, value) => {
        onUpdate({ ...props, [field]: value });
    };

    return (
        <div className="space-y-4">
            {/* Content Section */}
            <Section title="Content">
                <Label>Heading Text</Label>
                <input
                    type="text"
                    value={props.text || ''}
                    onChange={(e) => handleChange('text', e.target.value)}
                    placeholder="Enter heading text..."
                    className="form-control mb-3"
                />

                <Label>Heading Level</Label>
                <select
                    value={props.level || 'h1'}
                    onChange={(e) => handleChange('level', e.target.value)}
                    className="form-control"
                >
                    <option value="h1">H1 - Main Heading</option>
                    <option value="h2">H2 - Section Heading</option>
                    <option value="h3">H3 - Subsection</option>
                    <option value="h4">H4 - Minor Heading</option>
                    <option value="h5">H5 - Small Heading</option>
                    <option value="h6">H6 - Smallest Heading</option>
                </select>
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

export default HeadingBlockEditor;
