/**
 * Preformatted Block - Property Editor
 */

const PreformattedBlockEditor = ({ props, onUpdate }) => {
    const handleChange = (field, value) => {
        onUpdate({ ...props, [field]: value });
    };

    const sectionStyle = { marginBottom: '16px' };
    const labelStyle = { display: 'block', fontSize: '13px', fontWeight: '500', color: '#374151', marginBottom: '6px' };
    const sectionTitleStyle = { fontSize: '12px', fontWeight: '600', color: '#6b7280', textTransform: 'uppercase', letterSpacing: '0.5px', marginBottom: '12px', paddingBottom: '8px', borderBottom: '1px solid #e5e7eb' };

    return (
        <div>
            <div style={sectionStyle}>
                <div style={sectionTitleStyle}>Content</div>

                <label style={labelStyle}>Preformatted Text</label>
                <textarea
                    value={props.text || ''}
                    onChange={(e) => handleChange('text', e.target.value)}
                    placeholder="Enter preformatted text..."
                    className="form-control"
                    rows={6}
                    style={{ fontFamily: 'ui-monospace, monospace' }}
                />
            </div>

            <div style={sectionStyle}>
                <div style={sectionTitleStyle}>Styling</div>

                <label style={labelStyle}>Font Size</label>
                <input
                    type="text"
                    value={props.fontSize || '14px'}
                    onChange={(e) => handleChange('fontSize', e.target.value)}
                    placeholder="14px"
                    className="form-control mb-3"
                />

                <label style={labelStyle}>Border Radius</label>
                <input
                    type="text"
                    value={props.borderRadius || '4px'}
                    onChange={(e) => handleChange('borderRadius', e.target.value)}
                    placeholder="4px"
                    className="form-control"
                />
            </div>

            <div style={sectionStyle}>
                <div style={sectionTitleStyle}>Colors</div>

                <label style={labelStyle}>Background Color</label>
                <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '12px' }}>
                    <input type="color" value={props.backgroundColor || '#f5f5f5'} onChange={(e) => handleChange('backgroundColor', e.target.value)} style={{ width: '40px', height: '36px', padding: '2px', border: '1px solid #d1d5db', borderRadius: '4px', cursor: 'pointer' }} />
                    <input type="text" value={props.backgroundColor || '#f5f5f5'} onChange={(e) => handleChange('backgroundColor', e.target.value)} className="form-control" style={{ flex: 1 }} />
                </div>

                <label style={labelStyle}>Text Color</label>
                <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '12px' }}>
                    <input type="color" value={props.textColor || '#333333'} onChange={(e) => handleChange('textColor', e.target.value)} style={{ width: '40px', height: '36px', padding: '2px', border: '1px solid #d1d5db', borderRadius: '4px', cursor: 'pointer' }} />
                    <input type="text" value={props.textColor || '#333333'} onChange={(e) => handleChange('textColor', e.target.value)} className="form-control" style={{ flex: 1 }} />
                </div>

                <label style={labelStyle}>Border Color</label>
                <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                    <input type="color" value={props.borderColor || '#e0e0e0'} onChange={(e) => handleChange('borderColor', e.target.value)} style={{ width: '40px', height: '36px', padding: '2px', border: '1px solid #d1d5db', borderRadius: '4px', cursor: 'pointer' }} />
                    <input type="text" value={props.borderColor || '#e0e0e0'} onChange={(e) => handleChange('borderColor', e.target.value)} className="form-control" style={{ flex: 1 }} />
                </div>
            </div>
        </div>
    );
};

export default PreformattedBlockEditor;
