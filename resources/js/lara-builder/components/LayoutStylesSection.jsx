/**
 * LayoutStylesSection - Reusable Layout styles panel for all blocks
 *
 * Provides spacing (margin, padding), sizing (width, height, min/max dimensions)
 * controls that can be applied to any block type.
 */

import { useState } from 'react';

// Preset options for spacing
const SPACING_PRESETS = [
    { value: '', label: 'Auto' },
    { value: '0', label: '0' },
    { value: '4px', label: '4' },
    { value: '8px', label: '8' },
    { value: '12px', label: '12' },
    { value: '16px', label: '16' },
    { value: '20px', label: '20' },
    { value: '24px', label: '24' },
    { value: '32px', label: '32' },
    { value: '40px', label: '40' },
    { value: '48px', label: '48' },
    { value: '64px', label: '64' },
];

// Preset options for sizing
const SIZE_PRESETS = [
    { value: '', label: 'Auto' },
    { value: '100%', label: '100%' },
    { value: '75%', label: '75%' },
    { value: '50%', label: '50%' },
    { value: '25%', label: '25%' },
    { value: '100px', label: '100px' },
    { value: '150px', label: '150px' },
    { value: '200px', label: '200px' },
    { value: '250px', label: '250px' },
    { value: '300px', label: '300px' },
    { value: '400px', label: '400px' },
    { value: '500px', label: '500px' },
];

// Input component with icon badge
const SpacingInput = ({ value, onChange, placeholder = '' }) => {
    return (
        <div className="relative">
            <input
                type="text"
                value={value || ''}
                onChange={(e) => onChange(e.target.value)}
                placeholder={placeholder}
                className="w-full px-2 py-1.5 text-xs bg-gray-100 border border-gray-200 rounded text-gray-700 placeholder-gray-400 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
            />
            <div className="absolute right-1 top-1/2 -translate-y-1/2">
                <iconify-icon icon="mdi:shield-outline" width="14" height="14" class="text-gray-400"></iconify-icon>
            </div>
        </div>
    );
};

// Spacing box control (margin or padding visual editor)
const SpacingBoxControl = ({ label, values, onChange, linkSides, onToggleLink }) => {
    const { top = '', right = '', bottom = '', left = '' } = values || {};

    const handleChange = (side, value) => {
        if (linkSides) {
            // When linked, change all sides
            onChange({ top: value, right: value, bottom: value, left: value });
        } else {
            onChange({ ...values, [side]: value });
        }
    };

    return (
        <div className="mb-4">
            <div className="flex items-center justify-between mb-2">
                <span className="text-xs font-medium text-gray-600">{label}</span>
                <div className="flex items-center gap-2">
                    <button
                        type="button"
                        onClick={onToggleLink}
                        className={`p-1 rounded transition-colors ${linkSides ? 'text-primary bg-primary/20' : 'text-gray-400 hover:text-gray-600'}`}
                        title={linkSides ? 'Unlink sides' : 'Link all sides'}
                    >
                        <iconify-icon icon={linkSides ? 'mdi:link' : 'mdi:link-off'} width="14" height="14"></iconify-icon>
                    </button>
                    <button
                        type="button"
                        onClick={() => onChange({ top: '', right: '', bottom: '', left: '' })}
                        className="p-1 text-gray-400 hover:text-gray-600 rounded transition-colors"
                        title="Reset"
                    >
                        <iconify-icon icon="mdi:refresh" width="14" height="14"></iconify-icon>
                    </button>
                </div>
            </div>

            {/* Visual spacing box */}
            <div className="relative bg-gray-100 rounded-lg p-3">
                {/* Top */}
                <div className="flex justify-center mb-2">
                    <SpacingInput
                        value={top}
                        onChange={(v) => handleChange('top', v)}
                        placeholder="0"
                    />
                </div>

                {/* Middle row with left and right */}
                <div className="flex items-center justify-between gap-2">
                    <div className="w-20">
                        <SpacingInput
                            value={left}
                            onChange={(v) => handleChange('left', v)}
                            placeholder="0"
                        />
                    </div>
                    <div className="flex-1 h-12 bg-gray-200 rounded flex items-center justify-center">
                        <span className="text-xs text-gray-500">Content</span>
                    </div>
                    <div className="w-20">
                        <SpacingInput
                            value={right}
                            onChange={(v) => handleChange('right', v)}
                            placeholder="0"
                        />
                    </div>
                </div>

                {/* Bottom */}
                <div className="flex justify-center mt-2">
                    <SpacingInput
                        value={bottom}
                        onChange={(v) => handleChange('bottom', v)}
                        placeholder="0"
                    />
                </div>
            </div>
        </div>
    );
};

// Size input with presets dropdown
const SizeInput = ({ label, value, onChange, presets = SIZE_PRESETS }) => {
    const [showCustom, setShowCustom] = useState(
        value && !presets.some(p => p.value === value)
    );

    return (
        <div className="mb-3">
            <label className="block text-xs font-medium text-gray-600 mb-1">{label}</label>
            <div className="flex gap-2">
                <select
                    value={showCustom ? 'custom' : (value || '')}
                    onChange={(e) => {
                        if (e.target.value === 'custom') {
                            setShowCustom(true);
                        } else {
                            setShowCustom(false);
                            onChange(e.target.value);
                        }
                    }}
                    className="flex-1 px-2 py-1.5 text-xs bg-gray-100 border border-gray-200 rounded text-gray-700 focus:border-primary focus:outline-none"
                >
                    {presets.map(preset => (
                        <option key={preset.value} value={preset.value}>{preset.label}</option>
                    ))}
                    <option value="custom">Custom</option>
                </select>
                {showCustom && (
                    <input
                        type="text"
                        value={value || ''}
                        onChange={(e) => onChange(e.target.value)}
                        placeholder="e.g., 200px"
                        className="w-24 px-2 py-1.5 text-xs bg-gray-100 border border-gray-200 rounded text-gray-700 placeholder-gray-400 focus:border-primary focus:outline-none"
                    />
                )}
                <div className="flex items-center">
                    <iconify-icon icon="mdi:shield-outline" width="14" height="14" class="text-gray-400"></iconify-icon>
                </div>
            </div>
        </div>
    );
};

const LayoutStylesSection = ({ layoutStyles = {}, onUpdate, defaultCollapsed = true }) => {
    const [isExpanded, setIsExpanded] = useState(!defaultCollapsed);
    const [linkMargin, setLinkMargin] = useState(false);
    const [linkPadding, setLinkPadding] = useState(false);

    const handleLayoutChange = (field, value) => {
        onUpdate({ ...layoutStyles, [field]: value });
    };

    const handleMarginChange = (margins) => {
        onUpdate({ ...layoutStyles, margin: margins });
    };

    const handlePaddingChange = (paddings) => {
        onUpdate({ ...layoutStyles, padding: paddings });
    };

    return (
        <div className="border-t border-gray-200 mt-4 pt-4">
            {/* Collapsible Header */}
            <button
                type="button"
                onClick={() => setIsExpanded(!isExpanded)}
                className="flex items-center justify-between w-full text-left mb-3 group"
            >
                <div className="flex items-center gap-2">
                    <iconify-icon
                        icon="mdi:view-dashboard-outline"
                        width="16"
                        height="16"
                        class="text-yellow-500"
                    ></iconify-icon>
                    <span className="text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Layout
                    </span>
                </div>
                <iconify-icon
                    icon={isExpanded ? 'mdi:chevron-up' : 'mdi:chevron-down'}
                    width="18"
                    height="18"
                    class="text-gray-400 group-hover:text-gray-600 transition-colors"
                ></iconify-icon>
            </button>

            {isExpanded && (
                <div className="space-y-4">
                    {/* SPACING Section */}
                    <div>
                        <h4 className="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
                            Spacing
                        </h4>

                        {/* Margin */}
                        <SpacingBoxControl
                            label="Margin"
                            values={layoutStyles.margin || {}}
                            onChange={handleMarginChange}
                            linkSides={linkMargin}
                            onToggleLink={() => setLinkMargin(!linkMargin)}
                        />

                        {/* Padding */}
                        <SpacingBoxControl
                            label="Padding"
                            values={layoutStyles.padding || {}}
                            onChange={handlePaddingChange}
                            linkSides={linkPadding}
                            onToggleLink={() => setLinkPadding(!linkPadding)}
                        />
                    </div>

                    {/* SIZING Section */}
                    <div>
                        <h4 className="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
                            Sizing
                        </h4>

                        <SizeInput
                            label="Width"
                            value={layoutStyles.width || ''}
                            onChange={(v) => handleLayoutChange('width', v)}
                        />

                        <SizeInput
                            label="Min. width"
                            value={layoutStyles.minWidth || ''}
                            onChange={(v) => handleLayoutChange('minWidth', v)}
                        />

                        <SizeInput
                            label="Max. width"
                            value={layoutStyles.maxWidth || ''}
                            onChange={(v) => handleLayoutChange('maxWidth', v)}
                        />

                        <SizeInput
                            label="Height"
                            value={layoutStyles.height || ''}
                            onChange={(v) => handleLayoutChange('height', v)}
                        />

                        <SizeInput
                            label="Min. height"
                            value={layoutStyles.minHeight || ''}
                            onChange={(v) => handleLayoutChange('minHeight', v)}
                        />

                        <SizeInput
                            label="Max. height"
                            value={layoutStyles.maxHeight || ''}
                            onChange={(v) => handleLayoutChange('maxHeight', v)}
                        />
                    </div>
                </div>
            )}
        </div>
    );
};

// Helper function to convert layout styles object to inline CSS
export const layoutStylesToCSS = (layoutStyles = {}) => {
    const styles = {};

    // Process margin
    if (layoutStyles.margin) {
        const { top, right, bottom, left } = layoutStyles.margin;
        if (top || right || bottom || left) {
            styles.marginTop = top || '0';
            styles.marginRight = right || '0';
            styles.marginBottom = bottom || '0';
            styles.marginLeft = left || '0';
        }
    }

    // Process padding
    if (layoutStyles.padding) {
        const { top, right, bottom, left } = layoutStyles.padding;
        if (top || right || bottom || left) {
            styles.paddingTop = top || '0';
            styles.paddingRight = right || '0';
            styles.paddingBottom = bottom || '0';
            styles.paddingLeft = left || '0';
        }
    }

    // Process sizing
    if (layoutStyles.width) styles.width = layoutStyles.width;
    if (layoutStyles.minWidth) styles.minWidth = layoutStyles.minWidth;
    if (layoutStyles.maxWidth) styles.maxWidth = layoutStyles.maxWidth;
    if (layoutStyles.height) styles.height = layoutStyles.height;
    if (layoutStyles.minHeight) styles.minHeight = layoutStyles.minHeight;
    if (layoutStyles.maxHeight) styles.maxHeight = layoutStyles.maxHeight;

    return styles;
};

// Helper function to convert layout styles to inline style string for HTML generation
export const layoutStylesToInlineCSS = (layoutStyles = {}) => {
    const cssProperties = [];

    // Process margin
    if (layoutStyles.margin) {
        const { top, right, bottom, left } = layoutStyles.margin;
        if (top) cssProperties.push(`margin-top: ${top}`);
        if (right) cssProperties.push(`margin-right: ${right}`);
        if (bottom) cssProperties.push(`margin-bottom: ${bottom}`);
        if (left) cssProperties.push(`margin-left: ${left}`);
    }

    // Process padding
    if (layoutStyles.padding) {
        const { top, right, bottom, left } = layoutStyles.padding;
        if (top) cssProperties.push(`padding-top: ${top}`);
        if (right) cssProperties.push(`padding-right: ${right}`);
        if (bottom) cssProperties.push(`padding-bottom: ${bottom}`);
        if (left) cssProperties.push(`padding-left: ${left}`);
    }

    // Process sizing
    if (layoutStyles.width) cssProperties.push(`width: ${layoutStyles.width}`);
    if (layoutStyles.minWidth) cssProperties.push(`min-width: ${layoutStyles.minWidth}`);
    if (layoutStyles.maxWidth) cssProperties.push(`max-width: ${layoutStyles.maxWidth}`);
    if (layoutStyles.height) cssProperties.push(`height: ${layoutStyles.height}`);
    if (layoutStyles.minHeight) cssProperties.push(`min-height: ${layoutStyles.minHeight}`);
    if (layoutStyles.maxHeight) cssProperties.push(`max-height: ${layoutStyles.maxHeight}`);

    return cssProperties.join('; ');
};

export default LayoutStylesSection;
