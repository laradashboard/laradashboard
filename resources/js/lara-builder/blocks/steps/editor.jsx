/**
 * Steps Block - Property Editor
 *
 * Renders the property fields for the steps block in the properties panel.
 */

import { useState } from 'react';
import { __ } from '@lara-builder/i18n';

const StepsBlockEditor = ({ props, onUpdate }) => {
    const [expandedStep, setExpandedStep] = useState(0);

    const handleChange = (field, value) => {
        onUpdate({ ...props, [field]: value });
    };

    const steps = props.steps || [];

    const updateStep = (index, field, value) => {
        const newSteps = [...steps];
        newSteps[index] = { ...newSteps[index], [field]: value };
        onUpdate({ ...props, steps: newSteps });
    };

    const addStep = () => {
        const newSteps = [...steps, {
            title: `Step ${steps.length + 1}`,
            description: 'Description for this step.',
            code: '',
            linkText: '',
            linkUrl: '',
        }];
        onUpdate({ ...props, steps: newSteps });
        setExpandedStep(newSteps.length - 1);
    };

    const removeStep = (index) => {
        if (steps.length <= 1) return;
        const newSteps = steps.filter((_, i) => i !== index);
        onUpdate({ ...props, steps: newSteps });
        setExpandedStep(Math.max(0, index - 1));
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

    const stepHeaderStyle = (isExpanded) => ({
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'space-between',
        padding: '10px 12px',
        backgroundColor: isExpanded ? '#f3f4f6' : '#f9fafb',
        border: '1px solid #e5e7eb',
        borderRadius: isExpanded ? '8px 8px 0 0' : '8px',
        cursor: 'pointer',
        fontSize: '13px',
        fontWeight: '500',
        color: '#374151',
    });

    const stepContentStyle = {
        padding: '12px',
        border: '1px solid #e5e7eb',
        borderTop: 'none',
        borderRadius: '0 0 8px 8px',
        backgroundColor: '#ffffff',
    };

    return (
        <div>
            {/* Steps Items Section */}
            <div style={sectionStyle}>
                <div style={sectionTitleStyle}>{__('Steps')}</div>

                {steps.map((step, index) => (
                    <div key={index} style={{ marginBottom: '8px' }}>
                        <div
                            style={stepHeaderStyle(expandedStep === index)}
                            onClick={() => setExpandedStep(expandedStep === index ? -1 : index)}
                        >
                            <span>{step.title || `Step ${index + 1}`}</span>
                            <div style={{ display: 'flex', gap: '4px', alignItems: 'center' }}>
                                {steps.length > 1 && (
                                    <button
                                        type="button"
                                        onClick={(e) => { e.stopPropagation(); removeStep(index); }}
                                        style={{
                                            padding: '2px 6px',
                                            border: 'none',
                                            background: 'none',
                                            cursor: 'pointer',
                                            color: '#ef4444',
                                            fontSize: '16px',
                                        }}
                                        title="Remove step"
                                    >
                                        &times;
                                    </button>
                                )}
                                <span style={{ fontSize: '10px', color: '#9ca3af' }}>
                                    {expandedStep === index ? '\u25B2' : '\u25BC'}
                                </span>
                            </div>
                        </div>

                        {expandedStep === index && (
                            <div style={stepContentStyle}>
                                <label style={labelStyle}>{__('Title')}</label>
                                <input
                                    type="text"
                                    value={step.title || ''}
                                    onChange={(e) => updateStep(index, 'title', e.target.value)}
                                    className="form-control"
                                    placeholder="Step title"
                                    style={{ marginBottom: '10px' }}
                                />

                                <label style={labelStyle}>{__('Description')}</label>
                                <textarea
                                    value={step.description || ''}
                                    onChange={(e) => updateStep(index, 'description', e.target.value)}
                                    className="form-control"
                                    placeholder="Step description"
                                    rows={3}
                                    style={{ marginBottom: '10px' }}
                                />

                                <label style={labelStyle}>{__('Code (optional)')}</label>
                                <input
                                    type="text"
                                    value={step.code || ''}
                                    onChange={(e) => updateStep(index, 'code', e.target.value)}
                                    className="form-control"
                                    placeholder="e.g. npm install my-package"
                                    style={{ marginBottom: '10px', fontFamily: 'monospace' }}
                                />

                                <label style={labelStyle}>{__('Link Text (optional)')}</label>
                                <input
                                    type="text"
                                    value={step.linkText || ''}
                                    onChange={(e) => updateStep(index, 'linkText', e.target.value)}
                                    className="form-control"
                                    placeholder="e.g. Learn more"
                                    style={{ marginBottom: '10px' }}
                                />

                                <label style={labelStyle}>{__('Link URL (optional)')}</label>
                                <input
                                    type="text"
                                    value={step.linkUrl || ''}
                                    onChange={(e) => updateStep(index, 'linkUrl', e.target.value)}
                                    className="form-control"
                                    placeholder="https://example.com"
                                />
                            </div>
                        )}
                    </div>
                ))}

                <button
                    type="button"
                    onClick={addStep}
                    style={{
                        display: 'flex',
                        alignItems: 'center',
                        gap: '6px',
                        marginTop: '8px',
                        padding: '8px 16px',
                        backgroundColor: 'var(--color-primary, #635bff)',
                        color: 'white',
                        border: 'none',
                        borderRadius: '6px',
                        fontSize: '13px',
                        fontWeight: '500',
                        cursor: 'pointer',
                        width: '100%',
                        justifyContent: 'center',
                    }}
                >
                    + {__('Add Step')}
                </button>
            </div>

            {/* Layout Section */}
            <div style={sectionStyle}>
                <div style={sectionTitleStyle}>{__('Layout')}</div>

                <label style={labelStyle}>{__('Layout')}</label>
                <select
                    value={props.layout || 'vertical'}
                    onChange={(e) => handleChange('layout', e.target.value)}
                    className="form-control"
                    style={{ marginBottom: '12px' }}
                >
                    <option value="vertical">{__('Vertical')}</option>
                    <option value="horizontal">{__('Horizontal')}</option>
                </select>

                <label style={{ ...labelStyle, display: 'flex', alignItems: 'center', gap: '8px', cursor: 'pointer' }}>
                    <input
                        type="checkbox"
                        checked={props.showNumbers !== false}
                        onChange={(e) => handleChange('showNumbers', e.target.checked)}
                        style={{ cursor: 'pointer' }}
                    />
                    <span>{__('Show Numbers')}</span>
                </label>

                <label style={{ ...labelStyle, display: 'flex', alignItems: 'center', gap: '8px', cursor: 'pointer', marginTop: '8px' }}>
                    <input
                        type="checkbox"
                        checked={props.showConnector !== false}
                        onChange={(e) => handleChange('showConnector', e.target.checked)}
                        style={{ cursor: 'pointer' }}
                    />
                    <span>{__('Show Connector')}</span>
                </label>
            </div>

            {/* Number Styling Section */}
            <div style={sectionStyle}>
                <div style={sectionTitleStyle}>{__('Number Circle')}</div>

                <label style={labelStyle}>{__('Number Color')}</label>
                <div style={colorInputContainerStyle}>
                    <input
                        type="color"
                        value={props.numberColor || '#ffffff'}
                        onChange={(e) => handleChange('numberColor', e.target.value)}
                        style={colorPickerStyle}
                    />
                    <input
                        type="text"
                        value={props.numberColor || '#ffffff'}
                        onChange={(e) => handleChange('numberColor', e.target.value)}
                        className="form-control"
                        style={{ flex: 1 }}
                        placeholder="#ffffff"
                    />
                </div>

                <label style={labelStyle}>{__('Background Color')}</label>
                <div style={colorInputContainerStyle}>
                    <input
                        type="color"
                        value={props.numberBgColor || '#3b82f6'}
                        onChange={(e) => handleChange('numberBgColor', e.target.value)}
                        style={colorPickerStyle}
                    />
                    <input
                        type="text"
                        value={props.numberBgColor || '#3b82f6'}
                        onChange={(e) => handleChange('numberBgColor', e.target.value)}
                        className="form-control"
                        style={{ flex: 1 }}
                        placeholder="#3b82f6"
                    />
                </div>
            </div>

            {/* Text Styling Section */}
            <div style={sectionStyle}>
                <div style={sectionTitleStyle}>{__('Text Styling')}</div>

                <label style={labelStyle}>{__('Title Color')}</label>
                <div style={colorInputContainerStyle}>
                    <input
                        type="color"
                        value={props.titleColor || '#111827'}
                        onChange={(e) => handleChange('titleColor', e.target.value)}
                        style={colorPickerStyle}
                    />
                    <input
                        type="text"
                        value={props.titleColor || '#111827'}
                        onChange={(e) => handleChange('titleColor', e.target.value)}
                        className="form-control"
                        style={{ flex: 1 }}
                        placeholder="#111827"
                    />
                </div>

                <label style={labelStyle}>{__('Title Size')}</label>
                <select
                    value={props.titleSize || '20px'}
                    onChange={(e) => handleChange('titleSize', e.target.value)}
                    className="form-control"
                    style={{ marginBottom: '12px' }}
                >
                    <option value="16px">16px</option>
                    <option value="18px">18px</option>
                    <option value="20px">20px</option>
                    <option value="24px">24px</option>
                    <option value="28px">28px</option>
                </select>

                <label style={labelStyle}>{__('Description Color')}</label>
                <div style={colorInputContainerStyle}>
                    <input
                        type="color"
                        value={props.descriptionColor || '#6b7280'}
                        onChange={(e) => handleChange('descriptionColor', e.target.value)}
                        style={colorPickerStyle}
                    />
                    <input
                        type="text"
                        value={props.descriptionColor || '#6b7280'}
                        onChange={(e) => handleChange('descriptionColor', e.target.value)}
                        className="form-control"
                        style={{ flex: 1 }}
                        placeholder="#6b7280"
                    />
                </div>

                <label style={labelStyle}>{__('Description Size')}</label>
                <select
                    value={props.descriptionSize || '14px'}
                    onChange={(e) => handleChange('descriptionSize', e.target.value)}
                    className="form-control"
                >
                    <option value="12px">12px</option>
                    <option value="14px">14px</option>
                    <option value="16px">16px</option>
                    <option value="18px">18px</option>
                </select>
            </div>

            {/* Connector Section */}
            <div style={sectionStyle}>
                <div style={sectionTitleStyle}>{__('Connector')}</div>

                <label style={labelStyle}>{__('Connector Color')}</label>
                <div style={colorInputContainerStyle}>
                    <input
                        type="color"
                        value={props.connectorColor || '#e5e7eb'}
                        onChange={(e) => handleChange('connectorColor', e.target.value)}
                        style={colorPickerStyle}
                    />
                    <input
                        type="text"
                        value={props.connectorColor || '#e5e7eb'}
                        onChange={(e) => handleChange('connectorColor', e.target.value)}
                        className="form-control"
                        style={{ flex: 1 }}
                        placeholder="#e5e7eb"
                    />
                </div>
            </div>

            {/* Code Block Section */}
            <div style={sectionStyle}>
                <div style={sectionTitleStyle}>{__('Code Block')}</div>

                <label style={labelStyle}>{__('Background Color')}</label>
                <div style={colorInputContainerStyle}>
                    <input
                        type="color"
                        value={props.codeBackgroundColor || '#1f2937'}
                        onChange={(e) => handleChange('codeBackgroundColor', e.target.value)}
                        style={colorPickerStyle}
                    />
                    <input
                        type="text"
                        value={props.codeBackgroundColor || '#1f2937'}
                        onChange={(e) => handleChange('codeBackgroundColor', e.target.value)}
                        className="form-control"
                        style={{ flex: 1 }}
                        placeholder="#1f2937"
                    />
                </div>

                <label style={labelStyle}>{__('Text Color')}</label>
                <div style={colorInputContainerStyle}>
                    <input
                        type="color"
                        value={props.codeTextColor || '#e5e7eb'}
                        onChange={(e) => handleChange('codeTextColor', e.target.value)}
                        style={colorPickerStyle}
                    />
                    <input
                        type="text"
                        value={props.codeTextColor || '#e5e7eb'}
                        onChange={(e) => handleChange('codeTextColor', e.target.value)}
                        className="form-control"
                        style={{ flex: 1 }}
                        placeholder="#e5e7eb"
                    />
                </div>
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
                <strong style={{ color: '#374151' }}>{__('Tip:')}</strong> {__('Each step can have an optional code snippet and link. Use vertical layout for detailed steps and horizontal for a compact process overview.')}
            </div>
        </div>
    );
};

export default StepsBlockEditor;
