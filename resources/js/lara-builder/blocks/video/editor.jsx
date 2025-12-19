/**
 * Video Block - Property Editor
 *
 * Renders the property fields for the video block in the properties panel.
 * Uses the media library for thumbnail selection.
 */

import { __ } from '@lara-builder/i18n';
import { mediaLibrary } from '../../services/MediaLibraryService';

const VideoBlockEditor = ({ props, onUpdate }) => {
    const handleChange = (field, value) => {
        onUpdate({ ...props, [field]: value });
    };

    const handleSelectThumbnail = async () => {
        try {
            const file = await mediaLibrary.selectImage();
            if (file) {
                handleChange('thumbnailUrl', file.url);
            }
        } catch (error) {
            // Selection cancelled
        }
    };

    const handleSelectVideo = async () => {
        try {
            const file = await mediaLibrary.selectVideo();
            if (file) {
                handleChange('videoUrl', file.url);
            }
        } catch (error) {
            // Selection cancelled
        }
    };

    const handleClearThumbnail = () => {
        handleChange('thumbnailUrl', '');
    };

    return (
        <div className="space-y-4">
            {/* Video Section */}
            <Section title={__('Video')}>
                <Label>{__('Video URL')}</Label>
                <div className="flex gap-2">
                    <input
                        type="url"
                        value={props.videoUrl || ''}
                        onChange={(e) => handleChange('videoUrl', e.target.value)}
                        placeholder="YouTube, Vimeo, or direct URL..."
                        className="form-control flex-1"
                    />
                    <button
                        type="button"
                        onClick={handleSelectVideo}
                        className="btn-default px-3"
                        title={__('Select from library')}
                    >
                        <iconify-icon icon="lucide:folder-open" width="16" height="16" />
                    </button>
                </div>
                <p className="text-xs text-gray-500 mt-1.5">
                    {__('YouTube, Vimeo, Dailymotion, Wistia, Loom, or direct files (mp4, webm)')}
                </p>
            </Section>

            {/* Thumbnail Section */}
            <Section title={__('Thumbnail')}>
                {props.thumbnailUrl ? (
                    <div className="relative group mb-3">
                        <img
                            src={props.thumbnailUrl}
                            alt="Thumbnail"
                            className="w-full max-h-32 object-contain rounded border border-gray-200 dark:border-gray-700"
                        />
                        <button
                            type="button"
                            onClick={handleClearThumbnail}
                            className="absolute top-2 right-2 p-1 bg-red-500 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity"
                            title={__('Remove thumbnail')}
                        >
                            <iconify-icon icon="lucide:x" width="14" height="14" />
                        </button>
                    </div>
                ) : (
                    <div className="flex flex-col items-center justify-center p-4 mb-3 bg-gray-50 dark:bg-gray-800 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg">
                        <iconify-icon icon="lucide:image" className="text-2xl text-gray-400 mb-1" />
                        <p className="text-xs text-gray-500 dark:text-gray-400">{__('Auto-generated from video')}</p>
                    </div>
                )}

                <button
                    type="button"
                    onClick={handleSelectThumbnail}
                    className="btn-default w-full flex items-center justify-center gap-2"
                >
                    <iconify-icon icon="lucide:image-plus" />
                    {props.thumbnailUrl ? __('Change Thumbnail') : __('Custom Thumbnail')}
                </button>

                <details className="mt-3">
                    <summary className="text-xs text-gray-500 dark:text-gray-400 cursor-pointer hover:text-gray-700 dark:hover:text-gray-300">
                        {__('Or enter URL manually')}
                    </summary>
                    <input
                        type="url"
                        value={props.thumbnailUrl || ''}
                        onChange={(e) => handleChange('thumbnailUrl', e.target.value)}
                        placeholder="https://..."
                        className="form-control mt-2"
                    />
                </details>
            </Section>

            {/* Accessibility */}
            <Section title={__('Accessibility')}>
                <Label>{__('Alt Text')}</Label>
                <input
                    type="text"
                    value={props.alt || ''}
                    onChange={(e) => handleChange('alt', e.target.value)}
                    placeholder="Describe the video..."
                    className="form-control"
                />
            </Section>

            {/* Appearance Section */}
            <Section title={__('Appearance')}>
                <Label>{__('Width')}</Label>
                <select
                    value={props.width || '100%'}
                    onChange={(e) => handleChange('width', e.target.value)}
                    className="form-control"
                >
                    <option value="100%">{__('Full Width')} (100%)</option>
                    <option value="75%">{__('Three Quarters')} (75%)</option>
                    <option value="50%">{__('Half')} (50%)</option>
                    <option value="25%">{__('Quarter')} (25%)</option>
                </select>

                <div className="mt-3">
                    <Label>{__('Play Button Color')}</Label>
                    <div className="flex gap-2">
                        <input
                            type="color"
                            value={props.playButtonColor || '#FF0000'}
                            onChange={(e) => handleChange('playButtonColor', e.target.value)}
                            className="w-12 h-9 rounded border border-gray-300 cursor-pointer"
                        />
                        <input
                            type="text"
                            value={props.playButtonColor || ''}
                            onChange={(e) => handleChange('playButtonColor', e.target.value)}
                            placeholder="#FF0000"
                            className="form-control flex-1"
                        />
                    </div>
                    <p className="text-xs text-gray-500 mt-1">{__('Leave empty for platform default')}</p>
                </div>
            </Section>
        </div>
    );
};

// Reusable Section Component
const Section = ({ title, children }) => (
    <div className="pb-4 border-b border-gray-200 dark:border-gray-700 last:border-b-0 last:pb-0">
        <h4 className="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3">
            {title}
        </h4>
        {children}
    </div>
);

// Reusable Label Component
const Label = ({ children }) => (
    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
        {children}
    </label>
);

export default VideoBlockEditor;
