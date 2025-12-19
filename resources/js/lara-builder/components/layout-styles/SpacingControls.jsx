/**
 * SpacingControls - Margin and Padding controls with visual box editor
 */
import { useState } from 'react';
import { __ } from '@lara-builder/i18n';

// Input component for spacing values
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

// Visual spacing box control (margin or padding)
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
                        title={linkSides ? __('Unlink sides') : __('Link all sides')}
                    >
                        <iconify-icon icon={linkSides ? 'mdi:link' : 'mdi:link-off'} width="14" height="14"></iconify-icon>
                    </button>
                    <button
                        type="button"
                        onClick={() => onChange({ top: '', right: '', bottom: '', left: '' })}
                        className="p-1 text-gray-400 hover:text-gray-600 rounded transition-colors"
                        title={__('Reset')}
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
                        <span className="text-xs text-gray-500">{__('Content')}</span>
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

const SpacingControls = ({ margin, padding, onMarginChange, onPaddingChange }) => {
    const [linkMargin, setLinkMargin] = useState(false);
    const [linkPadding, setLinkPadding] = useState(false);

    return (
        <div>
            <h4 className="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
                {__('Spacing')}
            </h4>

            {/* Margin */}
            <SpacingBoxControl
                label={__('Margin')}
                values={margin || {}}
                onChange={onMarginChange}
                linkSides={linkMargin}
                onToggleLink={() => setLinkMargin(!linkMargin)}
            />

            {/* Padding */}
            <SpacingBoxControl
                label={__('Padding')}
                values={padding || {}}
                onChange={onPaddingChange}
                linkSides={linkPadding}
                onToggleLink={() => setLinkPadding(!linkPadding)}
            />
        </div>
    );
};

export default SpacingControls;
