/**
 * BoxShadowControls - Box shadow X, Y, blur, spread, color, and inset controls
 */
import { useState } from 'react';
import { __ } from '@lara-builder/i18n';

// Shadow value input
const ShadowInput = ({ label, value, onChange, placeholder = '0' }) => (
    <div className="flex items-center gap-2">
        <span className="text-xs text-gray-500 w-12 uppercase">{label}</span>
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
    </div>
);

const BoxShadowControls = ({ boxShadow = {}, onChange }) => {
    const [isExpanded, setIsExpanded] = useState(false);

    const { x = '', y = '', blur = '', spread = '', color = '', inset = false } = boxShadow;

    const handleChange = (field, value) => {
        onChange({ ...boxShadow, [field]: value });
    };

    const hasShadow = x || y || blur || spread || color;

    return (
        <div className="mb-3">
            <button
                type="button"
                onClick={() => setIsExpanded(!isExpanded)}
                className="flex items-center justify-between w-full text-left py-2 group"
            >
                <span className="text-xs font-medium text-gray-600">{__('Box shadow')}</span>
                <div className="flex items-center gap-2">
                    {hasShadow && (
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
                <div className="mt-2 p-3 bg-gray-50 rounded-lg space-y-3">
                    <div className="text-xs font-medium text-gray-600 mb-2">{__('Box shadow')}</div>

                    {/* X Offset */}
                    <ShadowInput
                        label="X"
                        value={x}
                        onChange={(v) => handleChange('x', v)}
                        placeholder="0px"
                    />

                    {/* Y Offset */}
                    <ShadowInput
                        label="Y"
                        value={y}
                        onChange={(v) => handleChange('y', v)}
                        placeholder="4px"
                    />

                    {/* Blur */}
                    <ShadowInput
                        label={__('Blur')}
                        value={blur}
                        onChange={(v) => handleChange('blur', v)}
                        placeholder="6px"
                    />

                    {/* Spread */}
                    <ShadowInput
                        label={__('Spread')}
                        value={spread}
                        onChange={(v) => handleChange('spread', v)}
                        placeholder="0px"
                    />

                    {/* Color */}
                    <div className="flex items-center gap-2">
                        <span className="text-xs text-gray-500 w-12">{__('Color')}</span>
                        <div className="flex-1 flex gap-2">
                            <input
                                type="color"
                                value={color || '#000000'}
                                onChange={(e) => handleChange('color', e.target.value)}
                                className="h-8 w-10 border border-gray-200 rounded cursor-pointer"
                            />
                            <input
                                type="text"
                                value={color}
                                onChange={(e) => handleChange('color', e.target.value)}
                                placeholder="rgba(0,0,0,0.1)"
                                className="flex-1 px-2 py-1.5 text-xs bg-gray-100 border border-gray-200 rounded text-gray-700 placeholder-gray-400 focus:border-primary focus:outline-none"
                            />
                        </div>
                    </div>

                    {/* Inset */}
                    <div className="flex items-center justify-between">
                        <span className="text-xs text-gray-500">{__('Inset')}</span>
                        <button
                            type="button"
                            onClick={() => handleChange('inset', !inset)}
                            className={`relative w-10 h-5 rounded-full transition-colors ${inset ? 'bg-primary' : 'bg-gray-300'}`}
                        >
                            <span
                                className={`absolute top-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform ${inset ? 'translate-x-5' : 'translate-x-0.5'}`}
                            />
                        </button>
                    </div>

                    {/* Preview */}
                    {hasShadow && (
                        <div className="mt-3 pt-3 border-t border-gray-200">
                            <div className="text-xs text-gray-500 mb-2">{__('Preview')}</div>
                            <div
                                className="w-full h-16 bg-white rounded-lg border border-gray-200"
                                style={{
                                    boxShadow: `${inset ? 'inset ' : ''}${x || '0px'} ${y || '4px'} ${blur || '6px'} ${spread || '0px'} ${color || 'rgba(0,0,0,0.1)'}`,
                                }}
                            />
                        </div>
                    )}

                    {/* Quick presets */}
                    <div className="pt-2">
                        <div className="text-xs text-gray-500 mb-2">{__('Quick presets')}</div>
                        <div className="flex flex-wrap gap-1">
                            <button
                                type="button"
                                onClick={() => onChange({ x: '0px', y: '1px', blur: '3px', spread: '0px', color: 'rgba(0,0,0,0.1)', inset: false })}
                                className="px-2 py-1 text-xs bg-gray-100 hover:bg-gray-200 rounded transition-colors"
                            >
                                {__('Subtle')}
                            </button>
                            <button
                                type="button"
                                onClick={() => onChange({ x: '0px', y: '4px', blur: '6px', spread: '-1px', color: 'rgba(0,0,0,0.1)', inset: false })}
                                className="px-2 py-1 text-xs bg-gray-100 hover:bg-gray-200 rounded transition-colors"
                            >
                                {__('Medium')}
                            </button>
                            <button
                                type="button"
                                onClick={() => onChange({ x: '0px', y: '10px', blur: '15px', spread: '-3px', color: 'rgba(0,0,0,0.1)', inset: false })}
                                className="px-2 py-1 text-xs bg-gray-100 hover:bg-gray-200 rounded transition-colors"
                            >
                                {__('Large')}
                            </button>
                            <button
                                type="button"
                                onClick={() => onChange({ x: '0px', y: '25px', blur: '50px', spread: '-12px', color: 'rgba(0,0,0,0.25)', inset: false })}
                                className="px-2 py-1 text-xs bg-gray-100 hover:bg-gray-200 rounded transition-colors"
                            >
                                {__('XL')}
                            </button>
                        </div>
                    </div>

                    {/* Reset */}
                    {hasShadow && (
                        <button
                            type="button"
                            onClick={() => onChange({})}
                            className="w-full px-3 py-1.5 text-xs text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded transition-colors"
                        >
                            {__('Reset Shadow')}
                        </button>
                    )}
                </div>
            )}
        </div>
    );
};

export default BoxShadowControls;
