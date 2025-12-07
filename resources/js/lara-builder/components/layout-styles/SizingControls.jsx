/**
 * SizingControls - Width and Height dimension controls
 */
import { useState } from 'react';
import { __ } from '@lara-builder/i18n';
import { SIZE_PRESETS } from './presets';

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
                    <option value="custom">{__('Custom')}</option>
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

const SizingControls = ({ layoutStyles = {}, onChange }) => {
    const handleChange = (field, value) => {
        onChange(field, value);
    };

    return (
        <div>
            <h4 className="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
                {__('Sizing')}
            </h4>

            <SizeInput
                label={__('Width')}
                value={layoutStyles.width || ''}
                onChange={(v) => handleChange('width', v)}
            />

            <SizeInput
                label={__('Min. width')}
                value={layoutStyles.minWidth || ''}
                onChange={(v) => handleChange('minWidth', v)}
            />

            <SizeInput
                label={__('Max. width')}
                value={layoutStyles.maxWidth || ''}
                onChange={(v) => handleChange('maxWidth', v)}
            />

            <SizeInput
                label={__('Height')}
                value={layoutStyles.height || ''}
                onChange={(v) => handleChange('height', v)}
            />

            <SizeInput
                label={__('Min. height')}
                value={layoutStyles.minHeight || ''}
                onChange={(v) => handleChange('minHeight', v)}
            />

            <SizeInput
                label={__('Max. height')}
                value={layoutStyles.maxHeight || ''}
                onChange={(v) => handleChange('maxHeight', v)}
            />
        </div>
    );
};

export default SizingControls;
