/**
 * Quote Block - Property Editor
 */

const QuoteBlockEditor = ({ props, onUpdate }) => {
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

                <label style={labelStyle}>Quote Text</label>
                <textarea
                    value={props.text || ''}
                    onChange={(e) => handleChange('text', e.target.value)}
                    placeholder="Enter quote..."
                    className="form-control mb-3"
                    rows={3}
                />

                <label style={labelStyle}>Author Name</label>
                <input
                    type="text"
                    value={props.author || ''}
                    onChange={(e) => handleChange('author', e.target.value)}
                    placeholder="John Doe"
                    className="form-control mb-3"
                />

                <label style={labelStyle}>Author Title</label>
                <input
                    type="text"
                    value={props.authorTitle || ''}
                    onChange={(e) => handleChange('authorTitle', e.target.value)}
                    placeholder="CEO, Company"
                    className="form-control"
                />
            </div>

            <div style={sectionStyle}>
                <div style={sectionTitleStyle}>Alignment</div>
                <div style={{ display: 'flex', gap: '4px' }}>
                    {['left', 'center', 'right'].map((align) => (
                        <button
                            key={align}
                            onClick={() => handleChange('align', align)}
                            className={`btn ${props.align === align ? 'btn-primary' : 'btn-default'}`}
                            style={{ flex: 1, padding: '8px' }}
                        >
                            <iconify-icon icon={`mdi:format-align-${align}`} width="18" height="18" />
                        </button>
                    ))}
                </div>
            </div>

            <div style={sectionStyle}>
                <div style={sectionTitleStyle}>Colors</div>

                <label style={labelStyle}>Border Color</label>
                <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '12px' }}>
                    <input type="color" value={props.borderColor || '#635bff'} onChange={(e) => handleChange('borderColor', e.target.value)} style={{ width: '40px', height: '36px', padding: '2px', border: '1px solid #d1d5db', borderRadius: '4px', cursor: 'pointer' }} />
                    <input type="text" value={props.borderColor || '#635bff'} onChange={(e) => handleChange('borderColor', e.target.value)} className="form-control" style={{ flex: 1 }} />
                </div>

                <label style={labelStyle}>Background Color</label>
                <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '12px' }}>
                    <input type="color" value={props.backgroundColor || '#f8fafc'} onChange={(e) => handleChange('backgroundColor', e.target.value)} style={{ width: '40px', height: '36px', padding: '2px', border: '1px solid #d1d5db', borderRadius: '4px', cursor: 'pointer' }} />
                    <input type="text" value={props.backgroundColor || '#f8fafc'} onChange={(e) => handleChange('backgroundColor', e.target.value)} className="form-control" style={{ flex: 1 }} />
                </div>

                <label style={labelStyle}>Text Color</label>
                <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '12px' }}>
                    <input type="color" value={props.textColor || '#475569'} onChange={(e) => handleChange('textColor', e.target.value)} style={{ width: '40px', height: '36px', padding: '2px', border: '1px solid #d1d5db', borderRadius: '4px', cursor: 'pointer' }} />
                    <input type="text" value={props.textColor || '#475569'} onChange={(e) => handleChange('textColor', e.target.value)} className="form-control" style={{ flex: 1 }} />
                </div>

                <label style={labelStyle}>Author Color</label>
                <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                    <input type="color" value={props.authorColor || '#1e293b'} onChange={(e) => handleChange('authorColor', e.target.value)} style={{ width: '40px', height: '36px', padding: '2px', border: '1px solid #d1d5db', borderRadius: '4px', cursor: 'pointer' }} />
                    <input type="text" value={props.authorColor || '#1e293b'} onChange={(e) => handleChange('authorColor', e.target.value)} className="form-control" style={{ flex: 1 }} />
                </div>
            </div>
        </div>
    );
};

export default QuoteBlockEditor;
