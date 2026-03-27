/**
 * Star Rating Block - Property Editor
 *
 * Renders the property fields for the star rating block in the properties panel.
 */

import { __ } from '@lara-builder/i18n';

const StarRatingBlockEditor = ({ props, onUpdate }) => {
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

    const colorInputContainerStyle = {
        display: 'flex',
        alignItems: 'center',
        gap: '8px',
        marginBottom: '12px',
    };

    const colorPickerStyle = {
        width: '40px',
        height: '36px',
        padding: '2px',
        border: '1px solid #d1d5db',
        borderRadius: '4px',
        cursor: 'pointer',
    };

    const sizeButtonStyle = (isActive) => ({
        flex: 1,
        padding: '8px',
        border: isActive ? '2px solid var(--color-primary, #635bff)' : '1px solid #d1d5db',
        borderRadius: '6px',
        backgroundColor: isActive ? '#f0f0ff' : '#ffffff',
        color: isActive ? 'var(--color-primary, #635bff)' : '#374151',
        fontWeight: isActive ? '600' : '400',
        fontSize: '13px',
        cursor: 'pointer',
        textAlign: 'center',
    });

    return (
        <div>
            {/* Rating Section */}
            <div style={sectionStyle}>
                <div style={sectionTitleStyle}>{__('Rating')}</div>

                <label style={labelStyle}>{__('Rating Value')}</label>
                <div style={{ display: 'flex', alignItems: 'center', gap: '12px', marginBottom: '12px' }}>
                    <input
                        type="range"
                        min="0"
                        max={props.maxStars || 5}
                        step="0.5"
                        value={props.rating ?? 5}
                        onChange={(e) => handleChange('rating', parseFloat(e.target.value))}
                        style={{ flex: 1 }}
                    />
                    <span style={{ fontSize: '14px', fontWeight: '600', color: '#374151', minWidth: '32px', textAlign: 'center' }}>
                        {props.rating ?? 5}
                    </span>
                </div>

                <label style={labelStyle}>{__('Maximum Stars')}</label>
                <input
                    type="number"
                    value={props.maxStars || 5}
                    onChange={(e) => handleChange('maxStars', Math.max(1, Math.min(10, parseInt(e.target.value) || 5)))}
                    className="form-control"
                    min="1"
                    max="10"
                    step="1"
                />
            </div>

            {/* Size Section */}
            <div style={sectionStyle}>
                <div style={sectionTitleStyle}>{__('Size')}</div>

                <div style={{ display: 'flex', gap: '8px' }}>
                    <button
                        type="button"
                        onClick={() => handleChange('size', 'sm')}
                        style={sizeButtonStyle(props.size === 'sm')}
                    >
                        {__('Small')}
                    </button>
                    <button
                        type="button"
                        onClick={() => handleChange('size', 'md')}
                        style={sizeButtonStyle((props.size || 'md') === 'md')}
                    >
                        {__('Medium')}
                    </button>
                    <button
                        type="button"
                        onClick={() => handleChange('size', 'lg')}
                        style={sizeButtonStyle(props.size === 'lg')}
                    >
                        {__('Large')}
                    </button>
                </div>
            </div>

            {/* Colors Section */}
            <div style={sectionStyle}>
                <div style={sectionTitleStyle}>{__('Colors')}</div>

                <label style={labelStyle}>{__('Filled Star Color')}</label>
                <div style={colorInputContainerStyle}>
                    <input
                        type="color"
                        value={props.filledColor || '#fbbf24'}
                        onChange={(e) => handleChange('filledColor', e.target.value)}
                        style={colorPickerStyle}
                    />
                    <input
                        type="text"
                        value={props.filledColor || '#fbbf24'}
                        onChange={(e) => handleChange('filledColor', e.target.value)}
                        className="form-control"
                        style={{ flex: 1 }}
                        placeholder="#fbbf24"
                    />
                </div>

                <label style={labelStyle}>{__('Empty Star Color')}</label>
                <div style={colorInputContainerStyle}>
                    <input
                        type="color"
                        value={props.emptyColor || '#d1d5db'}
                        onChange={(e) => handleChange('emptyColor', e.target.value)}
                        style={colorPickerStyle}
                    />
                    <input
                        type="text"
                        value={props.emptyColor || '#d1d5db'}
                        onChange={(e) => handleChange('emptyColor', e.target.value)}
                        className="form-control"
                        style={{ flex: 1 }}
                        placeholder="#d1d5db"
                    />
                </div>
            </div>

            {/* Label Section */}
            <div style={sectionStyle}>
                <div style={sectionTitleStyle}>{__('Label')}</div>

                <label style={{ ...labelStyle, display: 'flex', alignItems: 'center', gap: '8px', cursor: 'pointer' }}>
                    <input
                        type="checkbox"
                        checked={props.showLabel || false}
                        onChange={(e) => handleChange('showLabel', e.target.checked)}
                        style={{ cursor: 'pointer' }}
                    />
                    <span>{__('Show Label')}</span>
                </label>

                {props.showLabel && (
                    <div style={{ marginTop: '12px' }}>
                        <label style={labelStyle}>{__('Label Text')}</label>
                        <input
                            type="text"
                            value={props.labelText || ''}
                            onChange={(e) => handleChange('labelText', e.target.value)}
                            className="form-control"
                            placeholder="e.g. 5 out of 5"
                        />
                    </div>
                )}
            </div>

            {/* Alignment Section */}
            <div style={sectionStyle}>
                <div style={sectionTitleStyle}>{__('Alignment')}</div>

                <select
                    value={props.align || 'center'}
                    onChange={(e) => handleChange('align', e.target.value)}
                    className="form-control"
                >
                    <option value="left">{__('Left')}</option>
                    <option value="center">{__('Center')}</option>
                    <option value="right">{__('Right')}</option>
                </select>
            </div>
        </div>
    );
};

export default StarRatingBlockEditor;
