import React, { useState } from 'react';

const SpacerBlock = ({ props, onUpdate, isSelected }) => {
    const [isEditing, setIsEditing] = useState(false);

    const handleDoubleClick = () => {
        setIsEditing(true);
    };

    const handleClose = () => {
        setIsEditing(false);
    };

    const handleChange = (value) => {
        onUpdate({ ...props, height: value });
    };

    const containerStyle = {
        outline: isSelected ? '2px solid #3b82f6' : '1px dashed #d1d5db',
        borderRadius: '4px',
        cursor: 'pointer',
        backgroundColor: isSelected ? 'rgba(59, 130, 246, 0.1)' : 'transparent',
    };

    const spacerStyle = {
        height: props.height || '40px',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        color: '#9ca3af',
        fontSize: '12px',
    };

    return (
        <div style={containerStyle} onDoubleClick={handleDoubleClick}>
            <div style={spacerStyle}>
                Spacer ({props.height || '40px'})
            </div>

            {isEditing && (
                <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50" onClick={handleClose}>
                    <div className="bg-white rounded-lg shadow-xl p-6 w-full max-w-md" onClick={e => e.stopPropagation()}>
                        <h3 className="text-lg font-semibold mb-4">Spacer Settings</h3>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Height</label>
                            <select
                                value={props.height || '40px'}
                                onChange={(e) => handleChange(e.target.value)}
                                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="10px">10px</option>
                                <option value="20px">20px</option>
                                <option value="30px">30px</option>
                                <option value="40px">40px</option>
                                <option value="50px">50px</option>
                                <option value="60px">60px</option>
                                <option value="80px">80px</option>
                                <option value="100px">100px</option>
                            </select>
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

export default SpacerBlock;
