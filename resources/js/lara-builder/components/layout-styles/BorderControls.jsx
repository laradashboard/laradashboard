/**
 * BorderControls - Border width, style, color, and radius controls
 */
import { useState } from 'react';
import { __ } from '@lara-builder/i18n';

const BORDER_STYLE_OPTIONS = [
    { value: '', label: __('Default') },
    { value: 'none', label: __('None') },
    { value: 'solid', label: __('Solid') },
    { value: 'dashed', label: __('Dashed') },
    { value: 'dotted', label: __('Dotted') },
    { value: 'double', label: __('Double') },
];

// Border width input for each side
const BorderWidthInput = ({ value, onChange, placeholder = '0' }) => (
    <div className="relative flex-1">
        <input
            type="text"
            value={value || ''}
            onChange={(e) => onChange(e.target.value)}
            placeholder={placeholder}
            className="w-full px-2 py-1.5 text-xs bg-gray-100 border border-gray-200 rounded text-gray-700 placeholder-gray-400 focus:border-primary focus:outline-none"
        />
        <div className="absolute right-1 top-1/2 -translate-y-1/2">
            <iconify-icon icon="mdi:shield-outline" width="12" height="12" class="text-gray-400"></iconify-icon>
        </div>
    </div>
);

// Border radius input for each corner
const RadiusInput = ({ label, value, onChange }) => (
    <div className="flex-1">
        <label className="block text-xs text-gray-500 mb-1 uppercase">{label}</label>
        <div className="relative">
            <input
                type="text"
                value={value || ''}
                onChange={(e) => onChange(e.target.value)}
                placeholder="0"
                className="w-full px-2 py-1.5 text-xs bg-gray-100 border border-gray-200 rounded text-gray-700 placeholder-gray-400 focus:border-primary focus:outline-none"
            />
            <div className="absolute right-1 top-1/2 -translate-y-1/2">
                <iconify-icon icon="mdi:shield-outline" width="12" height="12" class="text-gray-400"></iconify-icon>
            </div>
        </div>
    </div>
);

const BorderControls = ({ border = {}, onChange }) => {
    const [isExpanded, setIsExpanded] = useState(false);
    const [linkWidth, setLinkWidth] = useState(true);
    const [linkRadius, setLinkRadius] = useState(true);

    const { width = {}, style = '', color = '', radius = {} } = border;

    const handleWidthChange = (side, value) => {
        if (linkWidth) {
            onChange({
                ...border,
                width: { top: value, right: value, bottom: value, left: value }
            });
        } else {
            onChange({
                ...border,
                width: { ...width, [side]: value }
            });
        }
    };

    const handleRadiusChange = (corner, value) => {
        if (linkRadius) {
            onChange({
                ...border,
                radius: { topLeft: value, topRight: value, bottomLeft: value, bottomRight: value }
            });
        } else {
            onChange({
                ...border,
                radius: { ...radius, [corner]: value }
            });
        }
    };

    const handleStyleChange = (value) => {
        onChange({ ...border, style: value });
    };

    const handleColorChange = (value) => {
        onChange({ ...border, color: value });
    };

    const hasBorder = width.top || width.right || width.bottom || width.left || style || color;

    return (
        <div className="mb-3">
            <button
                type="button"
                onClick={() => setIsExpanded(!isExpanded)}
                className="flex items-center justify-between w-full text-left py-2 group"
            >
                <span className="text-xs font-medium text-gray-600">{__('Border')}</span>
                <div className="flex items-center gap-2">
                    {hasBorder && (
                        <span className="w-2 h-2 rounded-full bg-primary"></span>
                    )}
                    <iconify-icon
                        icon="mdi:pencil-outline"
                        width="16"
                        height="16"
                        class="text-gray-400 group-hover:text-gray-600"
                    ></iconify-icon>
                </div>
            </button>

            {isExpanded && (
                <div className="mt-2 p-3 bg-gray-50 rounded-lg space-y-4">
                    {/* Width */}
                    <div>
                        <div className="flex items-center justify-between mb-2">
                            <span className="text-xs font-medium text-gray-600">{__('Width')}</span>
                            <button
                                type="button"
                                onClick={() => setLinkWidth(!linkWidth)}
                                className={`p-1 rounded transition-colors ${linkWidth ? 'text-primary bg-primary/20' : 'text-gray-400 hover:text-gray-600'}`}
                                title={linkWidth ? __('Unlink sides') : __('Link all sides')}
                            >
                                <iconify-icon icon={linkWidth ? 'mdi:link' : 'mdi:link-off'} width="14" height="14"></iconify-icon>
                            </button>
                        </div>

                        {/* Top */}
                        <div className="flex justify-center mb-2">
                            <BorderWidthInput
                                value={width.top}
                                onChange={(v) => handleWidthChange('top', v)}
                            />
                        </div>

                        {/* Left and Right */}
                        <div className="flex gap-2 mb-2">
                            <BorderWidthInput
                                value={width.left}
                                onChange={(v) => handleWidthChange('left', v)}
                            />
                            <BorderWidthInput
                                value={width.right}
                                onChange={(v) => handleWidthChange('right', v)}
                            />
                        </div>

                        {/* Bottom */}
                        <div className="flex justify-center">
                            <BorderWidthInput
                                value={width.bottom}
                                onChange={(v) => handleWidthChange('bottom', v)}
                            />
                        </div>
                    </div>

                    {/* Style */}
                    <div>
                        <label className="block text-xs font-medium text-gray-600 mb-1">{__('Style')}</label>
                        <select
                            value={style}
                            onChange={(e) => handleStyleChange(e.target.value)}
                            className="w-full px-2 py-1.5 text-xs bg-gray-100 border border-gray-200 rounded text-gray-700 focus:border-primary focus:outline-none"
                        >
                            {BORDER_STYLE_OPTIONS.map(opt => (
                                <option key={opt.value} value={opt.value}>{opt.label}</option>
                            ))}
                        </select>
                    </div>

                    {/* Color */}
                    <div>
                        <label className="block text-xs font-medium text-gray-600 mb-1">{__('Color')}</label>
                        <div className="flex gap-2">
                            <input
                                type="color"
                                value={color || '#000000'}
                                onChange={(e) => handleColorChange(e.target.value)}
                                className="h-8 w-10 border border-gray-200 rounded cursor-pointer"
                            />
                            <input
                                type="text"
                                value={color}
                                onChange={(e) => handleColorChange(e.target.value)}
                                placeholder="#000000"
                                className="flex-1 px-2 py-1.5 text-xs bg-gray-100 border border-gray-200 rounded text-gray-700 placeholder-gray-400 focus:border-primary focus:outline-none"
                            />
                            {color && (
                                <button
                                    type="button"
                                    onClick={() => handleColorChange('')}
                                    className="p-1 text-gray-400 hover:text-gray-600"
                                    title={__('Clear')}
                                >
                                    <iconify-icon icon="mdi:close" width="16" height="16"></iconify-icon>
                                </button>
                            )}
                        </div>
                    </div>

                    {/* Radius */}
                    <div>
                        <div className="flex items-center justify-between mb-2">
                            <span className="text-xs font-medium text-gray-600">{__('Radius')}</span>
                            <button
                                type="button"
                                onClick={() => setLinkRadius(!linkRadius)}
                                className={`p-1 rounded transition-colors ${linkRadius ? 'text-primary bg-primary/20' : 'text-gray-400 hover:text-gray-600'}`}
                                title={linkRadius ? __('Unlink corners') : __('Link all corners')}
                            >
                                <iconify-icon icon={linkRadius ? 'mdi:link' : 'mdi:link-off'} width="14" height="14"></iconify-icon>
                            </button>
                        </div>

                        <div className="grid grid-cols-2 gap-2">
                            <RadiusInput
                                label={__('Top Left')}
                                value={radius.topLeft}
                                onChange={(v) => handleRadiusChange('topLeft', v)}
                            />
                            <RadiusInput
                                label={__('Top Right')}
                                value={radius.topRight}
                                onChange={(v) => handleRadiusChange('topRight', v)}
                            />
                            <RadiusInput
                                label={__('Bottom Left')}
                                value={radius.bottomLeft}
                                onChange={(v) => handleRadiusChange('bottomLeft', v)}
                            />
                            <RadiusInput
                                label={__('Bottom Right')}
                                value={radius.bottomRight}
                                onChange={(v) => handleRadiusChange('bottomRight', v)}
                            />
                        </div>
                    </div>

                    {/* Reset */}
                    {hasBorder && (
                        <button
                            type="button"
                            onClick={() => onChange({})}
                            className="w-full px-3 py-1.5 text-xs text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded transition-colors"
                        >
                            {__('Reset Border')}
                        </button>
                    )}
                </div>
            )}
        </div>
    );
};

export default BorderControls;
