import React, { useState } from 'react';

const DividerBlock = ({ props, onUpdate, isSelected }) => {
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
        padding: '8px',
        outline: isSelected ? '2px solid #635bff' : 'none',
        borderRadius: '4px',
        cursor: 'pointer',
    };

    const dividerStyle = {
        border: 'none',
        borderTop: `${props.thickness || '1px'} ${props.style || 'solid'} ${props.color || '#e5e7eb'}`,
        width: props.width || '100%',
        margin: props.margin || '20px 0',
    };

    return (
        <div style={containerStyle} onDoubleClick={handleDoubleClick}>
            <hr style={dividerStyle} />

            {isEditing && (
                <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50" onClick={handleClose}>
                    <div className="bg-white rounded-lg shadow-xl p-6 w-full max-w-md" onClick={e => e.stopPropagation()}>
                        <h3 className="text-lg font-semibold mb-4">Divider Settings</h3>

                        <div className="space-y-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Style</label>
                                <select
                                    value={props.style || 'solid'}
                                    onChange={(e) => handleChange('style', e.target.value)}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary"
                                >
                                    <option value="solid">Solid</option>
                                    <option value="dashed">Dashed</option>
                                    <option value="dotted">Dotted</option>
                                </select>
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Color</label>
                                <input
                                    type="color"
                                    value={props.color || '#e5e7eb'}
                                    onChange={(e) => handleChange('color', e.target.value)}
                                    className="w-full h-10 border border-gray-300 rounded-md cursor-pointer"
                                />
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Thickness</label>
                                <select
                                    value={props.thickness || '1px'}
                                    onChange={(e) => handleChange('thickness', e.target.value)}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary"
                                >
                                    <option value="1px">1px</option>
                                    <option value="2px">2px</option>
                                    <option value="3px">3px</option>
                                    <option value="4px">4px</option>
                                </select>
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Width</label>
                                <select
                                    value={props.width || '100%'}
                                    onChange={(e) => handleChange('width', e.target.value)}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary"
                                >
                                    <option value="100%">Full Width</option>
                                    <option value="75%">75%</option>
                                    <option value="50%">50%</option>
                                    <option value="25%">25%</option>
                                </select>
                            </div>
                        </div>

                        <button
                            type="button"
                            onClick={handleClose}
                            className="mt-6 w-full px-4 py-2 bg-primary text-white rounded-md hover:bg-primary/90"
                        >
                            Done
                        </button>
                    </div>
                </div>
            )}
        </div>
    );
};

export default DividerBlock;
