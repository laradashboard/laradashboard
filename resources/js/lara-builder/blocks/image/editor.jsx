/**
 * Image Block - Property Editor
 *
 * Renders the property fields for the image block in the properties panel.
 * Uses the media library for image selection.
 */

import { mediaLibrary } from '../../services/MediaLibraryService';

const ImageBlockEditor = ({ props, onUpdate }) => {
    const handleChange = (field, value) => {
        onUpdate({ ...props, [field]: value });
    };

    const handleSelectFromLibrary = async () => {
        try {
            const file = await mediaLibrary.selectImage();
            if (file) {
                handleChange('src', file.url);
            }
        } catch (error) {
            // Selection was cancelled - do nothing
        }
    };

    const handleClearImage = () => {
        handleChange('src', '');
    };

    return (
        <div className="space-y-4">
            {/* Image Section */}
            <Section title="Image">
                {/* Image Preview */}
                {props.src ? (
                    <div className="relative group mb-3">
                        <img
                            src={props.src}
                            alt="Preview"
                            className="w-full max-h-40 object-contain rounded border border-gray-200 dark:border-gray-700"
                        />
                        <button
                            type="button"
                            onClick={handleClearImage}
                            className="absolute top-2 right-2 p-1 bg-red-500 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity"
                            title="Remove image"
                        >
                            <iconify-icon icon="lucide:x" width="14" height="14" />
                        </button>
                    </div>
                ) : (
                    <div className="flex flex-col items-center justify-center p-6 mb-3 bg-gray-50 dark:bg-gray-800 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg">
                        <iconify-icon icon="lucide:image" className="text-3xl text-gray-400 mb-2" />
                        <p className="text-sm text-gray-500 dark:text-gray-400">No image selected</p>
                    </div>
                )}

                {/* Select from Library Button */}
                <button
                    type="button"
                    onClick={handleSelectFromLibrary}
                    className="btn-default w-full flex items-center justify-center gap-2"
                >
                    <iconify-icon icon="lucide:image-plus" />
                    {props.src ? 'Change Image' : 'Select Image'}
                </button>

                {/* URL Input (collapsible alternative) */}
                <details className="mt-3">
                    <summary className="text-xs text-gray-500 dark:text-gray-400 cursor-pointer hover:text-gray-700 dark:hover:text-gray-300">
                        Or enter URL manually
                    </summary>
                    <input
                        type="url"
                        value={props.src || ''}
                        onChange={(e) => handleChange('src', e.target.value)}
                        placeholder="https://..."
                        className="form-control mt-2"
                    />
                </details>

                {/* Alt Text */}
                <div className="mt-3">
                    <Label>Alt Text</Label>
                    <input
                        type="text"
                        value={props.alt || ''}
                        onChange={(e) => handleChange('alt', e.target.value)}
                        placeholder="Describe the image..."
                        className="form-control"
                    />
                </div>
            </Section>

            {/* Link Section */}
            <Section title="Link">
                <Label>Link URL</Label>
                <input
                    type="url"
                    value={props.link || ''}
                    onChange={(e) => handleChange('link', e.target.value)}
                    placeholder="https://..."
                    className="form-control"
                />
            </Section>

            {/* Size Section */}
            <Section title="Size">
                <Label>Width</Label>
                <select
                    value={props.width || '100%'}
                    onChange={(e) => handleChange('width', e.target.value)}
                    className="form-control"
                >
                    <option value="100%">Full Width (100%)</option>
                    <option value="75%">Three Quarters (75%)</option>
                    <option value="50%">Half (50%)</option>
                    <option value="25%">Quarter (25%)</option>
                    <option value="custom">Custom</option>
                </select>

                {props.width === 'custom' && (
                    <div className="mt-3">
                        <Label>Custom Width</Label>
                        <input
                            type="text"
                            value={props.customWidth || ''}
                            onChange={(e) => handleChange('customWidth', e.target.value)}
                            placeholder="e.g., 300px"
                            className="form-control"
                        />
                    </div>
                )}

                <div className="mt-3">
                    <Label>Height</Label>
                    <select
                        value={props.height || 'auto'}
                        onChange={(e) => handleChange('height', e.target.value)}
                        className="form-control"
                    >
                        <option value="auto">Auto</option>
                        <option value="custom">Custom</option>
                    </select>
                </div>

                {props.height === 'custom' && (
                    <div className="mt-3">
                        <Label>Custom Height</Label>
                        <input
                            type="text"
                            value={props.customHeight || ''}
                            onChange={(e) => handleChange('customHeight', e.target.value)}
                            placeholder="e.g., 200px"
                            className="form-control"
                        />
                    </div>
                )}
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

export default ImageBlockEditor;
