/**
 * Button Block - Property Editor
 *
 * Renders the property fields for the button block in the properties panel.
 */

const ButtonBlockEditor = ({ props, onUpdate }) => {
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

                <label style={labelStyle}>Button Text</label>
                <input
                    type="text"
                    value={props.text || ''}
                    onChange={(e) => handleChange('text', e.target.value)}
                    placeholder="Click Here"
                    className="form-control mb-3"
                />

                <label style={labelStyle}>Link URL</label>
                <input
                    type="url"
                    value={props.link || ''}
                    onChange={(e) => handleChange('link', e.target.value)}
                    placeholder="https://..."
                    className="form-control"
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

            {/* Colors Section */}
            <div style={sectionStyle}>
                <div style={sectionTitleStyle}>Colors</div>

                <label style={labelStyle}>Background Color</label>
                <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '12px' }}>
                    <input
                        type="color"
                        value={props.backgroundColor || '#635bff'}
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
                        value={props.backgroundColor || '#635bff'}
                        onChange={(e) => handleChange('backgroundColor', e.target.value)}
                        className="form-control"
                        style={{ flex: 1 }}
                    />
                </div>

                <label style={labelStyle}>Text Color</label>
                <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                    <input
                        type="color"
                        value={props.textColor || '#ffffff'}
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
                        value={props.textColor || '#ffffff'}
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
                    value={props.fontSize || '16px'}
                    onChange={(e) => handleChange('fontSize', e.target.value)}
                    className="form-control mb-3"
                >
                    <option value="14px">Small (14px)</option>
                    <option value="16px">Medium (16px)</option>
                    <option value="18px">Large (18px)</option>
                    <option value="20px">X-Large (20px)</option>
                </select>

                <label style={labelStyle}>Font Weight</label>
                <select
                    value={props.fontWeight || '600'}
                    onChange={(e) => handleChange('fontWeight', e.target.value)}
                    className="form-control"
                >
                    <option value="normal">Normal</option>
                    <option value="500">Medium</option>
                    <option value="600">Semi Bold</option>
                    <option value="bold">Bold</option>
                </select>
            </div>

            {/* Style Section */}
            <div style={sectionStyle}>
                <div style={sectionTitleStyle}>Style</div>

                <label style={labelStyle}>Border Radius</label>
                <select
                    value={props.borderRadius || '6px'}
                    onChange={(e) => handleChange('borderRadius', e.target.value)}
                    className="form-control mb-3"
                >
                    <option value="0">None</option>
                    <option value="4px">Small (4px)</option>
                    <option value="6px">Medium (6px)</option>
                    <option value="8px">Large (8px)</option>
                    <option value="12px">X-Large (12px)</option>
                    <option value="9999px">Pill</option>
                </select>

                <label style={labelStyle}>Padding</label>
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
        </div>
    );
};

export default ButtonBlockEditor;
