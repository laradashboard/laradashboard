import { useState, useRef, useEffect } from 'react';

/**
 * EditorOptionsMenu - Gutenberg-style options menu
 *
 * Features:
 * - Visual Editor / Code Editor toggle
 * - Copy all blocks
 * - Other editor options
 */
const EditorOptionsMenu = ({
    editorMode,
    onEditorModeChange,
    onCopyAllBlocks,
    onPasteBlocks,
}) => {
    const [isOpen, setIsOpen] = useState(false);
    const [copySuccess, setCopySuccess] = useState(false);
    const menuRef = useRef(null);

    // Close menu when clicking outside
    useEffect(() => {
        const handleClickOutside = (event) => {
            if (menuRef.current && !menuRef.current.contains(event.target)) {
                setIsOpen(false);
            }
        };

        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    const handleCopyAllBlocks = async () => {
        try {
            await onCopyAllBlocks();
            setCopySuccess(true);
            setTimeout(() => setCopySuccess(false), 2000);
        } catch (error) {
            console.error('Failed to copy blocks:', error);
        }
    };

    const handlePaste = async () => {
        try {
            const text = await navigator.clipboard.readText();
            if (text) {
                onPasteBlocks(text);
            }
        } catch (error) {
            console.error('Failed to paste:', error);
        }
        setIsOpen(false);
    };

    return (
        <div className="relative" ref={menuRef}>
            {/* Menu trigger button */}
            <button
                onClick={() => setIsOpen(!isOpen)}
                className={`p-1.5 pb-0 rounded-md transition-colors ${
                    isOpen ? 'bg-gray-100 text-gray-900' : 'hover:bg-gray-100 text-gray-600'
                }`}
                title="Options"
            >
                <iconify-icon icon="mdi:dots-vertical" width="20" height="20"></iconify-icon>
            </button>

            {/* Dropdown menu */}
            {isOpen && (
                <div className="absolute right-0 top-full mt-1 w-64 bg-white border border-gray-200 rounded-lg shadow-xl z-50 py-1 overflow-hidden">
                    {/* EDITOR Section */}
                    <div className="px-3 py-2 border-b border-gray-100">
                        <span className="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">
                            Editor
                        </span>
                    </div>

                    {/* Visual Editor option */}
                    <button
                        onClick={() => {
                            onEditorModeChange('visual');
                            setIsOpen(false);
                        }}
                        className="w-full px-3 py-2.5 text-left hover:bg-gray-50 flex items-center justify-between group"
                    >
                        <div className="flex items-center gap-3">
                            <iconify-icon
                                icon="mdi:eye-outline"
                                width="18"
                                height="18"
                                class="text-gray-500 group-hover:text-gray-700"
                            ></iconify-icon>
                            <span className="text-sm text-gray-700">Visual editor</span>
                        </div>
                        {editorMode === 'visual' && (
                            <iconify-icon
                                icon="mdi:check"
                                width="18"
                                height="18"
                                class="text-primary"
                            ></iconify-icon>
                        )}
                    </button>

                    {/* Code Editor option */}
                    <button
                        onClick={() => {
                            onEditorModeChange('code');
                            setIsOpen(false);
                        }}
                        className="w-full px-3 py-2.5 text-left hover:bg-gray-50 flex items-center justify-between group"
                    >
                        <div className="flex items-center gap-3">
                            <iconify-icon
                                icon="mdi:code-tags"
                                width="18"
                                height="18"
                                class="text-gray-500 group-hover:text-gray-700"
                            ></iconify-icon>
                            <span className="text-sm text-gray-700">Code editor</span>
                        </div>
                        {editorMode === 'code' && (
                            <iconify-icon
                                icon="mdi:check"
                                width="18"
                                height="18"
                                class="text-primary"
                            ></iconify-icon>
                        )}
                    </button>

                    {/* TOOLS Section */}
                    <div className="px-3 py-2 border-t border-b border-gray-100 mt-1">
                        <span className="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">
                            Tools
                        </span>
                    </div>

                    {/* Copy all blocks */}
                    <button
                        onClick={handleCopyAllBlocks}
                        className="w-full px-3 py-2.5 text-left hover:bg-gray-50 flex items-center gap-3 group"
                    >
                        <iconify-icon
                            icon={copySuccess ? 'mdi:check' : 'mdi:content-copy'}
                            width="18"
                            height="18"
                            class={copySuccess ? 'text-green-500' : 'text-gray-500 group-hover:text-gray-700'}
                        ></iconify-icon>
                        <span className={`text-sm ${copySuccess ? 'text-green-600' : 'text-gray-700'}`}>
                            {copySuccess ? 'Copied!' : 'Copy all blocks'}
                        </span>
                    </button>

                    {/* Paste blocks */}
                    <button
                        onClick={handlePaste}
                        className="w-full px-3 py-2.5 text-left hover:bg-gray-50 flex items-center gap-3 group"
                    >
                        <iconify-icon
                            icon="mdi:content-paste"
                            width="18"
                            height="18"
                            class="text-gray-500 group-hover:text-gray-700"
                        ></iconify-icon>
                        <span className="text-sm text-gray-700">Paste blocks</span>
                    </button>
                </div>
            )}
        </div>
    );
};

export default EditorOptionsMenu;
