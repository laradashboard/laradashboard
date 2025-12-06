/**
 * Image Block - Property Editor
 *
 * Renders the property fields for the image block in the properties panel.
 */

const ImageBlockEditor = ({ props, onUpdate, onImageUpload }) => {
    const handleChange = (field, value) => {
        onUpdate({ ...props, [field]: value });
    };

    const handleImageUpload = async (e) => {
        const file = e.target.files?.[0];
        if (!file) return;

        try {
            if (onImageUpload) {
                const result = await onImageUpload(file);
                handleChange('src', result.url || result.path);
            }
        } catch (error) {
            console.error('Image upload failed:', error);
        }
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
            {/* Image Section */}
            <div style={sectionStyle}>
                <div style={sectionTitleStyle}>Image</div>

                {props.src && (
                    <div style={{ marginBottom: '12px' }}>
                        <img
                            src={props.src}
                            alt="Preview"
                            style={{
                                maxWidth: '100%',
                                maxHeight: '150px',
                                borderRadius: '4px',
                                border: '1px solid #e5e7eb',
                            }}
                        />
                    </div>
                )}

                <label style={labelStyle}>Upload Image</label>
                <input
                    type="file"
                    accept="image/*"
                    onChange={handleImageUpload}
                    className="form-control mb-3"
                />

                <label style={labelStyle}>Or Enter URL</label>
                <input
                    type="url"
                    value={props.src || ''}
                    onChange={(e) => handleChange('src', e.target.value)}
                    placeholder="https://..."
                    className="form-control mb-3"
                />

                <label style={labelStyle}>Alt Text</label>
                <input
                    type="text"
                    value={props.alt || ''}
                    onChange={(e) => handleChange('alt', e.target.value)}
                    placeholder="Describe the image..."
                    className="form-control"
                />
            </div>

            {/* Link Section */}
            <div style={sectionStyle}>
                <div style={sectionTitleStyle}>Link</div>

                <label style={labelStyle}>Link URL</label>
                <input
                    type="url"
                    value={props.link || ''}
                    onChange={(e) => handleChange('link', e.target.value)}
                    placeholder="https://..."
                    className="form-control"
                />
            </div>

            {/* Size Section */}
            <div style={sectionStyle}>
                <div style={sectionTitleStyle}>Size</div>

                <label style={labelStyle}>Width</label>
                <select
                    value={props.width || '100%'}
                    onChange={(e) => handleChange('width', e.target.value)}
                    className="form-control mb-3"
                >
                    <option value="100%">Full Width (100%)</option>
                    <option value="75%">Three Quarters (75%)</option>
                    <option value="50%">Half (50%)</option>
                    <option value="25%">Quarter (25%)</option>
                    <option value="custom">Custom</option>
                </select>

                {props.width === 'custom' && (
                    <>
                        <label style={labelStyle}>Custom Width</label>
                        <input
                            type="text"
                            value={props.customWidth || ''}
                            onChange={(e) => handleChange('customWidth', e.target.value)}
                            placeholder="e.g., 300px"
                            className="form-control mb-3"
                        />
                    </>
                )}

                <label style={labelStyle}>Height</label>
                <select
                    value={props.height || 'auto'}
                    onChange={(e) => handleChange('height', e.target.value)}
                    className="form-control"
                >
                    <option value="auto">Auto</option>
                    <option value="custom">Custom</option>
                </select>

                {props.height === 'custom' && (
                    <>
                        <label style={{ ...labelStyle, marginTop: '12px' }}>Custom Height</label>
                        <input
                            type="text"
                            value={props.customHeight || ''}
                            onChange={(e) => handleChange('customHeight', e.target.value)}
                            placeholder="e.g., 200px"
                            className="form-control"
                        />
                    </>
                )}
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
        </div>
    );
};

export default ImageBlockEditor;
