/**
 * BackgroundControls - Background color and image settings
 */
import { useState } from 'react';
import {
    BACKGROUND_SIZE_PRESETS,
    BACKGROUND_POSITION_PRESETS,
    BACKGROUND_REPEAT_PRESETS,
} from './presets';

const BackgroundControls = ({ background = {}, onChange, onImageUpload }) => {
    const [isUploading, setIsUploading] = useState(false);

    const handleColorChange = (color) => {
        onChange({ ...background, color });
    };

    const handleImageChange = (image) => {
        onChange({ ...background, image });
    };

    const handleImageUpload = async (e) => {
        const file = e.target.files?.[0];
        if (!file || !onImageUpload) return;

        setIsUploading(true);
        try {
            const result = await onImageUpload(file);
            if (result.url) {
                handleImageChange(result.url);
            }
        } catch (err) {
            console.error('Failed to upload image:', err);
        } finally {
            setIsUploading(false);
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
                <label className="block text-xs font-medium text-gray-600 mb-1">Background color</label>
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
                            title="Clear"
                        >
                            <iconify-icon icon="mdi:close" width="14" height="14"></iconify-icon>
                        </button>
                    )}
                </div>
            </div>

            {/* Background Image */}
            <div>
                <label className="block text-xs font-medium text-gray-600 mb-1">Background image</label>

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
                            title="Remove image"
                        >
                            <iconify-icon icon="mdi:close" width="12" height="12"></iconify-icon>
                        </button>
                    </div>
                )}

                {/* Upload / URL input */}
                <div className="border-2 border-dashed border-gray-200 rounded-lg p-3 text-center hover:border-gray-300 transition-colors">
                    {onImageUpload && (
                        <label className={`cursor-pointer block mb-2 ${isUploading ? 'opacity-50' : ''}`}>
                            <div className="flex items-center justify-center gap-2 text-xs text-gray-500">
                                {isUploading ? (
                                    <>
                                        <iconify-icon icon="mdi:loading" width="16" height="16" class="animate-spin"></iconify-icon>
                                        <span>Uploading...</span>
                                    </>
                                ) : (
                                    <>
                                        <iconify-icon icon="mdi:image-plus" width="16" height="16"></iconify-icon>
                                        <span>SELECT IMAGE</span>
                                    </>
                                )}
                            </div>
                            <input
                                type="file"
                                accept="image/*"
                                onChange={handleImageUpload}
                                className="hidden"
                                disabled={isUploading}
                            />
                        </label>
                    )}

                    <div className="flex items-center gap-2">
                        <input
                            type="text"
                            value={background.image || ''}
                            onChange={(e) => handleImageChange(e.target.value)}
                            placeholder="Custom URL"
                            className="flex-1 px-2 py-1.5 text-xs bg-white border border-gray-200 rounded text-gray-700 placeholder-gray-400 focus:border-primary focus:outline-none"
                        />
                        <iconify-icon icon="mdi:lightning-bolt" width="14" height="14" class="text-gray-400"></iconify-icon>
                    </div>
                </div>
            </div>

            {/* Image options - only show when image is set */}
            {background.image && (
                <div className="space-y-2 pt-2 border-t border-gray-100">
                    {/* Size */}
                    <div>
                        <label className="block text-xs font-medium text-gray-600 mb-1">Size</label>
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
                        <label className="block text-xs font-medium text-gray-600 mb-1">Position</label>
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
                        <label className="block text-xs font-medium text-gray-600 mb-1">Repeat</label>
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
