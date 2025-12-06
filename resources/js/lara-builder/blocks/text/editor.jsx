/**
 * Text Block - Property Editor
 *
 * Renders the property fields for the text block in the properties panel.
 */

const TextBlockEditor = ({ props, onUpdate }) => {
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

                <label style={labelStyle}>Text Content</label>
                <textarea
                    value={props.content || ''}
                    onChange={(e) => handleChange('content', e.target.value)}
                    placeholder="Enter your text..."
                    className="form-control"
                    rows={4}
                    style={{ resize: 'vertical' }}
                />
            </div>

            {/* Alignment Section */}
            <div style={sectionStyle}>
                <div style={sectionTitleStyle}>Alignment</div>

                <div style={{ display: 'flex', gap: '4px' }}>
                    {['left', 'center', 'right'].map((align) => (
                        <button
                            key={align}
                            onClick={() => handleChange('align', align)}
                            className={`btn ${props.align === align ? 'btn-primary' : 'btn-default'}`}
                            style={{ flex: 1, padding: '8px' }}
                            title={`Align ${align}`}
                        >
                            <iconify-icon
                                icon={`mdi:format-align-${align}`}
                                width="18"
                                height="18"
                            />
                        </button>
                    ))}
                </div>
            </div>

            {/* Typography Section */}
            <div style={sectionStyle}>
                <div style={sectionTitleStyle}>Typography</div>

                <label style={labelStyle}>Font Size</label>
                <select
                    value={props.fontSize || '16px'}
                    onChange={(e) => handleChange('fontSize', e.target.value)}
                    className="form-control mb-3"
                >
                    <option value="12px">Small (12px)</option>
                    <option value="14px">Default (14px)</option>
                    <option value="16px">Medium (16px)</option>
                    <option value="18px">Large (18px)</option>
                    <option value="20px">X-Large (20px)</option>
                    <option value="24px">XX-Large (24px)</option>
                </select>

                <label style={labelStyle}>Line Height</label>
                <select
                    value={props.lineHeight || '1.6'}
                    onChange={(e) => handleChange('lineHeight', e.target.value)}
                    className="form-control"
                >
                    <option value="1">Tight (1)</option>
                    <option value="1.4">Normal (1.4)</option>
                    <option value="1.6">Relaxed (1.6)</option>
                    <option value="2">Loose (2)</option>
                </select>
            </div>

            {/* Colors Section */}
            <div style={sectionStyle}>
                <div style={sectionTitleStyle}>Colors</div>

                <label style={labelStyle}>Text Color</label>
                <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                    <input
                        type="color"
                        value={props.color || '#666666'}
                        onChange={(e) => handleChange('color', e.target.value)}
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
                        value={props.color || '#666666'}
                        onChange={(e) => handleChange('color', e.target.value)}
                        className="form-control"
                        style={{ flex: 1 }}
                        placeholder="#666666"
                    />
                </div>
            </div>
        </div>
    );
};

export default TextBlockEditor;
