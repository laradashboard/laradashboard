/**
 * Tabs Block - Canvas Component
 *
 * Renders the tabs block in the builder canvas.
 * Supports tab switching and inline editing when selected.
 */

import { useState } from 'react';
import { applyLayoutStyles } from '../../components/layout-styles/styleHelpers';

const TabsBlock = ({ props, isSelected, onUpdate }) => {
    const tabs = props.tabs || [
        { label: 'Tab 1', heading: 'First Tab', description: 'Content for the first tab.', items: [], badges: [] },
    ];
    const tabStyle = props.tabStyle || 'pills';
    const tabAlignment = props.tabAlignment || 'center';
    const accentColor = props.accentColor || '#3b82f6';
    const contentPadding = props.contentPadding || '24px';
    const backgroundColor = props.backgroundColor || '#ffffff';
    const tabBgColor = props.tabBgColor || '#f3f4f6';

    const [activeTab, setActiveTab] = useState(props.activeTab || 0);

    const getAlignJustify = () => {
        switch (tabAlignment) {
            case 'left': return 'flex-start';
            case 'stretch': return 'stretch';
            default: return 'center';
        }
    };

    const getTabButtonStyle = (index) => {
        const isActive = index === activeTab;
        const base = {
            padding: '10px 20px',
            fontSize: '14px',
            fontWeight: isActive ? '600' : '400',
            cursor: 'pointer',
            border: 'none',
            transition: 'all 0.2s ease',
            flex: tabAlignment === 'stretch' ? 1 : undefined,
            textAlign: 'center',
            whiteSpace: 'nowrap',
        };

        switch (tabStyle) {
            case 'pills':
                return {
                    ...base,
                    borderRadius: '9999px',
                    backgroundColor: isActive ? accentColor : tabBgColor,
                    color: isActive ? '#ffffff' : '#374151',
                };
            case 'underline':
                return {
                    ...base,
                    borderRadius: '0',
                    backgroundColor: 'transparent',
                    color: isActive ? accentColor : '#6b7280',
                    borderBottom: isActive ? `3px solid ${accentColor}` : '3px solid transparent',
                    paddingBottom: '8px',
                };
            case 'buttons':
                return {
                    ...base,
                    borderRadius: '8px',
                    backgroundColor: isActive ? accentColor : 'transparent',
                    color: isActive ? '#ffffff' : '#374151',
                    border: isActive ? `2px solid ${accentColor}` : '2px solid #d1d5db',
                };
            default:
                return base;
        }
    };

    const defaultContainerStyle = {
        padding: '8px',
        borderRadius: '8px',
        backgroundColor,
    };
    const containerStyle = applyLayoutStyles(defaultContainerStyle, props.layoutStyles);

    const currentTab = tabs[activeTab] || tabs[0] || {};

    return (
        <div style={containerStyle}>
            {/* Tab Buttons */}
            <div style={{
                display: 'flex',
                gap: tabStyle === 'underline' ? '0' : '8px',
                justifyContent: getAlignJustify(),
                marginBottom: '16px',
                borderBottom: tabStyle === 'underline' ? '1px solid #e5e7eb' : 'none',
                flexWrap: 'wrap',
            }}>
                {tabs.map((tab, index) => (
                    <button
                        key={index}
                        type="button"
                        onClick={() => setActiveTab(index)}
                        style={getTabButtonStyle(index)}
                    >
                        {tab.label || `Tab ${index + 1}`}
                    </button>
                ))}
            </div>

            {/* Active Panel Content */}
            <div style={{ padding: contentPadding }}>
                {currentTab.heading && (
                    <h3 style={{
                        fontSize: '20px',
                        fontWeight: '600',
                        color: '#111827',
                        margin: '0 0 12px 0',
                    }}>
                        {currentTab.heading}
                    </h3>
                )}

                {currentTab.description && (
                    <p style={{
                        fontSize: '14px',
                        color: '#6b7280',
                        margin: '0 0 16px 0',
                        lineHeight: '1.6',
                    }}>
                        {currentTab.description}
                    </p>
                )}

                {/* Items List */}
                {currentTab.items && currentTab.items.length > 0 && (
                    <ul style={{
                        margin: '0 0 16px 0',
                        padding: '0 0 0 20px',
                        listStyleType: 'disc',
                    }}>
                        {currentTab.items.map((item, i) => (
                            <li key={i} style={{
                                fontSize: '14px',
                                color: '#374151',
                                marginBottom: '6px',
                                lineHeight: '1.5',
                            }}>
                                {item}
                            </li>
                        ))}
                    </ul>
                )}

                {/* Badges */}
                {currentTab.badges && currentTab.badges.length > 0 && (
                    <div style={{ display: 'flex', gap: '8px', flexWrap: 'wrap' }}>
                        {currentTab.badges.map((badge, i) => (
                            <span key={i} style={{
                                display: 'inline-block',
                                padding: '4px 12px',
                                fontSize: '12px',
                                fontWeight: '500',
                                borderRadius: '9999px',
                                backgroundColor: `${accentColor}15`,
                                color: accentColor,
                                border: `1px solid ${accentColor}30`,
                            }}>
                                {badge}
                            </span>
                        ))}
                    </div>
                )}
            </div>

            {/* Helper text when selected */}
            {isSelected && (
                <p style={{ marginTop: '8px', fontSize: '12px', color: '#6b7280', textAlign: 'center' }}>
                    Click tabs to preview. Use the properties panel to edit tab content.
                </p>
            )}
        </div>
    );
};

export default TabsBlock;
