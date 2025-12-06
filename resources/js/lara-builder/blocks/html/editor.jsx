/**
 * HTML Block - Property Editor
 *
 * Renders the property fields for the HTML block in the properties panel.
 * Provides a textarea for raw HTML code editing.
 */

const HtmlBlockEditor = ({ props, onUpdate }) => {
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

    const textareaStyle = {
        width: '100%',
        minHeight: '200px',
        padding: '12px',
        fontSize: '13px',
        fontFamily: 'monospace',
        lineHeight: '1.5',
        border: '1px solid #d1d5db',
        borderRadius: '4px',
        resize: 'vertical',
        backgroundColor: '#f9fafb',
    };

    const infoBoxStyle = {
        padding: '12px',
        backgroundColor: '#eff6ff',
        border: '1px solid #bfdbfe',
        borderRadius: '6px',
        fontSize: '12px',
        color: '#1e40af',
        marginBottom: '12px',
    };

    return (
        <div>
            {/* Content Section */}
            <div style={sectionStyle}>
                <div style={sectionTitleStyle}>HTML Code</div>

                <div style={infoBoxStyle}>
                    <div style={{ fontWeight: '600', marginBottom: '4px' }}>
                        Editing Tips:
                    </div>
                    <ul style={{ margin: 0, paddingLeft: '20px' }}>
                        <li>Edit raw HTML directly here</li>
                        <li>Double-click the block to use the visual editor</li>
                        <li>Use inline styles for email compatibility</li>
                    </ul>
                </div>

                <label style={labelStyle}>HTML Content</label>
                <textarea
                    value={props.code || ''}
                    onChange={(e) => handleChange('code', e.target.value)}
                    placeholder="<div>Enter your HTML code here...</div>"
                    style={textareaStyle}
                    spellCheck="false"
                />

                <div style={{ marginTop: '8px', fontSize: '12px', color: '#6b7280' }}>
                    Lines: {(props.code || '').split('\n').length} | Characters: {(props.code || '').length}
                </div>
            </div>

            {/* Quick Templates */}
            <div style={sectionStyle}>
                <div style={sectionTitleStyle}>Quick Templates</div>

                <div style={{ display: 'flex', flexDirection: 'column', gap: '8px' }}>
                    <button
                        type="button"
                        onClick={() => handleChange('code', '<div style="padding: 20px; text-align: center; background: #f3f4f6; border-radius: 8px;">Centered content</div>')}
                        className="btn-default"
                        style={{ justifyContent: 'flex-start', fontSize: '13px' }}
                    >
                        <iconify-icon icon="mdi:format-align-center" width="16" height="16" style={{ marginRight: '8px' }} />
                        Centered Container
                    </button>

                    <button
                        type="button"
                        onClick={() => handleChange('code', '<div style="padding: 16px; background: #dbeafe; border-left: 4px solid #3b82f6; border-radius: 4px;">Information box</div>')}
                        className="btn-default"
                        style={{ justifyContent: 'flex-start', fontSize: '13px' }}
                    >
                        <iconify-icon icon="mdi:information" width="16" height="16" style={{ marginRight: '8px' }} />
                        Info Box
                    </button>

                    <button
                        type="button"
                        onClick={() => handleChange('code', '<table style="width: 100%; border-collapse: collapse;"><tr><td style="padding: 12px; border: 1px solid #e5e7eb;">Cell 1</td><td style="padding: 12px; border: 1px solid #e5e7eb;">Cell 2</td></tr></table>')}
                        className="btn-default"
                        style={{ justifyContent: 'flex-start', fontSize: '13px' }}
                    >
                        <iconify-icon icon="mdi:table" width="16" height="16" style={{ marginRight: '8px' }} />
                        Simple Table
                    </button>

                    <button
                        type="button"
                        onClick={() => handleChange('code', '<div style="padding: 20px; text-align: center;"><img src="https://via.placeholder.com/400x200" alt="Placeholder" style="max-width: 100%; height: auto; border-radius: 8px;" /></div>')}
                        className="btn-default"
                        style={{ justifyContent: 'flex-start', fontSize: '13px' }}
                    >
                        <iconify-icon icon="mdi:image" width="16" height="16" style={{ marginRight: '8px' }} />
                        Image Container
                    </button>
                </div>
            </div>
        </div>
    );
};

export default HtmlBlockEditor;
