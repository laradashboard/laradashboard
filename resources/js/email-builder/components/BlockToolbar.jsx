import { useState, useRef, useEffect } from 'react';
import { getBlock } from '../utils/blockRegistry';

const BlockToolbar = ({ block, onMoveUp, onMoveDown, onDelete, onDuplicate, canMoveUp, canMoveDown }) => {
    const [showMenu, setShowMenu] = useState(false);
    const menuRef = useRef(null);

    const blockConfig = getBlock(block.type);

    // Close menu when clicking outside
    useEffect(() => {
        const handleClickOutside = (event) => {
            if (menuRef.current && !menuRef.current.contains(event.target)) {
                setShowMenu(false);
            }
        };

        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    return (
        <div
            className="absolute -top-10 left-1/2 -translate-x-1/2 flex items-center gap-0.5 bg-white border border-gray-200 rounded-lg shadow-lg px-1 py-1 z-50"
            onClick={(e) => e.stopPropagation()}
        >
            {/* Block type icon/label */}
            <div className="flex items-center gap-1.5 px-2 py-1 border-r border-gray-200 mr-1">
                <iconify-icon
                    icon={blockConfig?.icon || 'mdi:square-outline'}
                    width="16"
                    height="16"
                    class="text-gray-600"
                ></iconify-icon>
                <span className="text-xs font-medium text-gray-700">
                    {blockConfig?.label || block.type}
                </span>
            </div>

            {/* Move Up */}
            <button
                type="button"
                onClick={onMoveUp}
                disabled={!canMoveUp}
                className={`p-1.5 rounded hover:bg-gray-100 transition-colors ${!canMoveUp ? 'opacity-30 cursor-not-allowed' : ''}`}
                title="Move up"
            >
                <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 15l7-7 7 7" />
                </svg>
            </button>

            {/* Move Down */}
            <button
                type="button"
                onClick={onMoveDown}
                disabled={!canMoveDown}
                className={`p-1.5 rounded hover:bg-gray-100 transition-colors ${!canMoveDown ? 'opacity-30 cursor-not-allowed' : ''}`}
                title="Move down"
            >
                <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            {/* Divider */}
            <div className="w-px h-5 bg-gray-200 mx-1"></div>

            {/* Duplicate */}
            <button
                type="button"
                onClick={onDuplicate}
                className="p-1.5 rounded hover:bg-gray-100 transition-colors"
                title="Duplicate"
            >
                <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
            </button>

            {/* More Options (Ellipsis Menu) */}
            <div className="relative" ref={menuRef}>
                <button
                    type="button"
                    onClick={() => setShowMenu(!showMenu)}
                    className={`p-1.5 rounded transition-colors ${showMenu ? 'bg-gray-100' : 'hover:bg-gray-100'}`}
                    title="More options"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                    </svg>
                </button>

                {/* Dropdown Menu */}
                {showMenu && (
                    <div className="absolute right-0 top-full mt-1 w-40 bg-white border border-gray-200 rounded-lg shadow-lg py-1 z-50">
                        <button
                            type="button"
                            onClick={() => {
                                onDuplicate();
                                setShowMenu(false);
                            }}
                            className="w-full px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            Duplicate
                        </button>
                        <button
                            type="button"
                            onClick={() => {
                                onMoveUp();
                                setShowMenu(false);
                            }}
                            disabled={!canMoveUp}
                            className={`w-full px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2 ${!canMoveUp ? 'opacity-50 cursor-not-allowed' : ''}`}
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 15l7-7 7 7" />
                            </svg>
                            Move Up
                        </button>
                        <button
                            type="button"
                            onClick={() => {
                                onMoveDown();
                                setShowMenu(false);
                            }}
                            disabled={!canMoveDown}
                            className={`w-full px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2 ${!canMoveDown ? 'opacity-50 cursor-not-allowed' : ''}`}
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                            </svg>
                            Move Down
                        </button>
                        <div className="border-t border-gray-100 my-1"></div>
                        <button
                            type="button"
                            onClick={() => {
                                onDelete();
                                setShowMenu(false);
                            }}
                            className="w-full px-3 py-2 text-left text-sm text-red-600 hover:bg-red-50 flex items-center gap-2"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Delete
                        </button>
                    </div>
                )}
            </div>
        </div>
    );
};

export default BlockToolbar;
