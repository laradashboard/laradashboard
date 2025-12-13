/**
 * TypographyControls - Typography settings (color, font, size, etc.)
 */
import { __ } from '@lara-builder/i18n';
import {
    FONT_FAMILY_PRESETS,
    FONT_SIZE_PRESETS,
    FONT_WEIGHT_PRESETS,
    FONT_STYLE_PRESETS,
    LINE_HEIGHT_PRESETS,
    LETTER_SPACING_PRESETS,
    TEXT_TRANSFORM_PRESETS,
    TEXT_DECORATION_PRESETS,
} from './presets';

const TypographyControls = ({ typography = {}, onChange }) => {
    const handleChange = (field, value) => {
        onChange({ ...typography, [field]: value });
    };

    return (
        <div className="space-y-3">
            {/* Color */}
            <div>
                <label className="block text-xs font-medium text-gray-600 mb-1">{__('Color')}</label>
                <div className="flex gap-2">
                    <input
                        type="color"
                        value={typography.color || '#000000'}
                        onChange={(e) => handleChange('color', e.target.value)}
                        className="h-8 w-10 border border-gray-200 rounded cursor-pointer bg-gray-100"
                    />
                    <input
                        type="text"
                        value={typography.color || ''}
                        onChange={(e) => handleChange('color', e.target.value)}
                        placeholder="inherit"
                        className="flex-1 px-2 py-1.5 text-xs bg-gray-100 border border-gray-200 rounded text-gray-700 placeholder-gray-400 focus:border-primary focus:outline-none"
                    />
                    {typography.color && (
                        <button
                            type="button"
                            onClick={() => handleChange('color', '')}
                            className="p-1.5 text-gray-400 hover:text-gray-600 rounded transition-colors"
                            title={__('Clear')}
                        >
                            <iconify-icon icon="mdi:close" width="14" height="14"></iconify-icon>
                        </button>
                    )}
                </div>
            </div>

            {/* Font Size */}
            <div>
                <label className="block text-xs font-medium text-gray-600 mb-1">{__('Font size')}</label>
                <div className="flex gap-2">
                    <select
                        value={FONT_SIZE_PRESETS.some(p => p.value === typography.fontSize) ? typography.fontSize : 'custom'}
                        onChange={(e) => {
                            if (e.target.value !== 'custom') {
                                handleChange('fontSize', e.target.value);
                            }
                        }}
                        className="flex-1 px-2 py-1.5 text-xs bg-gray-100 border border-gray-200 rounded text-gray-700 focus:border-primary focus:outline-none"
                    >
                        {FONT_SIZE_PRESETS.map(preset => (
                            <option key={preset.value} value={preset.value}>{preset.label}</option>
                        ))}
                        <option value="custom">{__('Custom')}</option>
                    </select>
                    <input
                        type="text"
                        value={typography.fontSize || ''}
                        onChange={(e) => handleChange('fontSize', e.target.value)}
                        placeholder="e.g., 16px"
                        className="w-20 px-2 py-1.5 text-xs bg-gray-100 border border-gray-200 rounded text-gray-700 placeholder-gray-400 focus:border-primary focus:outline-none"
                    />
                </div>
            </div>

            {/* Text Align */}
            <div>
                <label className="block text-xs font-medium text-gray-600 mb-1">{__('Text align')}</label>
                <div className="flex gap-1">
                    {[
                        { value: 'left', icon: 'mdi:format-align-left' },
                        { value: 'center', icon: 'mdi:format-align-center' },
                        { value: 'right', icon: 'mdi:format-align-right' },
                        { value: 'justify', icon: 'mdi:format-align-justify' },
                    ].map(({ value, icon }) => (
                        <button
                            key={value}
                            type="button"
                            onClick={() => handleChange('textAlign', value)}
                            className={`flex-1 p-2 rounded transition-colors ${
                                typography.textAlign === value
                                    ? 'bg-primary text-white'
                                    : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                            }`}
                            title={value.charAt(0).toUpperCase() + value.slice(1)}
                        >
                            <iconify-icon icon={icon} width="16" height="16"></iconify-icon>
                        </button>
                    ))}
                </div>
            </div>

            {/* Text Transform */}
            <div>
                <label className="block text-xs font-medium text-gray-600 mb-1">{__('Text transform')}</label>
                <div className="flex gap-1">
                    {TEXT_TRANSFORM_PRESETS.slice(0, 4).map(({ value, label, icon }) => (
                        <button
                            key={value || 'inherit'}
                            type="button"
                            onClick={() => handleChange('textTransform', value)}
                            className={`flex-1 p-2 rounded text-xs font-medium transition-colors ${
                                (typography.textTransform || '') === value
                                    ? 'bg-primary text-white'
                                    : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                            }`}
                            title={label}
                        >
                            {icon}
                        </button>
                    ))}
                </div>
            </div>

            {/* Font Family */}
            <div>
                <label className="block text-xs font-medium text-gray-600 mb-1">{__('Font family')}</label>
                <select
                    value={typography.fontFamily || ''}
                    onChange={(e) => handleChange('fontFamily', e.target.value)}
                    className="w-full px-2 py-1.5 text-xs bg-gray-100 border border-gray-200 rounded text-gray-700 focus:border-primary focus:outline-none"
                >
                    {FONT_FAMILY_PRESETS.map(preset => (
                        <option key={preset.value} value={preset.value}>{preset.label}</option>
                    ))}
                </select>
            </div>

            {/* Font Weight */}
            <div>
                <label className="block text-xs font-medium text-gray-600 mb-1">{__('Font weight')}</label>
                <select
                    value={typography.fontWeight || ''}
                    onChange={(e) => handleChange('fontWeight', e.target.value)}
                    className="w-full px-2 py-1.5 text-xs bg-gray-100 border border-gray-200 rounded text-gray-700 focus:border-primary focus:outline-none"
                >
                    {FONT_WEIGHT_PRESETS.map(preset => (
                        <option key={preset.value} value={preset.value}>{preset.label}</option>
                    ))}
                </select>
            </div>

            {/* Font Style */}
            <div>
                <label className="block text-xs font-medium text-gray-600 mb-1">{__('Font style')}</label>
                <select
                    value={typography.fontStyle || ''}
                    onChange={(e) => handleChange('fontStyle', e.target.value)}
                    className="w-full px-2 py-1.5 text-xs bg-gray-100 border border-gray-200 rounded text-gray-700 focus:border-primary focus:outline-none"
                >
                    {FONT_STYLE_PRESETS.map(preset => (
                        <option key={preset.value} value={preset.value}>{preset.label}</option>
                    ))}
                </select>
            </div>

            {/* Line Height */}
            <div>
                <label className="block text-xs font-medium text-gray-600 mb-1">{__('Line height')}</label>
                <div className="flex gap-2">
                    <select
                        value={LINE_HEIGHT_PRESETS.some(p => p.value === typography.lineHeight) ? typography.lineHeight : 'custom'}
                        onChange={(e) => {
                            if (e.target.value !== 'custom') {
                                handleChange('lineHeight', e.target.value);
                            }
                        }}
                        className="flex-1 px-2 py-1.5 text-xs bg-gray-100 border border-gray-200 rounded text-gray-700 focus:border-primary focus:outline-none"
                    >
                        {LINE_HEIGHT_PRESETS.map(preset => (
                            <option key={preset.value} value={preset.value}>{preset.label}</option>
                        ))}
                        <option value="custom">{__('Custom')}</option>
                    </select>
                    <input
                        type="text"
                        value={typography.lineHeight || ''}
                        onChange={(e) => handleChange('lineHeight', e.target.value)}
                        placeholder="-"
                        className="w-16 px-2 py-1.5 text-xs bg-gray-100 border border-gray-200 rounded text-gray-700 placeholder-gray-400 focus:border-primary focus:outline-none"
                    />
                </div>
            </div>

            {/* Letter Spacing */}
            <div>
                <label className="block text-xs font-medium text-gray-600 mb-1">{__('Letter spacing')}</label>
                <div className="flex gap-2">
                    <select
                        value={LETTER_SPACING_PRESETS.some(p => p.value === typography.letterSpacing) ? typography.letterSpacing : 'custom'}
                        onChange={(e) => {
                            if (e.target.value !== 'custom') {
                                handleChange('letterSpacing', e.target.value);
                            }
                        }}
                        className="flex-1 px-2 py-1.5 text-xs bg-gray-100 border border-gray-200 rounded text-gray-700 focus:border-primary focus:outline-none"
                    >
                        {LETTER_SPACING_PRESETS.map(preset => (
                            <option key={preset.value} value={preset.value}>{preset.label}</option>
                        ))}
                        <option value="custom">{__('Custom')}</option>
                    </select>
                    <input
                        type="text"
                        value={typography.letterSpacing || ''}
                        onChange={(e) => handleChange('letterSpacing', e.target.value)}
                        placeholder="-"
                        className="w-16 px-2 py-1.5 text-xs bg-gray-100 border border-gray-200 rounded text-gray-700 placeholder-gray-400 focus:border-primary focus:outline-none"
                    />
                </div>
            </div>

            {/* Text Decoration */}
            <div>
                <label className="block text-xs font-medium text-gray-600 mb-1">{__('Text decoration')}</label>
                <div className="flex gap-1">
                    {TEXT_DECORATION_PRESETS.slice(0, 4).map(({ value, label, icon }) => (
                        <button
                            key={value || 'inherit'}
                            type="button"
                            onClick={() => handleChange('textDecoration', value)}
                            className={`flex-1 p-2 rounded transition-colors ${
                                (typography.textDecoration || '') === value
                                    ? 'bg-primary text-white'
                                    : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                            }`}
                            title={label}
                        >
                            <iconify-icon icon={icon} width="16" height="16"></iconify-icon>
                        </button>
                    ))}
                </div>
            </div>
        </div>
    );
};

export default TypographyControls;
