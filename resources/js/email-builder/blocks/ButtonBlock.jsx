import React, { useState } from 'react';

const ButtonBlock = ({ props, onUpdate, isSelected }) => {
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
        padding: '10px 8px',
        outline: isSelected ? '2px solid #3b82f6' : 'none',
        borderRadius: '4px',
    };

    const buttonStyle = {
        display: 'inline-block',
        backgroundColor: props.backgroundColor || '#3b82f6',
        color: props.textColor || '#ffffff',
        padding: props.padding || '12px 24px',
        borderRadius: props.borderRadius || '6px',
        textDecoration: 'none',
        fontSize: props.fontSize || '16px',
        fontWeight: props.fontWeight || '600',
        cursor: 'pointer',
        border: 'none',
    };

    return (
        <div style={containerStyle} onDoubleClick={handleDoubleClick}>
            <button type="button" style={buttonStyle}>
                {props.text || 'Button Text'}
            </button>

            {isEditing && (
                <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50" onClick={handleClose}>
                    <div className="bg-white rounded-lg shadow-xl p-6 w-full max-w-md max-h-[90vh] overflow-y-auto" onClick={e => e.stopPropagation()}>
                        <h3 className="text-lg font-semibold mb-4">Button Settings</h3>

                        <div className="space-y-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Button Text</label>
                                <input
                                    type="text"
                                    value={props.text || ''}
                                    onChange={(e) => handleChange('text', e.target.value)}
                                    placeholder="Click Here"
                                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                                />
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Link URL</label>
                                <input
                                    type="text"
                                    value={props.link || ''}
                                    onChange={(e) => handleChange('link', e.target.value)}
                                    placeholder="https://example.com"
                                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                                />
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">Background Color</label>
                                    <input
                                        type="color"
                                        value={props.backgroundColor || '#3b82f6'}
                                        onChange={(e) => handleChange('backgroundColor', e.target.value)}
                                        className="w-full h-10 border border-gray-300 rounded-md cursor-pointer"
                                    />
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">Text Color</label>
                                    <input
                                        type="color"
                                        value={props.textColor || '#ffffff'}
                                        onChange={(e) => handleChange('textColor', e.target.value)}
                                        className="w-full h-10 border border-gray-300 rounded-md cursor-pointer"
                                    />
                                </div>
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Border Radius</label>
                                <select
                                    value={props.borderRadius || '6px'}
                                    onChange={(e) => handleChange('borderRadius', e.target.value)}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                                >
                                    <option value="0">None</option>
                                    <option value="4px">Small</option>
                                    <option value="6px">Medium</option>
                                    <option value="8px">Large</option>
                                    <option value="9999px">Pill</option>
                                </select>
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Size</label>
                                <select
                                    value={props.padding || '12px 24px'}
                                    onChange={(e) => handleChange('padding', e.target.value)}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                                >
                                    <option value="8px 16px">Small</option>
                                    <option value="12px 24px">Medium</option>
                                    <option value="16px 32px">Large</option>
                                </select>
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

export default ButtonBlock;
