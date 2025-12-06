/**
 * Code Block - Property Editor
 *
 * Renders the property fields for the code block in the properties panel.
 */

const CodeBlockEditor = ({ props, onUpdate }) => {
    const handleChange = (field, value) => {
        onUpdate({ ...props, [field]: value });
    };

    const sectionStyle = {
        marginBottom: '16px',
    };

    const labelStyle = {
        display: 'block',
        fontSize: '13px',
        fontWeight: '500',
        color: '#374151',
        marginBottom: '6px',
    };

    const sectionTitleStyle = {
        fontSize: '12px',
        fontWeight: '600',
        color: '#6b7280',
        textTransform: 'uppercase',
        letterSpacing: '0.5px',
        marginBottom: '12px',
        paddingBottom: '8px',
        borderBottom: '1px solid #e5e7eb',
    };

    return (
        <div>
            {/* Content Section */}
            <div style={sectionStyle}>
                <div style={sectionTitleStyle}>Content</div>

                <label style={labelStyle}>Code</label>
                <textarea
                    value={props.code || ''}
                    onChange={(e) => handleChange('code', e.target.value)}
                    placeholder="Enter your code here..."
                    className="form-control"
                    rows={10}
                    style={{
                        fontFamily: 'ui-monospace, SFMono-Regular, "SF Mono", Menlo, Consolas, "Liberation Mono", monospace',
                        fontSize: '13px',
                        lineHeight: '1.5',
                    }}
                />
            </div>

            {/* Colors Section */}
            <div style={sectionStyle}>
                <div style={sectionTitleStyle}>Colors</div>

                <label style={labelStyle}>Background Color</label>
                <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '12px' }}>
                    <input
                        type="color"
                        value={props.backgroundColor || '#1e1e1e'}
                        onChange={(e) => handleChange('backgroundColor', e.target.value)}
                        style={{
                            width: '40px',
                            height: '36px',
                            padding: '2px',
                            border: '1px solid #d1d5db',
                            borderRadius: '4px',
                            cursor: 'pointer',
                        }}
                    />
                    <input
                        type="text"
                        value={props.backgroundColor || '#1e1e1e'}
                        onChange={(e) => handleChange('backgroundColor', e.target.value)}
                        className="form-control"
                        style={{ flex: 1 }}
                    />
                </div>

                <label style={labelStyle}>Text Color</label>
                <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                    <input
                        type="color"
                        value={props.textColor || '#d4d4d4'}
                        onChange={(e) => handleChange('textColor', e.target.value)}
                        style={{
                            width: '40px',
                            height: '36px',
                            padding: '2px',
                            border: '1px solid #d1d5db',
                            borderRadius: '4px',
                            cursor: 'pointer',
                        }}
                    />
                    <input
                        type="text"
                        value={props.textColor || '#d4d4d4'}
                        onChange={(e) => handleChange('textColor', e.target.value)}
                        className="form-control"
                        style={{ flex: 1 }}
                    />
                </div>
            </div>

            {/* Typography Section */}
            <div style={sectionStyle}>
                <div style={sectionTitleStyle}>Typography</div>

                <label style={labelStyle}>Font Size</label>
                <select
                    value={props.fontSize || '14px'}
                    onChange={(e) => handleChange('fontSize', e.target.value)}
                    className="form-control"
                >
                    <option value="12px">X-Small (12px)</option>
                    <option value="14px">Small (14px)</option>
                    <option value="16px">Medium (16px)</option>
                    <option value="18px">Large (18px)</option>
                </select>
            </div>

            {/* Style Section */}
            <div style={sectionStyle}>
                <div style={sectionTitleStyle}>Style</div>

                <label style={labelStyle}>Border Radius</label>
                <select
                    value={props.borderRadius || '8px'}
                    onChange={(e) => handleChange('borderRadius', e.target.value)}
                    className="form-control"
                >
                    <option value="0">None</option>
                    <option value="4px">Small (4px)</option>
                    <option value="6px">Medium (6px)</option>
                    <option value="8px">Large (8px)</option>
                    <option value="12px">X-Large (12px)</option>
                </select>
            </div>
        </div>
    );
};

export default CodeBlockEditor;
