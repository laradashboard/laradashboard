import { useState } from 'react';
import { getBlock } from '../../email-builder/utils/blockRegistry';
import { parseVideoUrl } from '../blocks/video/block';
import LayoutStylesSection from './LayoutStylesSection';
import CollapsibleSection from './CollapsibleSection';
import { blockRegistry } from '../registry/BlockRegistry';

const PropertiesPanel = ({ selectedBlock, onUpdate, onImageUpload, onVideoUpload, canvasSettings, onCanvasSettingsUpdate }) => {
    const [uploading, setUploading] = useState(false);
    const [uploadError, setUploadError] = useState(null);
    const [videoUploading, setVideoUploading] = useState(false);
    const [videoUploadError, setVideoUploadError] = useState(null);

    // Handle canvas layout styles update
    const handleCanvasLayoutStylesUpdate = (newLayoutStyles) => {
        onCanvasSettingsUpdate({ ...canvasSettings, layoutStyles: newLayoutStyles });
    };

    // Show canvas settings when no block is selected
    if (!selectedBlock) {
        return (
            <div className="h-full overflow-y-auto px-1">
                <div className="mb-4 pb-3 border-b border-gray-200">
                    <span className="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        Email Settings
                    </span>
                </div>

                {/* Width */}
                <div className="mb-4">
                    <label className="block text-sm font-medium text-gray-700 mb-1">Email Width</label>
                    <select
                        value={canvasSettings?.width || '700px'}
                        onChange={(e) => onCanvasSettingsUpdate({ ...canvasSettings, width: e.target.value })}
                        className="form-control"
                    >
                        <option value="500px">500px (Narrow)</option>
                        <option value="600px">600px (Standard)</option>
                        <option value="700px">700px (Wide)</option>
                        <option value="800px">800px (Extra Wide)</option>
                    </select>
                </div>

                {/* Content Layout Styles - Same as blocks */}
                <LayoutStylesSection
                    layoutStyles={canvasSettings?.layoutStyles || {}}
                    onUpdate={handleCanvasLayoutStylesUpdate}
                    onImageUpload={onImageUpload}
                    defaultCollapsed={false}
                />

                <div className="mt-6 pt-4 border-t border-gray-200">
                    <p className="text-xs text-gray-400 text-center">
                        Click on a block to edit its properties
                    </p>
                </div>
            </div>
        );
    }

    const blockConfig = getBlock(selectedBlock.type);
    const props = selectedBlock.props || {};

    const handleChange = (field, value) => {
        onUpdate(selectedBlock.id, { ...props, [field]: value });
    };

    const handleImageUpload = async (e) => {
        const file = e.target.files?.[0];
        if (!file) return;

        // Validate file type
        if (!file.type.startsWith('image/')) {
            setUploadError('Please select an image file');
            return;
        }

        // Validate file size (2MB max)
        if (file.size > 2 * 1024 * 1024) {
            setUploadError('Image must be less than 2MB');
            return;
        }

        setUploading(true);
        setUploadError(null);

        try {
            const result = await onImageUpload(file);
            if (result.success && result.url) {
                handleChange('src', result.url);
            } else {
                setUploadError(result.message || 'Failed to upload image');
            }
        } catch (error) {
            setUploadError(error.message || 'Failed to upload image');
        } finally {
            setUploading(false);
            // Reset the input
            e.target.value = '';
        }
    };

    const renderImageUploadField = () => {
        const value = props['src'] ?? '';

        return (
            <div key="image-upload" className="mb-4">
                <label className="block text-sm font-medium text-gray-700 mb-1">Image</label>

                {/* Current image preview */}
                {value && (
                    <div className="mb-2 relative">
                        <img
                            src={value}
                            alt="Preview"
                            className="w-full h-24 object-cover rounded-md border border-gray-200"
                            onError={(e) => {
                                e.target.style.display = 'none';
                            }}
                        />
                    </div>
                )}

                {/* Upload button */}
                <div className="mb-2">
                    <label className={`flex items-center justify-center gap-2 px-3 py-2 border border-gray-300 rounded-md cursor-pointer hover:bg-gray-50 transition-colors ${uploading ? 'opacity-50 cursor-not-allowed' : ''}`}>
                        {uploading ? (
                            <>
                                <svg className="animate-spin h-4 w-4 text-gray-500" viewBox="0 0 24 24">
                                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" fill="none" />
                                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                                </svg>
                                <span className="text-sm text-gray-600">Uploading...</span>
                            </>
                        ) : (
                            <>
                                <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span className="text-sm text-gray-600">Upload Image</span>
                            </>
                        )}
                        <input
                            type="file"
                            accept="image/*"
                            onChange={handleImageUpload}
                            disabled={uploading}
                            className="hidden"
                        />
                    </label>
                </div>

                {/* Upload error */}
                {uploadError && (
                    <p className="text-xs text-red-500 mb-2">{uploadError}</p>
                )}

                {/* Or enter URL manually */}
                <div className="relative">
                    <span className="absolute left-0 top-1/2 -translate-y-1/2 text-xs text-gray-400 px-2">or</span>
                    <input
                        type="text"
                        value={value}
                        onChange={(e) => handleChange('src', e.target.value)}
                        className="form-control text-xs flex-1 pl-8"
                        placeholder="Enter image URL..."
                    />
                </div>

                <p className="text-xs text-gray-400 mt-1">Max file size: 2MB. Formats: JPG, PNG, GIF, WebP</p>
            </div>
        );
    };

    const renderField = (field, label, type = 'text', options = {}) => {
        const value = props[field] ?? '';

        switch (type) {
            case 'select':
                return (
                    <div key={field} className="mb-4">
                        <label className="form-label">{label}</label>
                        <select
                            value={value}
                            onChange={(e) => handleChange(field, e.target.value)}
                            className="form-control"
                        >
                            {options.choices?.map(choice => (
                                <option key={choice.value} value={choice.value}>{choice.label}</option>
                            ))}
                        </select>
                    </div>
                );

            case 'color':
                return (
                    <div key={field} className="mb-4">
                        <label className="form-label">{label}</label>
                        <div className="flex gap-2">
                            <input
                                type="color"
                                value={value || '#000000'}
                                onChange={(e) => handleChange(field, e.target.value)}
                                className="h-10 w-12 border border-gray-300 rounded cursor-pointer"
                            />
                            <input
                                type="text"
                                value={value || ''}
                                onChange={(e) => handleChange(field, e.target.value)}
                                className="form-control"
                                placeholder="#000000"
                            />
                        </div>
                    </div>
                );

            case 'textarea':
                return (
                    <div key={field} className="mb-4">
                        <label className="form-label">{label}</label>
                        <textarea
                            value={value}
                            onChange={(e) => handleChange(field, e.target.value)}
                            rows={4}
                            className="form-control-textarea"
                        />
                    </div>
                );

            case 'align':
                return (
                    <div key={field} className="mb-4">
                        <label className="form-label">{label}</label>
                        <div className="flex gap-1">
                            {['left', 'center', 'right'].map(align => (
                                <button
                                    key={align}
                                    type="button"
                                    onClick={() => handleChange(field, align)}
                                    className={`flex-1 px-3 py-2 text-sm rounded-md capitalize ${
                                        value === align
                                            ? 'btn-primary'
                                            : 'btn-default'
                                    }`}
                                >
                                    {align}
                                </button>
                            ))}
                        </div>
                    </div>
                );

            case 'image-upload':
                return renderImageUploadField();

            case 'image-dimension':
                return (
                    <div key={field} className="mb-4">
                        <label className="form-label">{label}</label>
                        <select
                            value={value}
                            onChange={(e) => {
                                handleChange(field, e.target.value);
                                if (e.target.value !== 'custom' && options.customField) {
                                    handleChange(options.customField, '');
                                }
                            }}
                            className="form-control"
                        >
                            {options.choices?.map(choice => (
                                <option key={choice.value} value={choice.value}>{choice.label}</option>
                            ))}
                        </select>
                        {value === 'custom' && options.customField && (
                            <input
                                type="text"
                                value={props[options.customField] || ''}
                                onChange={(e) => handleChange(options.customField, e.target.value)}
                                placeholder={options.placeholder || 'Enter value...'}
                                className="form-control mt-2"
                            />
                        )}
                    </div>
                );

            case 'checkbox':
                return (
                    <div key={field} className="mb-4">
                        <label className="flex items-center gap-2 cursor-pointer">
                            <input
                                type="checkbox"
                                checked={value || false}
                                onChange={(e) => handleChange(field, e.target.checked)}
                                className="form-checkbox"
                            />
                            <span className="text-sm font-medium text-gray-700">{label}</span>
                        </label>
                    </div>
                );

            case 'date':
                return (
                    <div key={field} className="mb-4">
                        <label className="form-label">{label}</label>
                        <input
                            type="date"
                            value={value || ''}
                            onChange={(e) => handleChange(field, e.target.value)}
                            className="form-control"
                        />
                    </div>
                );

            case 'time':
                return (
                    <div key={field} className="mb-4">
                        <label className="form-label">{label}</label>
                        <input
                            type="time"
                            value={value || '23:59'}
                            onChange={(e) => handleChange(field, e.target.value)}
                            className="form-control"
                        />
                    </div>
                );

            case 'list-items':
                const items = value || [];
                return (
                    <div key={field} className="mb-4">
                        <label className="form-label">{label}</label>
                        <div className="space-y-2">
                            {items.map((item, index) => (
                                <div key={index} className="flex gap-2">
                                    <input
                                        type="text"
                                        value={item}
                                        onChange={(e) => {
                                            const newItems = [...items];
                                            newItems[index] = e.target.value;
                                            handleChange(field, newItems);
                                        }}
                                        className="form-control"
                                        placeholder={`Item ${index + 1}`}
                                    />
                                    <button
                                        type="button"
                                        onClick={() => {
                                            const newItems = items.filter((_, i) => i !== index);
                                            handleChange(field, newItems);
                                        }}
                                        className="btn-danger px-2 py-1 text-xs"
                                    >
                                        <iconify-icon icon="mdi:close" width="16" height="16"></iconify-icon>
                                    </button>
                                </div>
                            ))}
                            <button
                                type="button"
                                onClick={() => handleChange(field, [...items, ''])}
                                className="btn-secondary w-full text-xs"
                            >
                                <iconify-icon icon="mdi:plus" width="16" height="16" class="mr-1"></iconify-icon>
                                Add Item
                            </button>
                        </div>
                    </div>
                );

            case 'table-headers':
                const headers = value || [];
                return (
                    <div key={field} className="mb-4">
                        <label className="form-label">{label}</label>
                        <div className="space-y-2">
                            {headers.map((header, index) => (
                                <div key={index} className="flex gap-2">
                                    <input
                                        type="text"
                                        value={header}
                                        onChange={(e) => {
                                            const newHeaders = [...headers];
                                            newHeaders[index] = e.target.value;
                                            handleChange(field, newHeaders);
                                        }}
                                        className="form-control"
                                        placeholder={`Header ${index + 1}`}
                                    />
                                    <button
                                        type="button"
                                        onClick={() => {
                                            // Update both headers and rows in a single call to avoid race condition
                                            const newHeaders = headers.filter((_, i) => i !== index);
                                            const newRows = (props.rows || []).map(row =>
                                                row.filter((_, i) => i !== index)
                                            );
                                            onUpdate(selectedBlock.id, {
                                                ...props,
                                                headers: newHeaders,
                                                rows: newRows,
                                            });
                                        }}
                                        className="btn-danger px-2 py-1 text-xs"
                                    >
                                        <iconify-icon icon="mdi:close" width="16" height="16"></iconify-icon>
                                    </button>
                                </div>
                            ))}
                            <button
                                type="button"
                                onClick={() => {
                                    // Update both headers and rows in a single call to avoid race condition
                                    const newHeaders = [...headers, ''];
                                    const newRows = (props.rows || []).map(row => [...row, '']);
                                    onUpdate(selectedBlock.id, {
                                        ...props,
                                        headers: newHeaders,
                                        rows: newRows,
                                    });
                                }}
                                className="btn-secondary w-full text-xs"
                            >
                                <iconify-icon icon="mdi:plus" width="16" height="16" class="mr-1"></iconify-icon>
                                Add Column
                            </button>
                        </div>
                    </div>
                );

            case 'table-rows':
                const rows = value || [];
                const headerCount = (props.headers || []).length || 3;
                return (
                    <div key={field} className="mb-4">
                        <label className="form-label">{label}</label>
                        <div className="space-y-3">
                            {rows.map((row, rowIndex) => (
                                <div key={rowIndex} className="p-2 bg-gray-50 rounded-md">
                                    <div className="flex items-center justify-between mb-2">
                                        <span className="text-xs font-medium text-gray-500">Row {rowIndex + 1}</span>
                                        <button
                                            type="button"
                                            onClick={() => {
                                                const newRows = rows.filter((_, i) => i !== rowIndex);
                                                handleChange(field, newRows);
                                            }}
                                            className="text-red-500 hover:text-red-700"
                                        >
                                            <iconify-icon icon="mdi:close" width="16" height="16"></iconify-icon>
                                        </button>
                                    </div>
                                    <div className="space-y-1">
                                        {row.map((cell, cellIndex) => (
                                            <input
                                                key={cellIndex}
                                                type="text"
                                                value={cell}
                                                onChange={(e) => {
                                                    const newRows = [...rows];
                                                    newRows[rowIndex] = [...row];
                                                    newRows[rowIndex][cellIndex] = e.target.value;
                                                    handleChange(field, newRows);
                                                }}
                                                className="form-control text-xs"
                                                placeholder={props.headers?.[cellIndex] || `Col ${cellIndex + 1}`}
                                            />
                                        ))}
                                    </div>
                                </div>
                            ))}
                            <button
                                type="button"
                                onClick={() => {
                                    const newRow = Array(headerCount).fill('');
                                    handleChange(field, [...rows, newRow]);
                                }}
                                className="btn-secondary w-full text-xs"
                            >
                                <iconify-icon icon="mdi:plus" width="16" height="16" class="mr-1"></iconify-icon>
                                Add Row
                            </button>
                        </div>
                    </div>
                );

            case 'thumbnail-upload':
                return (
                    <div key={field} className="mb-4">
                        <label className="form-label">{label}</label>
                        <p className="text-xs text-gray-400 mb-2">
                            Leave empty to use auto-detected thumbnail from video URL
                        </p>

                        {/* Show current custom thumbnail if set */}
                        {value && (
                            <div className="mb-2 relative">
                                <img
                                    src={value}
                                    alt="Custom thumbnail"
                                    className="w-full h-auto rounded-md border border-gray-200"
                                />
                                <button
                                    type="button"
                                    onClick={() => handleChange(field, '')}
                                    className="absolute top-1 right-1 p-1 bg-red-500 text-white rounded-full hover:bg-red-600"
                                    title="Remove custom thumbnail"
                                >
                                    <iconify-icon icon="mdi:close" width="14" height="14"></iconify-icon>
                                </button>
                            </div>
                        )}

                        {/* URL input */}
                        <input
                            type="text"
                            value={value || ''}
                            onChange={(e) => handleChange(field, e.target.value)}
                            className="form-control text-xs"
                            placeholder="Paste custom thumbnail URL..."
                        />
                    </div>
                );

            case 'social-links':
                const socialPlatforms = [
                    { key: 'facebook', label: 'Facebook', icon: 'mdi:facebook', placeholder: 'https://facebook.com/yourpage' },
                    { key: 'twitter', label: 'Twitter/X', icon: 'mdi:twitter', placeholder: 'https://twitter.com/yourhandle' },
                    { key: 'instagram', label: 'Instagram', icon: 'mdi:instagram', placeholder: 'https://instagram.com/yourhandle' },
                    { key: 'linkedin', label: 'LinkedIn', icon: 'mdi:linkedin', placeholder: 'https://linkedin.com/in/yourprofile' },
                    { key: 'youtube', label: 'YouTube', icon: 'mdi:youtube', placeholder: 'https://youtube.com/@yourchannel' },
                ];
                const links = value || {};
                return (
                    <div key={field} className="mb-4">
                        <label className="form-label">{label}</label>
                        <div className="space-y-3">
                            {socialPlatforms.map(({ key, label: platformLabel, icon, placeholder }) => (
                                <div key={key}>
                                    <div className="flex items-center gap-2 mb-1">
                                        <iconify-icon icon={icon} width="16" height="16" class="text-gray-500"></iconify-icon>
                                        <span className="text-xs font-medium text-gray-600">{platformLabel}</span>
                                    </div>
                                    <input
                                        type="text"
                                        value={links[key] || ''}
                                        onChange={(e) => {
                                            const newLinks = { ...links, [key]: e.target.value };
                                            handleChange(field, newLinks);
                                        }}
                                        placeholder={placeholder}
                                        className="form-control text-sm"
                                    />
                                </div>
                            ))}
                        </div>
                        <p className="text-xs text-gray-400 mt-2">Only platforms with URLs will be shown</p>
                    </div>
                );

            case 'video-url':
                const videoInfo = parseVideoUrl(value);
                const platformColors = {
                    youtube: { bg: 'bg-red-50', text: 'text-red-600', border: 'border-red-200' },
                    vimeo: { bg: 'bg-cyan-50', text: 'text-cyan-600', border: 'border-cyan-200' },
                    dailymotion: { bg: 'bg-sky-50', text: 'text-sky-600', border: 'border-sky-200' },
                    wistia: { bg: 'bg-sky-50', text: 'text-sky-600', border: 'border-sky-200' },
                    loom: { bg: 'bg-purple-50', text: 'text-purple-600', border: 'border-purple-200' },
                };
                const platformIcons = {
                    youtube: 'mdi:youtube',
                    vimeo: 'mdi:vimeo',
                    dailymotion: 'simple-icons:dailymotion',
                    wistia: 'mdi:video-box',
                    loom: 'mdi:video-outline',
                };

                // Helper function to capture first frame from video file
                const captureVideoThumbnail = (videoFile) => {
                    return new Promise((resolve) => {
                        const video = document.createElement('video');
                        video.preload = 'metadata';
                        video.muted = true;
                        video.playsInline = true;

                        video.onloadeddata = () => {
                            // Seek to 1 second or 10% of video, whichever is smaller
                            video.currentTime = Math.min(1, video.duration * 0.1);
                        };

                        video.onseeked = () => {
                            try {
                                const canvas = document.createElement('canvas');
                                canvas.width = video.videoWidth || 640;
                                canvas.height = video.videoHeight || 360;
                                const ctx = canvas.getContext('2d');
                                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                                // Convert canvas to blob
                                canvas.toBlob((blob) => {
                                    URL.revokeObjectURL(video.src);
                                    resolve(blob);
                                }, 'image/jpeg', 0.85);
                            } catch (e) {
                                console.error('Failed to capture thumbnail:', e);
                                URL.revokeObjectURL(video.src);
                                resolve(null);
                            }
                        };

                        video.onerror = () => {
                            URL.revokeObjectURL(video.src);
                            resolve(null);
                        };

                        // Set timeout in case video fails to load
                        setTimeout(() => {
                            URL.revokeObjectURL(video.src);
                            resolve(null);
                        }, 10000);

                        video.src = URL.createObjectURL(videoFile);
                    });
                };

                const handleVideoFileUpload = async (e) => {
                    const file = e.target.files?.[0];
                    if (!file || !onVideoUpload) return;

                    setVideoUploading(true);
                    setVideoUploadError(null);

                    try {
                        // Capture thumbnail from first frame
                        const thumbnailBlob = await captureVideoThumbnail(file);

                        // Convert blob to File if captured successfully
                        let thumbnailFile = null;
                        if (thumbnailBlob) {
                            thumbnailFile = new File([thumbnailBlob], 'thumbnail.jpg', { type: 'image/jpeg' });
                        }

                        // Upload video with auto-generated thumbnail
                        const result = await onVideoUpload(file, thumbnailFile);
                        if (result.success) {
                            // Update both videoUrl and thumbnailUrl together to avoid stale props issue
                            const updates = { videoUrl: result.videoUrl };
                            if (result.thumbnailUrl) {
                                updates.thumbnailUrl = result.thumbnailUrl;
                            }
                            onUpdate(selectedBlock.id, { ...props, ...updates });
                        } else {
                            setVideoUploadError(result.message || 'Upload failed');
                        }
                    } catch (err) {
                        setVideoUploadError(err.message || 'Upload failed');
                    } finally {
                        setVideoUploading(false);
                    }
                };

                return (
                    <div key={field} className="mb-4">
                        <label className="form-label">{label}</label>

                        {/* Video source tabs */}
                        <div className="flex gap-1 mb-2">
                            <button
                                type="button"
                                onClick={() => handleChange('_videoSource', 'url')}
                                className={`flex-1 px-2 py-1.5 text-xs rounded-md ${
                                    props._videoSource !== 'upload' ? 'btn-primary' : 'btn-default'
                                }`}
                            >
                                <iconify-icon icon="mdi:link" width="14" height="14" class="mr-1"></iconify-icon>
                                URL / Embed
                            </button>
                            <button
                                type="button"
                                onClick={() => handleChange('_videoSource', 'upload')}
                                className={`flex-1 px-2 py-1.5 text-xs rounded-md ${
                                    props._videoSource === 'upload' ? 'btn-primary' : 'btn-default'
                                }`}
                            >
                                <iconify-icon icon="mdi:upload" width="14" height="14" class="mr-1"></iconify-icon>
                                Upload
                            </button>
                        </div>

                        {props._videoSource === 'upload' ? (
                            /* Upload video section */
                            <div className="space-y-2">
                                <div className="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-primary transition-colors">
                                    <input
                                        type="file"
                                        accept="video/mp4,video/webm,video/ogg,video/mov"
                                        onChange={handleVideoFileUpload}
                                        className="hidden"
                                        id={`video-upload-${selectedBlock?.id}`}
                                        disabled={videoUploading}
                                    />
                                    <label htmlFor={`video-upload-${selectedBlock?.id}`} className="cursor-pointer">
                                        {videoUploading ? (
                                            <div className="flex flex-col items-center gap-2">
                                                <iconify-icon icon="mdi:loading" width="24" height="24" class="text-primary animate-spin"></iconify-icon>
                                                <span className="text-xs text-gray-500">Uploading video...</span>
                                            </div>
                                        ) : (
                                            <div className="flex flex-col items-center gap-2">
                                                <iconify-icon icon="mdi:cloud-upload-outline" width="32" height="32" class="text-gray-400"></iconify-icon>
                                                <span className="text-xs text-gray-600">Click to upload video</span>
                                                <span className="text-xs text-gray-400">MP4, WebM, OGG (max 50MB)</span>
                                            </div>
                                        )}
                                    </label>
                                </div>
                                {videoUploadError && (
                                    <p className="text-xs text-red-500">
                                        <iconify-icon icon="mdi:alert-circle" width="14" height="14" class="mr-1"></iconify-icon>
                                        {videoUploadError}
                                    </p>
                                )}
                                {value && !videoInfo && (
                                    <div className="p-2 bg-green-50 border border-green-200 rounded-md">
                                        <div className="flex items-center gap-2 text-xs text-green-600">
                                            <iconify-icon icon="mdi:check-circle" width="16" height="16"></iconify-icon>
                                            <span>Video uploaded successfully</span>
                                        </div>
                                        <p className="text-xs text-gray-500 mt-1 truncate">{value}</p>
                                    </div>
                                )}
                            </div>
                        ) : (
                            /* URL input section */
                            <>
                                <input
                                    type="text"
                                    value={value || ''}
                                    onChange={(e) => handleChange(field, e.target.value)}
                                    className="form-control"
                                    placeholder="Paste YouTube, Vimeo, or video URL..."
                                />
                                {videoInfo ? (
                                    <div className={`mt-2 p-2 rounded-md border ${platformColors[videoInfo.platform]?.bg || 'bg-gray-50'} ${platformColors[videoInfo.platform]?.border || 'border-gray-200'}`}>
                                        <div className={`flex items-center gap-2 text-xs font-medium ${platformColors[videoInfo.platform]?.text || 'text-gray-600'}`}>
                                            <iconify-icon icon={platformIcons[videoInfo.platform] || 'mdi:play-circle'} width="16" height="16"></iconify-icon>
                                            <span className="capitalize">{videoInfo.platform} video detected</span>
                                        </div>
                                        {videoInfo.thumbnailUrl && (
                                            <p className="text-xs text-gray-500 mt-1">Thumbnail will be auto-loaded</p>
                                        )}
                                    </div>
                                ) : value ? (
                                    <p className="text-xs text-amber-600 mt-1">
                                        <iconify-icon icon="mdi:alert-circle-outline" width="14" height="14" class="mr-1"></iconify-icon>
                                        Custom URL - add a thumbnail below
                                    </p>
                                ) : (
                                    <div className="mt-2 text-xs text-gray-400">
                                        <p className="font-medium mb-1">Supported platforms:</p>
                                        <div className="flex flex-wrap gap-1">
                                            {['YouTube', 'Vimeo', 'Dailymotion', 'Wistia', 'Loom'].map(p => (
                                                <span key={p} className="px-1.5 py-0.5 bg-gray-100 rounded text-gray-500">{p}</span>
                                            ))}
                                        </div>
                                    </div>
                                )}
                            </>
                        )}
                    </div>
                );

            case 'info':
                return (
                    <div key={field} className="mb-4">
                        <div className="flex items-start gap-2 p-3 bg-blue-50 border border-blue-200 rounded-md">
                            <iconify-icon icon="mdi:information" width="18" height="18" class="text-blue-500 flex-shrink-0 mt-0.5"></iconify-icon>
                            <p className="text-sm text-blue-700">{options.value || value}</p>
                        </div>
                    </div>
                );

            default:
                return (
                    <div key={field} className="mb-4">
                        <label className="form-label">{label}</label>
                        <input
                            type={type}
                            value={value}
                            onChange={(e) => handleChange(field, e.target.value)}
                            className="form-control"
                        />
                    </div>
                );
        }
    };

    // Define editable fields based on block type with sections
    const getSectionsForBlockType = (type) => {
        // First check the registry for custom property sections (external modules)
        const blockDef = blockRegistry.get(type);
        if (blockDef?.propertySections) {
            // If it's a function, call it with props; otherwise use as-is
            return typeof blockDef.propertySections === 'function'
                ? blockDef.propertySections(props)
                : blockDef.propertySections;
        }

        switch (type) {
            case 'heading':
                // Heading level is controlled from the toolbar, no sidebar settings needed
                return [];

            case 'text':
                // Text settings now use Typography section in Layout Styles
                return [];

            case 'image':
                return [
                    {
                        title: 'Image Settings',
                        icon: 'mdi:image',
                        iconColor: 'text-primary/100',
                        defaultExpanded: true,
                        fields: [
                            { field: 'src', label: 'Image', type: 'image-upload' },
                            { field: 'alt', label: 'Alt Text', type: 'text' },
                            { field: 'link', label: 'Link URL', type: 'text' },
                            { field: 'width', label: 'Width', type: 'image-dimension', options: {
                                customField: 'customWidth',
                                choices: [
                                    { value: '100%', label: 'Full Width' },
                                    { value: '75%', label: '75%' },
                                    { value: '50%', label: '50%' },
                                    { value: '25%', label: '25%' },
                                    { value: 'custom', label: 'Custom' },
                                ],
                                placeholder: 'e.g., 300px or 50%'
                            }},
                            { field: 'height', label: 'Height', type: 'image-dimension', options: {
                                customField: 'customHeight',
                                choices: [
                                    { value: 'auto', label: 'Auto' },
                                    { value: '100px', label: '100px' },
                                    { value: '150px', label: '150px' },
                                    { value: '200px', label: '200px' },
                                    { value: '250px', label: '250px' },
                                    { value: '300px', label: '300px' },
                                    { value: '400px', label: '400px' },
                                    { value: 'custom', label: 'Custom' },
                                ],
                                placeholder: 'e.g., 200px'
                            }},
                        ]
                    }
                ];

            case 'button':
                return [
                    {
                        title: 'Button Settings',
                        icon: 'mdi:button-cursor',
                        iconColor: 'text-primary/100',
                        defaultExpanded: true,
                        fields: [
                            { field: 'text', label: 'Button Text', type: 'text' },
                            { field: 'link', label: 'Link URL', type: 'text' },
                            { field: 'borderRadius', label: 'Border Radius', type: 'select', options: {
                                choices: [
                                    { value: '0', label: 'None' },
                                    { value: '4px', label: 'Small' },
                                    { value: '6px', label: 'Medium' },
                                    { value: '8px', label: 'Large' },
                                    { value: '9999px', label: 'Pill' },
                                ]
                            }},
                        ]
                    },
                    {
                        title: 'Colors',
                        icon: 'mdi:palette',
                        iconColor: 'text-primary/100',
                        defaultExpanded: false,
                        fields: [
                            { field: 'backgroundColor', label: 'Background Color', type: 'color' },
                            { field: 'textColor', label: 'Text Color', type: 'color' },
                        ]
                    }
                ];

            case 'divider':
                return [
                    {
                        title: 'Divider Settings',
                        icon: 'mdi:minus',
                        iconColor: 'text-primary/100',
                        defaultExpanded: true,
                        fields: [
                            { field: 'style', label: 'Style', type: 'select', options: {
                                choices: [
                                    { value: 'solid', label: 'Solid' },
                                    { value: 'dashed', label: 'Dashed' },
                                    { value: 'dotted', label: 'Dotted' },
                                ]
                            }},
                            { field: 'color', label: 'Color', type: 'color' },
                            { field: 'thickness', label: 'Thickness', type: 'select', options: {
                                choices: [
                                    { value: '1px', label: '1px' },
                                    { value: '2px', label: '2px' },
                                    { value: '3px', label: '3px' },
                                    { value: '4px', label: '4px' },
                                ]
                            }},
                        ]
                    }
                ];

            case 'spacer':
                return [
                    {
                        title: 'Spacer Settings',
                        icon: 'mdi:arrow-expand-vertical',
                        iconColor: 'text-primary/100',
                        defaultExpanded: true,
                        fields: [
                            { field: 'height', label: 'Height', type: 'select', options: {
                                choices: [
                                    { value: '10px', label: '10px' },
                                    { value: '20px', label: '20px' },
                                    { value: '30px', label: '30px' },
                                    { value: '40px', label: '40px' },
                                    { value: '60px', label: '60px' },
                                    { value: '80px', label: '80px' },
                                ]
                            }},
                        ]
                    }
                ];

            case 'columns':
                return [
                    {
                        title: 'Columns Settings',
                        icon: 'mdi:view-column',
                        iconColor: 'text-primary/100',
                        defaultExpanded: true,
                        fields: [
                            { field: 'columns', label: 'Number of Columns', type: 'select', options: {
                                choices: [
                                    { value: '1', label: '1 Column (Container)' },
                                    { value: '2', label: '2 Columns' },
                                    { value: '3', label: '3 Columns' },
                                    { value: '4', label: '4 Columns' },
                                    { value: '5', label: '5 Columns' },
                                    { value: '6', label: '6 Columns' },
                                ]
                            }},
                            { field: 'gap', label: 'Column Gap', type: 'select', options: {
                                choices: [
                                    { value: '10px', label: '10px' },
                                    { value: '20px', label: '20px' },
                                    { value: '30px', label: '30px' },
                                    { value: '40px', label: '40px' },
                                ]
                            }},
                        ]
                    }
                ];

            case 'html':
                return [
                    {
                        title: 'HTML Settings',
                        icon: 'mdi:code-tags',
                        iconColor: 'text-primary/100',
                        defaultExpanded: true,
                        fields: [
                            { field: '_info', label: '', type: 'info', options: { value: 'Double-click the block on canvas to open the HTML editor.' } },
                        ]
                    }
                ];

            case 'quote':
                return [
                    {
                        title: 'Quote Content',
                        icon: 'mdi:format-quote-close',
                        iconColor: 'text-primary/100',
                        defaultExpanded: true,
                        fields: [
                            { field: 'text', label: 'Quote Text', type: 'textarea' },
                            { field: 'author', label: 'Author Name', type: 'text' },
                            { field: 'authorTitle', label: 'Author Title', type: 'text' },
                        ]
                    },
                    {
                        title: 'Colors',
                        icon: 'mdi:palette',
                        iconColor: 'text-primary/100',
                        defaultExpanded: false,
                        fields: [
                            { field: 'borderColor', label: 'Border Color', type: 'color' },
                            { field: 'backgroundColor', label: 'Background Color', type: 'color' },
                            { field: 'textColor', label: 'Text Color', type: 'color' },
                            { field: 'authorColor', label: 'Author Color', type: 'color' },
                        ]
                    }
                ];

            case 'list':
                return [
                    {
                        title: 'List Settings',
                        icon: 'mdi:format-list-bulleted',
                        iconColor: 'text-primary/100',
                        defaultExpanded: true,
                        fields: [
                            { field: 'listType', label: 'List Type', type: 'select', options: {
                                choices: [
                                    { value: 'bullet', label: 'Bullet' },
                                    { value: 'number', label: 'Numbered' },
                                    { value: 'check', label: 'Checkmark' },
                                    { value: 'none', label: 'None' },
                                ]
                            }},
                            { field: 'iconColor', label: 'Icon Color', type: 'color' },
                        ]
                    }
                ];

            case 'video':
                return [
                    {
                        title: 'Video Settings',
                        icon: 'mdi:video',
                        iconColor: 'text-primary/100',
                        defaultExpanded: true,
                        fields: [
                            { field: 'videoUrl', label: 'Video URL', type: 'video-url' },
                            { field: 'thumbnailUrl', label: 'Custom Thumbnail', type: 'thumbnail-upload' },
                            { field: 'alt', label: 'Alt Text', type: 'text' },
                            { field: 'width', label: 'Width', type: 'select', options: {
                                choices: [
                                    { value: '100%', label: 'Full Width' },
                                    { value: '75%', label: '75%' },
                                    { value: '50%', label: '50%' },
                                ]
                            }},
                            { field: 'playButtonColor', label: 'Play Button Color', type: 'color' },
                        ]
                    }
                ];

            case 'footer':
                return [
                    {
                        title: 'Company Info',
                        icon: 'mdi:domain',
                        iconColor: 'text-primary/100',
                        defaultExpanded: true,
                        fields: [
                            { field: 'companyName', label: 'Company Name', type: 'text' },
                            { field: 'address', label: 'Address', type: 'text' },
                            { field: 'phone', label: 'Phone', type: 'text' },
                            { field: 'email', label: 'Email', type: 'text' },
                        ]
                    },
                    {
                        title: 'Links & Copyright',
                        icon: 'mdi:link-variant',
                        iconColor: 'text-primary/100',
                        defaultExpanded: false,
                        fields: [
                            { field: 'unsubscribeText', label: 'Unsubscribe Text', type: 'text' },
                            { field: 'unsubscribeUrl', label: 'Unsubscribe URL', type: 'text' },
                            { field: 'copyright', label: 'Copyright Text', type: 'text' },
                        ]
                    },
                    {
                        title: 'Appearance',
                        icon: 'mdi:palette',
                        iconColor: 'text-primary/100',
                        defaultExpanded: false,
                        fields: [
                            { field: 'textColor', label: 'Text Color', type: 'color' },
                            { field: 'linkColor', label: 'Link Color', type: 'color' },
                        ]
                    }
                ];

            case 'countdown':
                return [
                    {
                        title: 'Countdown Settings',
                        icon: 'mdi:timer-outline',
                        iconColor: 'text-primary/100',
                        defaultExpanded: true,
                        fields: [
                            { field: 'targetDate', label: 'Target Date', type: 'date' },
                            { field: 'targetTime', label: 'Target Time', type: 'time' },
                            { field: 'title', label: 'Title', type: 'text' },
                            { field: 'expiredMessage', label: 'Expired Message', type: 'text' },
                        ]
                    },
                    {
                        title: 'Colors',
                        icon: 'mdi:palette',
                        iconColor: 'text-primary/100',
                        defaultExpanded: false,
                        fields: [
                            { field: 'backgroundColor', label: 'Background Color', type: 'color' },
                            { field: 'textColor', label: 'Text Color', type: 'color' },
                            { field: 'numberColor', label: 'Number Color', type: 'color' },
                        ]
                    }
                ];

            case 'table':
                return [
                    {
                        title: 'Table Data',
                        icon: 'mdi:table',
                        iconColor: 'text-primary/100',
                        defaultExpanded: true,
                        fields: [
                            { field: 'headers', label: 'Headers', type: 'table-headers' },
                            { field: 'rows', label: 'Rows', type: 'table-rows' },
                            { field: 'showHeader', label: 'Show Header', type: 'checkbox' },
                        ]
                    },
                    {
                        title: 'Appearance',
                        icon: 'mdi:palette',
                        iconColor: 'text-primary/100',
                        defaultExpanded: false,
                        fields: [
                            { field: 'headerBgColor', label: 'Header Background', type: 'color' },
                            { field: 'headerTextColor', label: 'Header Text Color', type: 'color' },
                            { field: 'borderColor', label: 'Border Color', type: 'color' },
                            { field: 'cellPadding', label: 'Cell Padding', type: 'select', options: {
                                choices: [
                                    { value: '8px', label: 'Small' },
                                    { value: '12px', label: 'Medium' },
                                    { value: '16px', label: 'Large' },
                                ]
                            }},
                        ]
                    }
                ];

            case 'social':
                return [
                    {
                        title: 'Social Links',
                        icon: 'mdi:share-variant',
                        iconColor: 'text-primary/100',
                        defaultExpanded: true,
                        fields: [
                            { field: 'links', label: 'Social Links', type: 'social-links' },
                        ]
                    },
                    {
                        title: 'Appearance',
                        icon: 'mdi:palette',
                        iconColor: 'text-primary/100',
                        defaultExpanded: false,
                        fields: [
                            { field: 'iconSize', label: 'Icon Size', type: 'select', options: {
                                choices: [
                                    { value: '24px', label: 'Small' },
                                    { value: '32px', label: 'Medium' },
                                    { value: '40px', label: 'Large' },
                                    { value: '48px', label: 'Extra Large' },
                                ]
                            }},
                            { field: 'gap', label: 'Icon Gap', type: 'select', options: {
                                choices: [
                                    { value: '8px', label: 'Small' },
                                    { value: '12px', label: 'Medium' },
                                    { value: '16px', label: 'Large' },
                                    { value: '24px', label: 'Extra Large' },
                                ]
                            }},
                        ]
                    }
                ];

            case 'code':
                return [
                    {
                        title: 'Code Settings',
                        icon: 'mdi:code-braces',
                        iconColor: 'text-primary/100',
                        defaultExpanded: true,
                        fields: [
                            { field: 'borderRadius', label: 'Border Radius', type: 'text' },
                        ]
                    },
                    {
                        title: 'Colors',
                        icon: 'mdi:palette',
                        iconColor: 'text-primary/100',
                        defaultExpanded: false,
                        fields: [
                            { field: 'backgroundColor', label: 'Background', type: 'color' },
                            { field: 'textColor', label: 'Text Color', type: 'color' },
                        ]
                    }
                ];

            case 'preformatted':
                return [
                    {
                        title: 'Preformatted Settings',
                        icon: 'mdi:format-text-wrapping-wrap',
                        iconColor: 'text-primary/100',
                        defaultExpanded: true,
                        fields: [
                            { field: 'borderRadius', label: 'Border Radius', type: 'text' },
                        ]
                    },
                    {
                        title: 'Colors',
                        icon: 'mdi:palette',
                        iconColor: 'text-primary/100',
                        defaultExpanded: false,
                        fields: [
                            { field: 'backgroundColor', label: 'Background', type: 'color' },
                            { field: 'textColor', label: 'Text Color', type: 'color' },
                            { field: 'borderColor', label: 'Border Color', type: 'color' },
                        ]
                    }
                ];

            case 'accordion':
                return [
                    {
                        title: 'Behavior',
                        icon: 'mdi:cog',
                        iconColor: 'text-primary/100',
                        defaultExpanded: true,
                        fields: [
                            { field: 'independentToggle', label: 'Independent Toggle', type: 'checkbox', help: 'Allow multiple items open at once' },
                            { field: 'transitionDuration', label: 'Transition (ms)', type: 'number' },
                            { field: 'iconPosition', label: 'Icon Position', type: 'select', options: {
                                choices: [
                                    { value: 'right', label: 'Right' },
                                    { value: 'left', label: 'Left' },
                                ]
                            }},
                        ]
                    },
                    {
                        title: 'Header Style',
                        icon: 'mdi:format-header-1',
                        iconColor: 'text-primary/100',
                        defaultExpanded: false,
                        fields: [
                            { field: 'headerBgColor', label: 'Background', type: 'color' },
                            { field: 'headerBgColorActive', label: 'Active Background', type: 'color' },
                            { field: 'titleColor', label: 'Title Color', type: 'color' },
                            { field: 'titleFontSize', label: 'Title Size', type: 'select', options: {
                                choices: [
                                    { value: '14px', label: 'Small' },
                                    { value: '16px', label: 'Medium' },
                                    { value: '18px', label: 'Large' },
                                    { value: '20px', label: 'Extra Large' },
                                ]
                            }},
                            { field: 'titleFontWeight', label: 'Title Weight', type: 'select', options: {
                                choices: [
                                    { value: '400', label: 'Normal' },
                                    { value: '500', label: 'Medium' },
                                    { value: '600', label: 'Semi Bold' },
                                    { value: '700', label: 'Bold' },
                                ]
                            }},
                        ]
                    },
                    {
                        title: 'Content Style',
                        icon: 'mdi:text',
                        iconColor: 'text-primary/100',
                        defaultExpanded: false,
                        fields: [
                            { field: 'contentBgColor', label: 'Background', type: 'color' },
                            { field: 'contentColor', label: 'Text Color', type: 'color' },
                            { field: 'contentFontSize', label: 'Font Size', type: 'select', options: {
                                choices: [
                                    { value: '12px', label: 'Small' },
                                    { value: '14px', label: 'Medium' },
                                    { value: '16px', label: 'Large' },
                                ]
                            }},
                        ]
                    },
                    {
                        title: 'Border & Icon',
                        icon: 'mdi:border-all',
                        iconColor: 'text-primary/100',
                        defaultExpanded: false,
                        fields: [
                            { field: 'borderColor', label: 'Border Color', type: 'color' },
                            { field: 'borderRadius', label: 'Border Radius', type: 'text' },
                            { field: 'iconColor', label: 'Icon Color', type: 'color' },
                        ]
                    }
                ];

            default:
                return [];
        }
    };

    const sections = getSectionsForBlockType(selectedBlock.type);

    // Handle layout styles update
    const handleLayoutStylesUpdate = (newLayoutStyles) => {
        onUpdate(selectedBlock.id, { ...props, layoutStyles: newLayoutStyles });
    };

    // Check for custom property editor from registry
    const registryBlockDef = blockRegistry.get(selectedBlock.type);
    const CustomPropertyEditor = registryBlockDef?.propertyEditor;

    // If block provides a custom property editor, use it
    if (CustomPropertyEditor) {
        return (
            <div className="h-full overflow-y-auto px-1">
                <div className="mb-2 pb-3 border-b border-gray-200">
                    <span className="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        {registryBlockDef?.label || blockConfig?.label || selectedBlock.type}
                    </span>
                </div>

                <CustomPropertyEditor
                    props={props}
                    onUpdate={(newProps) => onUpdate(selectedBlock.id, newProps)}
                    onImageUpload={onImageUpload}
                    onVideoUpload={onVideoUpload}
                    renderField={renderField}
                />

                {/* Layout Styles Section - Available for all blocks */}
                <LayoutStylesSection
                    layoutStyles={props.layoutStyles || {}}
                    onUpdate={handleLayoutStylesUpdate}
                    onImageUpload={onImageUpload}
                    defaultCollapsed={true}
                />
            </div>
        );
    }

    return (
        <div className="h-full overflow-y-auto px-1">
            <div className="mb-2 pb-3 border-b border-gray-200">
                <span className="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    {registryBlockDef?.label || blockConfig?.label || selectedBlock.type}
                </span>
            </div>

            {/* Render sections */}
            {sections.map((section, index) => (
                <CollapsibleSection
                    key={section.title}
                    title={section.title}
                    icon={section.icon}
                    iconColor={section.iconColor}
                    defaultExpanded={section.defaultExpanded}
                    className={index === 0 ? 'mt-0 pt-0 border-t-0' : ''}
                >
                    {section.fields.map(({ field, label, type, options }) =>
                        renderField(field, label, type, options)
                    )}
                </CollapsibleSection>
            ))}

            {sections.length === 0 && (
                <p className="text-gray-500 text-sm">Double-click the block to edit it directly.</p>
            )}

            {/* Layout Styles Section - Available for all blocks */}
            <LayoutStylesSection
                layoutStyles={props.layoutStyles || {}}
                onUpdate={handleLayoutStylesUpdate}
                onImageUpload={onImageUpload}
                defaultCollapsed={true}
            />
        </div>
    );
};

export default PropertiesPanel;
