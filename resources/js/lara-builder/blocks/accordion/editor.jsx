/**
 * Accordion Block - Property Editor
 *
 * Renders the property fields for the accordion block in the properties panel.
 */

import { __ } from '@lara-builder/i18n';

const AccordionBlockEditor = ({ props, onUpdate }) => {
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

    return (
        <div>
            {/* Behavior Section */}
            <div style={sectionStyle}>
                <div style={sectionTitleStyle}>{__('Behavior')}</div>

                <label style={{ ...labelStyle, display: 'flex', alignItems: 'center', gap: '8px', cursor: 'pointer' }}>
                    <input
                        type="checkbox"
                        checked={props.independentToggle || false}
                        onChange={(e) => handleChange('independentToggle', e.target.checked)}
                        style={{ cursor: 'pointer' }}
                    />
                    <span>{__('Allow Multiple Items Open')}</span>
                </label>

                <div style={{ marginTop: '12px' }}>
                    <label style={labelStyle}>{__('Transition Duration (ms)')}</label>
                    <input
                        type="number"
                        value={props.transitionDuration || 200}
                        onChange={(e) => handleChange('transitionDuration', parseInt(e.target.value) || 200)}
                        className="form-control"
                        min="0"
                        max="1000"
                        step="50"
                    />
                </div>
            </div>

            {/* Header Section */}
            <div style={sectionStyle}>
                <div style={sectionTitleStyle}>{__('Header Styling')}</div>

                <label style={labelStyle}>{__('Background Color')}</label>
                <div style={colorInputContainerStyle}>
                    <input
                        type="color"
                        value={props.headerBgColor || '#ffffff'}
                        onChange={(e) => handleChange('headerBgColor', e.target.value)}
                        style={colorPickerStyle}
                    />
                    <input
                        type="text"
                        value={props.headerBgColor || '#ffffff'}
                        onChange={(e) => handleChange('headerBgColor', e.target.value)}
                        className="form-control"
                        style={{ flex: 1 }}
                        placeholder="#ffffff"
                    />
                </div>

                <label style={labelStyle}>{__('Active Background Color')}</label>
                <div style={colorInputContainerStyle}>
                    <input
                        type="color"
                        value={props.headerBgColorActive || '#f9fafb'}
                        onChange={(e) => handleChange('headerBgColorActive', e.target.value)}
                        style={colorPickerStyle}
                    />
                    <input
                        type="text"
                        value={props.headerBgColorActive || '#f9fafb'}
                        onChange={(e) => handleChange('headerBgColorActive', e.target.value)}
                        className="form-control"
                        style={{ flex: 1 }}
                        placeholder="#f9fafb"
                    />
                </div>

                <label style={labelStyle}>{__('Padding')}</label>
                <input
                    type="text"
                    value={props.headerPadding || '16px'}
                    onChange={(e) => handleChange('headerPadding', e.target.value)}
                    className="form-control"
                    placeholder="16px"
                />
            </div>

            {/* Title Section */}
            <div style={sectionStyle}>
                <div style={sectionTitleStyle}>{__('Title Styling')}</div>

                <label style={labelStyle}>{__('Color')}</label>
                <div style={colorInputContainerStyle}>
                    <input
                        type="color"
                        value={props.titleColor || '#1f2937'}
                        onChange={(e) => handleChange('titleColor', e.target.value)}
                        style={colorPickerStyle}
                    />
                    <input
                        type="text"
                        value={props.titleColor || '#1f2937'}
                        onChange={(e) => handleChange('titleColor', e.target.value)}
                        className="form-control"
                        style={{ flex: 1 }}
                        placeholder="#1f2937"
                    />
                </div>

                <label style={labelStyle}>{__('Font Size')}</label>
                <select
                    value={props.titleFontSize || '16px'}
                    onChange={(e) => handleChange('titleFontSize', e.target.value)}
                    className="form-control"
                    style={{ marginBottom: '12px' }}
                >
                    <option value="12px">12px</option>
                    <option value="14px">14px</option>
                    <option value="16px">16px</option>
                    <option value="18px">18px</option>
                    <option value="20px">20px</option>
                    <option value="24px">24px</option>
                </select>

                <label style={labelStyle}>{__('Font Weight')}</label>
                <select
                    value={props.titleFontWeight || '600'}
                    onChange={(e) => handleChange('titleFontWeight', e.target.value)}
                    className="form-control"
                >
                    <option value="400">{__('Normal')} (400)</option>
                    <option value="500">{__('Medium')} (500)</option>
                    <option value="600">{__('Semi-bold')} (600)</option>
                    <option value="700">{__('Bold')} (700)</option>
                </select>
            </div>

            {/* Content Section */}
            <div style={sectionStyle}>
                <div style={sectionTitleStyle}>{__('Content Styling')}</div>

                <label style={labelStyle}>{__('Background Color')}</label>
                <div style={colorInputContainerStyle}>
                    <input
                        type="color"
                        value={props.contentBgColor || '#ffffff'}
                        onChange={(e) => handleChange('contentBgColor', e.target.value)}
                        style={colorPickerStyle}
                    />
                    <input
                        type="text"
                        value={props.contentBgColor || '#ffffff'}
                        onChange={(e) => handleChange('contentBgColor', e.target.value)}
                        className="form-control"
                        style={{ flex: 1 }}
                        placeholder="#ffffff"
                    />
                </div>

                <label style={labelStyle}>{__('Text Color')}</label>
                <div style={colorInputContainerStyle}>
                    <input
                        type="color"
                        value={props.contentColor || '#4b5563'}
                        onChange={(e) => handleChange('contentColor', e.target.value)}
                        style={colorPickerStyle}
                    />
                    <input
                        type="text"
                        value={props.contentColor || '#4b5563'}
                        onChange={(e) => handleChange('contentColor', e.target.value)}
                        className="form-control"
                        style={{ flex: 1 }}
                        placeholder="#4b5563"
                    />
                </div>

                <label style={labelStyle}>{__('Font Size')}</label>
                <select
                    value={props.contentFontSize || '14px'}
                    onChange={(e) => handleChange('contentFontSize', e.target.value)}
                    className="form-control"
                    style={{ marginBottom: '12px' }}
                >
                    <option value="12px">12px</option>
                    <option value="14px">14px</option>
                    <option value="16px">16px</option>
                    <option value="18px">18px</option>
                </select>

                <label style={labelStyle}>{__('Padding')}</label>
                <input
                    type="text"
                    value={props.contentPadding || '16px'}
                    onChange={(e) => handleChange('contentPadding', e.target.value)}
                    className="form-control"
                    placeholder="16px"
                />
            </div>

            {/* Icon Section */}
            <div style={sectionStyle}>
                <div style={sectionTitleStyle}>{__('Icon')}</div>

                <label style={labelStyle}>{__('Color')}</label>
                <div style={colorInputContainerStyle}>
                    <input
                        type="color"
                        value={props.iconColor || '#6b7280'}
                        onChange={(e) => handleChange('iconColor', e.target.value)}
                        style={colorPickerStyle}
                    />
                    <input
                        type="text"
                        value={props.iconColor || '#6b7280'}
                        onChange={(e) => handleChange('iconColor', e.target.value)}
                        className="form-control"
                        style={{ flex: 1 }}
                        placeholder="#6b7280"
                    />
                </div>

                <label style={labelStyle}>{__('Position')}</label>
                <select
                    value={props.iconPosition || 'right'}
                    onChange={(e) => handleChange('iconPosition', e.target.value)}
                    className="form-control"
                >
                    <option value="left">{__('Left')}</option>
                    <option value="right">{__('Right')}</option>
                </select>
            </div>

            {/* Border Section */}
            <div style={sectionStyle}>
                <div style={sectionTitleStyle}>{__('Border')}</div>

                <label style={labelStyle}>{__('Color')}</label>
                <div style={colorInputContainerStyle}>
                    <input
                        type="color"
                        value={props.borderColor || '#e5e7eb'}
                        onChange={(e) => handleChange('borderColor', e.target.value)}
                        style={colorPickerStyle}
                    />
                    <input
                        type="text"
                        value={props.borderColor || '#e5e7eb'}
                        onChange={(e) => handleChange('borderColor', e.target.value)}
                        className="form-control"
                        style={{ flex: 1 }}
                        placeholder="#e5e7eb"
                    />
                </div>

                <label style={labelStyle}>{__('Border Radius')}</label>
                <input
                    type="text"
                    value={props.borderRadius || '8px'}
                    onChange={(e) => handleChange('borderRadius', e.target.value)}
                    className="form-control"
                    placeholder="8px"
                />
            </div>

            {/* Info Section */}
            <div style={{
                padding: '12px',
                backgroundColor: '#f3f4f6',
                borderRadius: '6px',
                fontSize: '12px',
                color: '#6b7280',
                lineHeight: '1.5',
            }}>
                <strong style={{ color: '#374151' }}>{__('Tip:')}</strong> {__('Double-click on titles or content in the canvas to edit them directly. Use the "Add Accordion Item" button to add more items.')}
            </div>
        </div>
    );
};

export default AccordionBlockEditor;
