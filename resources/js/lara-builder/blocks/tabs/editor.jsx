/**
 * Tabs Block - Property Editor
 *
 * Renders the property fields for the tabs block in the properties panel.
 */

import { useState } from 'react';
import { __ } from '@lara-builder/i18n';

const TabsBlockEditor = ({ props, onUpdate }) => {
    const [expandedTab, setExpandedTab] = useState(0);

    const handleChange = (field, value) => {
        onUpdate({ ...props, [field]: value });
    };

    const tabs = props.tabs || [];

    const updateTab = (index, field, value) => {
        const newTabs = [...tabs];
        newTabs[index] = { ...newTabs[index], [field]: value };
        onUpdate({ ...props, tabs: newTabs });
    };

    const addTab = () => {
        const newTabs = [...tabs, {
            label: `Tab ${tabs.length + 1}`,
            heading: `Tab ${tabs.length + 1} Heading`,
            description: 'New tab content.',
            items: [],
            badges: [],
        }];
        onUpdate({ ...props, tabs: newTabs });
        setExpandedTab(newTabs.length - 1);
    };

    const removeTab = (index) => {
        if (tabs.length <= 1) return;
        const newTabs = tabs.filter((_, i) => i !== index);
        onUpdate({ ...props, tabs: newTabs, activeTab: 0 });
        setExpandedTab(Math.max(0, index - 1));
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

    const tabHeaderStyle = (isExpanded) => ({
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

    const tabContentStyle = {
        padding: '12px',
        border: '1px solid #e5e7eb',
        borderTop: 'none',
        borderRadius: '0 0 8px 8px',
        backgroundColor: '#ffffff',
    };

    return (
        <div>
            {/* Tab Items Section */}
            <div style={sectionStyle}>
                <div style={sectionTitleStyle}>{__('Tab Items')}</div>

                {tabs.map((tab, index) => (
                    <div key={index} style={{ marginBottom: '8px' }}>
                        <div
                            style={tabHeaderStyle(expandedTab === index)}
                            onClick={() => setExpandedTab(expandedTab === index ? -1 : index)}
                        >
                            <span>{tab.label || `Tab ${index + 1}`}</span>
                            <div style={{ display: 'flex', gap: '4px', alignItems: 'center' }}>
                                {tabs.length > 1 && (
                                    <button
                                        type="button"
                                        onClick={(e) => { e.stopPropagation(); removeTab(index); }}
                                        style={{
                                            padding: '2px 6px',
                                            border: 'none',
                                            background: 'none',
                                            cursor: 'pointer',
                                            color: '#ef4444',
                                            fontSize: '16px',
                                        }}
                                        title="Remove tab"
                                    >
                                        &times;
                                    </button>
                                )}
                                <span style={{ fontSize: '10px', color: '#9ca3af' }}>
                                    {expandedTab === index ? '\u25B2' : '\u25BC'}
                                </span>
                            </div>
                        </div>

                        {expandedTab === index && (
                            <div style={tabContentStyle}>
                                <label style={labelStyle}>{__('Label')}</label>
                                <input
                                    type="text"
                                    value={tab.label || ''}
                                    onChange={(e) => updateTab(index, 'label', e.target.value)}
                                    className="form-control"
                                    placeholder="Tab label"
                                    style={{ marginBottom: '10px' }}
                                />

                                <label style={labelStyle}>{__('Heading')}</label>
                                <input
                                    type="text"
                                    value={tab.heading || ''}
                                    onChange={(e) => updateTab(index, 'heading', e.target.value)}
                                    className="form-control"
                                    placeholder="Tab heading"
                                    style={{ marginBottom: '10px' }}
                                />

                                <label style={labelStyle}>{__('Description')}</label>
                                <textarea
                                    value={tab.description || ''}
                                    onChange={(e) => updateTab(index, 'description', e.target.value)}
                                    className="form-control"
                                    placeholder="Tab description"
                                    rows={3}
                                    style={{ marginBottom: '10px' }}
                                />

                                <label style={labelStyle}>{__('Items (one per line)')}</label>
                                <textarea
                                    value={(tab.items || []).join('\n')}
                                    onChange={(e) => updateTab(index, 'items', e.target.value.split('\n').filter(s => s.trim()))}
                                    className="form-control"
                                    placeholder="Feature one&#10;Feature two&#10;Feature three"
                                    rows={3}
                                    style={{ marginBottom: '10px' }}
                                />

                                <label style={labelStyle}>{__('Badges (comma-separated)')}</label>
                                <input
                                    type="text"
                                    value={(tab.badges || []).join(', ')}
                                    onChange={(e) => updateTab(index, 'badges', e.target.value.split(',').map(s => s.trim()).filter(Boolean))}
                                    className="form-control"
                                    placeholder="Fast, Easy, New"
                                />
                            </div>
                        )}
                    </div>
                ))}

                <button
                    type="button"
                    onClick={addTab}
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
                    + {__('Add Tab')}
                </button>
            </div>

            {/* Style Section */}
            <div style={sectionStyle}>
                <div style={sectionTitleStyle}>{__('Tab Style')}</div>

                <label style={labelStyle}>{__('Style')}</label>
                <select
                    value={props.tabStyle || 'pills'}
                    onChange={(e) => handleChange('tabStyle', e.target.value)}
                    className="form-control"
                    style={{ marginBottom: '12px' }}
                >
                    <option value="pills">{__('Pills')}</option>
                    <option value="underline">{__('Underline')}</option>
                    <option value="buttons">{__('Buttons')}</option>
                </select>

                <label style={labelStyle}>{__('Alignment')}</label>
                <select
                    value={props.tabAlignment || 'center'}
                    onChange={(e) => handleChange('tabAlignment', e.target.value)}
                    className="form-control"
                    style={{ marginBottom: '12px' }}
                >
                    <option value="left">{__('Left')}</option>
                    <option value="center">{__('Center')}</option>
                    <option value="stretch">{__('Stretch')}</option>
                </select>
            </div>

            {/* Colors Section */}
            <div style={sectionStyle}>
                <div style={sectionTitleStyle}>{__('Colors')}</div>

                <label style={labelStyle}>{__('Accent Color')}</label>
                <div style={colorInputContainerStyle}>
                    <input
                        type="color"
                        value={props.accentColor || '#3b82f6'}
                        onChange={(e) => handleChange('accentColor', e.target.value)}
                        style={colorPickerStyle}
                    />
                    <input
                        type="text"
                        value={props.accentColor || '#3b82f6'}
                        onChange={(e) => handleChange('accentColor', e.target.value)}
                        className="form-control"
                        style={{ flex: 1 }}
                        placeholder="#3b82f6"
                    />
                </div>

                <label style={labelStyle}>{__('Background Color')}</label>
                <div style={colorInputContainerStyle}>
                    <input
                        type="color"
                        value={props.backgroundColor || '#ffffff'}
                        onChange={(e) => handleChange('backgroundColor', e.target.value)}
                        style={colorPickerStyle}
                    />
                    <input
                        type="text"
                        value={props.backgroundColor || '#ffffff'}
                        onChange={(e) => handleChange('backgroundColor', e.target.value)}
                        className="form-control"
                        style={{ flex: 1 }}
                        placeholder="#ffffff"
                    />
                </div>

                <label style={labelStyle}>{__('Tab Background Color')}</label>
                <div style={colorInputContainerStyle}>
                    <input
                        type="color"
                        value={props.tabBgColor || '#f3f4f6'}
                        onChange={(e) => handleChange('tabBgColor', e.target.value)}
                        style={colorPickerStyle}
                    />
                    <input
                        type="text"
                        value={props.tabBgColor || '#f3f4f6'}
                        onChange={(e) => handleChange('tabBgColor', e.target.value)}
                        className="form-control"
                        style={{ flex: 1 }}
                        placeholder="#f3f4f6"
                    />
                </div>
            </div>

            {/* Spacing Section */}
            <div style={sectionStyle}>
                <div style={sectionTitleStyle}>{__('Spacing')}</div>

                <label style={labelStyle}>{__('Content Padding')}</label>
                <input
                    type="text"
                    value={props.contentPadding || '24px'}
                    onChange={(e) => handleChange('contentPadding', e.target.value)}
                    className="form-control"
                    placeholder="24px"
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
                <strong style={{ color: '#374151' }}>{__('Tip:')}</strong> {__('Click tabs in the canvas to preview different panels. Use items (one per line) for bullet lists and badges (comma-separated) for pill labels.')}
            </div>
        </div>
    );
};

export default TabsBlockEditor;
