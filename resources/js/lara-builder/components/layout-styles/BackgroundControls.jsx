/**
 * BackgroundControls - Background color and image settings
 */
import { useState } from 'react';
import { __ } from '@lara-builder/i18n';
import {
    BACKGROUND_SIZE_PRESETS,
    BACKGROUND_POSITION_PRESETS,
    BACKGROUND_REPEAT_PRESETS,
} from './presets';
import { mediaLibrary } from '../../services/MediaLibraryService';

const BackgroundControls = ({ background = {}, onChange, onImageUpload }) => {
    const [isSelecting, setIsSelecting] = useState(false);

    const handleColorChange = (color) => {
        onChange({ ...background, color });
    };

    const handleImageChange = (image) => {
        onChange({ ...background, image });
    };

    const handleSelectImage = async () => {
        setIsSelecting(true);
        try {
            const file = await mediaLibrary.selectImage();
            if (file?.url) {
                handleImageChange(file.url);
            }
        } catch (err) {
            console.error('Failed to select image:', err);
        } finally {
            setIsSelecting(false);
        }
    };

    const handleClearImage = () => {
        onChange({
            ...background,
            image: '',
            size: '',
            position: '',
            repeat: '',
        });
    };

    return (
        <div className="space-y-3">
            {/* Background Color */}
            <div>
                <label className="block text-xs font-medium text-gray-600 mb-1">{__('Background color')}</label>
                <div className="flex gap-2">
                    <input
                        type="color"
                        value={background.color || '#ffffff'}
                        onChange={(e) => handleColorChange(e.target.value)}
                        className="h-8 w-10 border border-gray-200 rounded cursor-pointer bg-gray-100"
                    />
                    <input
                        type="text"
                        value={background.color || ''}
                        onChange={(e) => handleColorChange(e.target.value)}
                        placeholder="transparent"
                        className="flex-1 px-2 py-1.5 text-xs bg-gray-100 border border-gray-200 rounded text-gray-700 placeholder-gray-400 focus:border-primary focus:outline-none"
                    />
                    {background.color && (
                        <button
                            type="button"
                            onClick={() => handleColorChange('')}
                            className="p-1.5 text-gray-400 hover:text-gray-600 rounded transition-colors"
                            title={__('Clear')}
                        >
                            <iconify-icon icon="mdi:close" width="14" height="14"></iconify-icon>
                        </button>
                    )}
                </div>
            </div>

            {/* Background Image */}
            <div>
                <label className="block text-xs font-medium text-gray-600 mb-1">{__('Background image')}</label>

                {/* Image preview */}
                {background.image && (
                    <div className="mb-2 relative">
                        <img
                            src={background.image}
                            alt="Background preview"
                            className="w-full h-16 object-cover rounded border border-gray-200"
                        />
                        <button
                            type="button"
                            onClick={handleClearImage}
                            className="absolute top-1 right-1 p-1 bg-red-500 text-white rounded-full hover:bg-red-600"
                            title={__('Remove image')}
                        >
                            <iconify-icon icon="mdi:close" width="12" height="12"></iconify-icon>
                        </button>
                    </div>
                )}

                {/* Select from Media Library */}
                <button
                    type="button"
                    onClick={handleSelectImage}
                    disabled={isSelecting}
                    className="w-full border-2 border-dashed border-gray-200 rounded-lg p-3 text-center hover:border-primary/50 hover:bg-primary/5 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <div className="flex items-center justify-center gap-2 text-xs text-gray-500">
                        {isSelecting ? (
                            <>
                                <iconify-icon icon="mdi:loading" width="16" height="16" class="animate-spin"></iconify-icon>
                                <span>{__('Selecting...')}</span>
                            </>
                        ) : (
                            <>
                                <iconify-icon icon="mdi:image-plus" width="16" height="16" class="text-primary"></iconify-icon>
                                <span className="font-medium">{__('Select from Media Library')}</span>
                            </>
                        )}
                    </div>
                </button>

                {/* Or enter URL manually */}
                <div className="flex items-center gap-2 mt-2">
                    <span className="text-xs text-gray-400">{__('or')}</span>
                    <input
                        type="text"
                        value={background.image || ''}
                        onChange={(e) => handleImageChange(e.target.value)}
                        placeholder="Enter image URL..."
                        className="flex-1 px-2 py-1.5 text-xs bg-gray-50 border border-gray-200 rounded text-gray-700 placeholder-gray-400 focus:border-primary focus:outline-none"
                    />
                </div>
            </div>

            {/* Image options - only show when image is set */}
            {background.image && (
                <div className="space-y-2 pt-2 border-t border-gray-100">
                    {/* Size */}
                    <div>
                        <label className="block text-xs font-medium text-gray-600 mb-1">{__('Size')}</label>
                        <select
                            value={background.size || 'cover'}
                            onChange={(e) => onChange({ ...background, size: e.target.value })}
                            className="w-full px-2 py-1.5 text-xs bg-gray-100 border border-gray-200 rounded text-gray-700 focus:border-primary focus:outline-none"
                        >
                            {BACKGROUND_SIZE_PRESETS.map(preset => (
                                <option key={preset.value} value={preset.value}>{preset.label}</option>
                            ))}
                        </select>
                    </div>

                    {/* Position */}
                    <div>
                        <label className="block text-xs font-medium text-gray-600 mb-1">{__('Position')}</label>
                        <select
                            value={background.position || 'center'}
                            onChange={(e) => onChange({ ...background, position: e.target.value })}
                            className="w-full px-2 py-1.5 text-xs bg-gray-100 border border-gray-200 rounded text-gray-700 focus:border-primary focus:outline-none"
                        >
                            {BACKGROUND_POSITION_PRESETS.map(preset => (
                                <option key={preset.value} value={preset.value}>{preset.label}</option>
                            ))}
                        </select>
                    </div>

                    {/* Repeat */}
                    <div>
                        <label className="block text-xs font-medium text-gray-600 mb-1">{__('Repeat')}</label>
                        <select
                            value={background.repeat || 'no-repeat'}
                            onChange={(e) => onChange({ ...background, repeat: e.target.value })}
                            className="w-full px-2 py-1.5 text-xs bg-gray-100 border border-gray-200 rounded text-gray-700 focus:border-primary focus:outline-none"
                        >
                            {BACKGROUND_REPEAT_PRESETS.map(preset => (
                                <option key={preset.value} value={preset.value}>{preset.label}</option>
                            ))}
                        </select>
                    </div>
                </div>
            )}
        </div>
    );
};

export default BackgroundControls;
