/**
 * Divider Block - Property Editor
 */

import { __ } from '@lara-builder/i18n';

const DividerBlockEditor = ({ props, onUpdate }) => {
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
            <div style={sectionStyle}>
                <div style={sectionTitleStyle}>{__('Style')}</div>

                <label style={labelStyle}>{__('Line Style')}</label>
                <select
                    value={props.style || 'solid'}
                    onChange={(e) => handleChange('style', e.target.value)}
                    className="form-control mb-3"
                >
                    <option value="solid">{__('Solid')}</option>
                    <option value="dashed">{__('Dashed')}</option>
                    <option value="dotted">{__('Dotted')}</option>
                </select>

                <label style={labelStyle}>{__('Thickness')}</label>
                <select
                    value={props.thickness || '1px'}
                    onChange={(e) => handleChange('thickness', e.target.value)}
                    className="form-control mb-3"
                >
                    <option value="1px">1px</option>
                    <option value="2px">2px</option>
                    <option value="3px">3px</option>
                    <option value="4px">4px</option>
                </select>

                <label style={labelStyle}>{__('Width')}</label>
                <select
                    value={props.width || '100%'}
                    onChange={(e) => handleChange('width', e.target.value)}
                    className="form-control"
                >
                    <option value="100%">{__('Full Width')}</option>
                    <option value="75%">75%</option>
                    <option value="50%">50%</option>
                    <option value="25%">25%</option>
                </select>
            </div>

            <div style={sectionStyle}>
                <div style={sectionTitleStyle}>{__('Color')}</div>

                <label style={labelStyle}>{__('Line Color')}</label>
                <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                    <input
                        type="color"
                        value={props.color || '#e5e7eb'}
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
                        value={props.color || '#e5e7eb'}
                        onChange={(e) => handleChange('color', e.target.value)}
                        className="form-control"
                        style={{ flex: 1 }}
                    />
                </div>
            </div>
        </div>
    );
};

export default DividerBlockEditor;
