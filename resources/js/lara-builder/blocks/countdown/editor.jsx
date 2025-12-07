import React from 'react';
import { __ } from '@lara-builder/i18n';

const CountdownEditor = ({ props, updateProps }) => {
    // Calculate default date (7 days from now)
    const getDefaultDate = () => {
        const date = new Date();
        date.setDate(date.getDate() + 7);
        return date.toISOString().split('T')[0];
    };

    // Ensure we have a default date if none is set
    const currentDate = props.targetDate || getDefaultDate();

    const handleChange = (field, value) => {
        updateProps({ [field]: value });
    };

    const inputStyle = {
        width: '100%',
        padding: '8px 12px',
        border: '1px solid #e2e8f0',
        borderRadius: '6px',
        fontSize: '14px',
        outline: 'none',
        transition: 'border-color 0.2s',
    };

    const labelStyle = {
        display: 'block',
        marginBottom: '6px',
        fontSize: '13px',
        fontWeight: '500',
        color: '#475569',
    };

    const fieldGroupStyle = {
        marginBottom: '16px',
    };

    const helpTextStyle = {
        marginTop: '4px',
        fontSize: '12px',
        color: '#64748b',
    };

    const colorInputWrapperStyle = {
        display: 'flex',
        gap: '8px',
        alignItems: 'center',
    };

    const colorPreviewStyle = (color) => ({
        width: '40px',
        height: '40px',
        borderRadius: '6px',
        border: '2px solid #e2e8f0',
        backgroundColor: color,
        cursor: 'pointer',
    });

    const selectStyle = {
        ...inputStyle,
        cursor: 'pointer',
    };

    return (
        <div style={{ padding: '16px' }}>
            <h3 style={{ marginTop: 0, marginBottom: '16px', fontSize: '16px', fontWeight: '600', color: '#1e293b' }}>
                {__('Countdown Settings')}
            </h3>

            <div style={fieldGroupStyle}>
                <label style={labelStyle}>{__('Target Date')} *</label>
                <input
                    type="date"
                    value={currentDate}
                    onChange={(e) => handleChange('targetDate', e.target.value)}
                    style={inputStyle}
                    required
                />
                <div style={helpTextStyle}>{__('The date when the countdown ends')}</div>
            </div>

            <div style={fieldGroupStyle}>
                <label style={labelStyle}>{__('Target Time')}</label>
                <input
                    type="time"
                    value={props.targetTime || '23:59'}
                    onChange={(e) => handleChange('targetTime', e.target.value)}
                    style={inputStyle}
                />
                <div style={helpTextStyle}>{__('The time when the countdown ends')}</div>
            </div>

            <div style={fieldGroupStyle}>
                <label style={labelStyle}>{__('Title')}</label>
                <input
                    type="text"
                    value={props.title || ''}
                    onChange={(e) => handleChange('title', e.target.value)}
                    placeholder="Sale Ends In"
                    style={inputStyle}
                />
            </div>

            <div style={fieldGroupStyle}>
                <label style={labelStyle}>{__('Background Color')}</label>
                <div style={colorInputWrapperStyle}>
                    <input
                        type="color"
                        value={props.backgroundColor || '#1e293b'}
                        onChange={(e) => handleChange('backgroundColor', e.target.value)}
                        style={{ display: 'none' }}
                        id="backgroundColor"
                    />
                    <label htmlFor="backgroundColor" style={colorPreviewStyle(props.backgroundColor || '#1e293b')} />
                    <input
                        type="text"
                        value={props.backgroundColor || '#1e293b'}
                        onChange={(e) => handleChange('backgroundColor', e.target.value)}
                        style={{ ...inputStyle, flex: 1 }}
                        placeholder="#1e293b"
                    />
                </div>
            </div>

            <div style={fieldGroupStyle}>
                <label style={labelStyle}>{__('Text Color')}</label>
                <div style={colorInputWrapperStyle}>
                    <input
                        type="color"
                        value={props.textColor || '#ffffff'}
                        onChange={(e) => handleChange('textColor', e.target.value)}
                        style={{ display: 'none' }}
                        id="textColor"
                    />
                    <label htmlFor="textColor" style={colorPreviewStyle(props.textColor || '#ffffff')} />
                    <input
                        type="text"
                        value={props.textColor || '#ffffff'}
                        onChange={(e) => handleChange('textColor', e.target.value)}
                        style={{ ...inputStyle, flex: 1 }}
                        placeholder="#ffffff"
                    />
                </div>
            </div>

            <div style={fieldGroupStyle}>
                <label style={labelStyle}>{__('Number Color')}</label>
                <div style={colorInputWrapperStyle}>
                    <input
                        type="color"
                        value={props.numberColor || '#635bff'}
                        onChange={(e) => handleChange('numberColor', e.target.value)}
                        style={{ display: 'none' }}
                        id="numberColor"
                    />
                    <label htmlFor="numberColor" style={colorPreviewStyle(props.numberColor || '#635bff')} />
                    <input
                        type="text"
                        value={props.numberColor || '#635bff'}
                        onChange={(e) => handleChange('numberColor', e.target.value)}
                        style={{ ...inputStyle, flex: 1 }}
                        placeholder="#635bff"
                    />
                </div>
            </div>

            <div style={fieldGroupStyle}>
                <label style={labelStyle}>{__('Alignment')}</label>
                <select
                    value={props.align || 'center'}
                    onChange={(e) => handleChange('align', e.target.value)}
                    style={selectStyle}
                >
                    <option value="left">{__('Left')}</option>
                    <option value="center">{__('Center')}</option>
                    <option value="right">{__('Right')}</option>
                </select>
            </div>

            <div style={fieldGroupStyle}>
                <label style={labelStyle}>{__('Expired Message')}</label>
                <input
                    type="text"
                    value={props.expiredMessage || ''}
                    onChange={(e) => handleChange('expiredMessage', e.target.value)}
                    placeholder="This offer has expired!"
                    style={inputStyle}
                />
                <div style={helpTextStyle}>{__('Message to show when countdown expires')}</div>
            </div>
        </div>
    );
};

export default CountdownEditor;
