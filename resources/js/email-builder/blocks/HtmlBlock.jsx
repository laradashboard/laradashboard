import React, { useState } from 'react';

const HtmlBlock = ({ props, onUpdate, isSelected }) => {
    const [isEditing, setIsEditing] = useState(false);
    const [code, setCode] = useState(props.code || '');

    const handleDoubleClick = () => {
        setCode(props.code || '');
        setIsEditing(true);
    };

    const handleClose = () => {
        setIsEditing(false);
    };

    const handleSave = () => {
        onUpdate({ ...props, code });
        setIsEditing(false);
    };

    const containerStyle = {
        padding: '8px',
        outline: isSelected ? '2px solid #3b82f6' : 'none',
        borderRadius: '4px',
        cursor: 'pointer',
    };

    return (
        <div style={containerStyle} onDoubleClick={handleDoubleClick}>
            <div dangerouslySetInnerHTML={{ __html: props.code || '<div style="padding: 20px; text-align: center; color: #999;">Custom HTML Block</div>' }} />

            {isEditing && (
                <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50" onClick={handleClose}>
                    <div className="bg-white rounded-lg shadow-xl p-6 w-full max-w-2xl max-h-[90vh] flex flex-col" onClick={e => e.stopPropagation()}>
                        <h3 className="text-lg font-semibold mb-4">Custom HTML</h3>

                        <textarea
                            value={code}
                            onChange={(e) => setCode(e.target.value)}
                            className="flex-1 min-h-[300px] w-full px-3 py-2 border border-gray-300 rounded-md font-mono text-sm focus:ring-2 focus:ring-blue-500"
                            placeholder="<div>Your custom HTML here</div>"
                        />

                        <div className="flex gap-3 mt-4">
                            <button
                                type="button"
                                onClick={handleClose}
                                className="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                onClick={handleSave}
                                className="flex-1 px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600"
                            >
                                Save
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
};

export default HtmlBlock;
