import React, { useState } from 'react';

const ImageBlock = ({ props, onUpdate, isSelected }) => {
    const [isEditing, setIsEditing] = useState(false);

    const handleDoubleClick = () => {
        setIsEditing(true);
    };

    const handleClose = () => {
        setIsEditing(false);
    };

    const handleChange = (field, value) => {
        onUpdate({ ...props, [field]: value });
    };

    const containerStyle = {
        textAlign: props.align || 'center',
        padding: '8px',
        outline: isSelected ? '2px solid #3b82f6' : 'none',
        borderRadius: '4px',
        cursor: 'pointer',
    };

    // Determine width and height values - only use custom when width/height is set to 'custom'
    const getWidth = () => {
        if (props.width === 'custom' && props.customWidth) return props.customWidth;
        return props.width || '100%';
    };

    const getHeight = () => {
        if (props.height === 'custom' && props.customHeight) return props.customHeight;
        return props.height || 'auto';
    };

    const isCustomWidth = props.width === 'custom' && props.customWidth;
    const isCustomHeight = props.height === 'custom' && props.customHeight;

    const imageStyle = {
        maxWidth: getWidth(),
        width: isCustomWidth ? props.customWidth : undefined,
        height: getHeight(),
        display: 'inline-block',
        objectFit: isCustomWidth || isCustomHeight ? 'cover' : undefined,
    };

    return (
        <div style={containerStyle} onDoubleClick={handleDoubleClick}>
            {props.src ? (
                <img
                    src={props.src}
                    alt={props.alt || ''}
                    style={imageStyle}
                />
            ) : (
                <div className="bg-gray-200 text-gray-500 p-8 rounded text-center">
                    Double-click to add image URL
                </div>
            )}

            {isEditing && (
                <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50" onClick={handleClose}>
                    <div className="bg-white rounded-lg shadow-xl p-6 w-full max-w-md" onClick={e => e.stopPropagation()}>
                        <h3 className="text-lg font-semibold mb-4">Image Settings</h3>

                        <div className="space-y-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Image URL</label>
                                <input
                                    type="text"
                                    value={props.src || ''}
                                    onChange={(e) => handleChange('src', e.target.value)}
                                    placeholder="https://example.com/image.jpg"
                                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                                />
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Alt Text</label>
                                <input
                                    type="text"
                                    value={props.alt || ''}
                                    onChange={(e) => handleChange('alt', e.target.value)}
                                    placeholder="Image description"
                                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                                />
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Link URL (optional)</label>
                                <input
                                    type="text"
                                    value={props.link || ''}
                                    onChange={(e) => handleChange('link', e.target.value)}
                                    placeholder="https://example.com"
                                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                                />
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Width</label>
                                <select
                                    value={props.width || '100%'}
                                    onChange={(e) => {
                                        handleChange('width', e.target.value);
                                        if (e.target.value !== 'custom') {
                                            handleChange('customWidth', '');
                                        }
                                    }}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                                >
                                    <option value="100%">Full Width</option>
                                    <option value="75%">75%</option>
                                    <option value="50%">50%</option>
                                    <option value="25%">25%</option>
                                    <option value="custom">Custom</option>
                                </select>
                                {props.width === 'custom' && (
                                    <input
                                        type="text"
                                        value={props.customWidth || ''}
                                        onChange={(e) => handleChange('customWidth', e.target.value)}
                                        placeholder="e.g., 300px or 50%"
                                        className="w-full mt-2 px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                                    />
                                )}
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Height</label>
                                <select
                                    value={props.height || 'auto'}
                                    onChange={(e) => {
                                        handleChange('height', e.target.value);
                                        if (e.target.value !== 'custom') {
                                            handleChange('customHeight', '');
                                        }
                                    }}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                                >
                                    <option value="auto">Auto</option>
                                    <option value="100px">100px</option>
                                    <option value="150px">150px</option>
                                    <option value="200px">200px</option>
                                    <option value="250px">250px</option>
                                    <option value="300px">300px</option>
                                    <option value="400px">400px</option>
                                    <option value="custom">Custom</option>
                                </select>
                                {props.height === 'custom' && (
                                    <input
                                        type="text"
                                        value={props.customHeight || ''}
                                        onChange={(e) => handleChange('customHeight', e.target.value)}
                                        placeholder="e.g., 200px"
                                        className="w-full mt-2 px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                                    />
                                )}
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Alignment</label>
                                <div className="flex gap-2">
                                    {['left', 'center', 'right'].map(align => (
                                        <button
                                            key={align}
                                            type="button"
                                            onClick={() => handleChange('align', align)}
                                            className={`flex-1 px-3 py-2 border rounded-md capitalize ${
                                                props.align === align
                                                    ? 'bg-blue-500 text-white border-blue-500'
                                                    : 'bg-white border-gray-300 hover:bg-gray-50'
                                            }`}
                                        >
                                            {align}
                                        </button>
                                    ))}
                                </div>
                            </div>
                        </div>

                        <button
                            type="button"
                            onClick={handleClose}
                            className="mt-6 w-full px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600"
                        >
                            Done
                        </button>
                    </div>
                </div>
            )}
        </div>
    );
};

export default ImageBlock;
