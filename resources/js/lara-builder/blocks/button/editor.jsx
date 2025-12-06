/**
 * Button Block - Property Editor
 *
 * Renders the property fields for the button block in the properties panel.
 * Note: Button text is edited inline on canvas (click button to edit).
 * Typography is controlled by the central Layout Styles section.
 */

const ButtonBlockEditor = ({ props, onUpdate }) => {
    const handleChange = (field, value) => {
        onUpdate({ ...props, [field]: value });
    };

    return (
        <div className="space-y-4">
            {/* Link Section */}
            <Section title="Link">
                <Label>URL</Label>
                <input
                    type="url"
                    value={props.link || ''}
                    onChange={(e) => handleChange('link', e.target.value)}
                    placeholder="https://..."
                    className="form-control"
                />

                {props.link && (
                    <>
                        <div className="mt-3">
                            <Label>Open In</Label>
                            <select
                                value={props.target || '_self'}
                                onChange={(e) => handleChange('target', e.target.value)}
                                className="form-control"
                            >
                                <option value="_self">Same Window</option>
                                <option value="_blank">New Tab</option>
                            </select>
                        </div>

                        <div className="mt-3">
                            <Label>Rel Attribute</Label>
                            <div className="space-y-2">
                                <label className="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        checked={props.nofollow || false}
                                        onChange={(e) => handleChange('nofollow', e.target.checked)}
                                        className="rounded border-gray-300"
                                    />
                                    nofollow
                                </label>
                                <label className="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        checked={props.sponsored || false}
                                        onChange={(e) => handleChange('sponsored', e.target.checked)}
                                        className="rounded border-gray-300"
                                    />
                                    sponsored
                                </label>
                            </div>
                            <p className="mt-1 text-xs text-gray-400">
                                noopener & noreferrer are auto-added for new tab links
                            </p>
                        </div>
                    </>
                )}
            </Section>

            {/* Colors Section */}
            <Section title="Colors">
                <Label>Background Color</Label>
                <ColorPicker
                    value={props.backgroundColor || '#635bff'}
                    onChange={(value) => handleChange('backgroundColor', value)}
                />

                <div className="mt-3">
                    <Label>Text Color</Label>
                    <ColorPicker
                        value={props.textColor || '#ffffff'}
                        onChange={(value) => handleChange('textColor', value)}
                    />
                </div>
            </Section>

            {/* Style Section */}
            <Section title="Style">
                <Label>Border Radius</Label>
                <select
                    value={props.borderRadius || '6px'}
                    onChange={(e) => handleChange('borderRadius', e.target.value)}
                    className="form-control"
                >
                    <option value="0">None</option>
                    <option value="4px">Small (4px)</option>
                    <option value="6px">Medium (6px)</option>
                    <option value="8px">Large (8px)</option>
                    <option value="12px">X-Large (12px)</option>
                    <option value="9999px">Pill</option>
                </select>

                <div className="mt-3">
                    <Label>Padding</Label>
                    <select
                        value={props.padding || '12px 24px'}
                        onChange={(e) => handleChange('padding', e.target.value)}
                        className="form-control"
                    >
                        <option value="8px 16px">Small</option>
                        <option value="12px 24px">Medium</option>
                        <option value="16px 32px">Large</option>
                        <option value="20px 40px">X-Large</option>
                    </select>
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

export default ButtonBlockEditor;
