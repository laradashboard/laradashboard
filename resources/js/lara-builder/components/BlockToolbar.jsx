import { useState, useRef, useEffect } from 'react';
import { getBlock } from '../../email-builder/utils/blockRegistry';

// Alignment-only controls for non-text blocks (image, button, video, etc.)
const AlignOnlyControls = ({ align, onAlignChange }) => {
    const buttonClass = "p-1.5 pb-0 rounded hover:bg-gray-100 transition-colors text-gray-600";
    const activeButtonClass = "p-1.5 pb-0 rounded bg-gray-100 text-gray-800";

    return (
        <>
            {/* Align Left */}
            <button type="button" onClick={() => onAlignChange('left')} className={align === 'left' ? activeButtonClass : buttonClass} title="Align Left">
                <iconify-icon icon="mdi:format-align-left" width="16" height="16"></iconify-icon>
            </button>
            {/* Align Center */}
            <button type="button" onClick={() => onAlignChange('center')} className={align === 'center' ? activeButtonClass : buttonClass} title="Align Center">
                <iconify-icon icon="mdi:format-align-center" width="16" height="16"></iconify-icon>
            </button>
            {/* Align Right */}
            <button type="button" onClick={() => onAlignChange('right')} className={align === 'right' ? activeButtonClass : buttonClass} title="Align Right">
                <iconify-icon icon="mdi:format-align-right" width="16" height="16"></iconify-icon>
            </button>
        </>
    );
};

// Text formatting controls component - uses execCommand for WYSIWYG contentEditable
const TextFormatControls = ({ editorRef, align, onAlignChange, showLink = true }) => {
    const [showLinkInput, setShowLinkInput] = useState(false);
    const [linkUrl, setLinkUrl] = useState('');
    const linkInputRef = useRef(null);
    const savedSelection = useRef(null);

    useEffect(() => {
        if (showLinkInput && linkInputRef.current) {
            linkInputRef.current.focus();
        }
    }, [showLinkInput]);

    // Save the current selection before clicking toolbar buttons
    const saveSelection = () => {
        const selection = window.getSelection();
        if (selection.rangeCount > 0) {
            savedSelection.current = selection.getRangeAt(0).cloneRange();
        }
    };

    // Restore the saved selection
    const restoreSelection = () => {
        if (savedSelection.current) {
            const selection = window.getSelection();
            selection.removeAllRanges();
            selection.addRange(savedSelection.current);
        }
    };

    // Execute formatting command
    const execFormat = (command, value = null) => {
        restoreSelection();
        document.execCommand(command, false, value);
        // Refocus the editor
        if (editorRef?.current) {
            editorRef.current.focus();
        }
    };

    const handleBold = () => execFormat('bold');
    const handleItalic = () => execFormat('italic');
    const handleUnderline = () => execFormat('underline');

    const handleLinkClick = () => {
        saveSelection();
        const selection = window.getSelection();
        if (selection.toString().length === 0) return; // No selection
        setShowLinkInput(true);
    };

    const handleLinkSubmit = () => {
        if (!linkUrl.trim()) {
            setShowLinkInput(false);
            return;
        }

        restoreSelection();
        document.execCommand('createLink', false, linkUrl);
        setLinkUrl('');
        setShowLinkInput(false);

        if (editorRef?.current) {
            editorRef.current.focus();
        }
    };

    const handleLinkKeyDown = (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            handleLinkSubmit();
        } else if (e.key === 'Escape') {
            setShowLinkInput(false);
            setLinkUrl('');
        }
    };

    const handleClearFormat = () => {
        restoreSelection();
        document.execCommand('removeFormat', false, null);
        // Also remove links
        document.execCommand('unlink', false, null);
        if (editorRef?.current) {
            editorRef.current.focus();
        }
    };

    const buttonClass = "p-1.5 pb-0 rounded hover:bg-gray-100 transition-colors text-gray-600";
    const activeButtonClass = "p-1.5 pb-0 rounded bg-gray-100 text-gray-800";

    return (
        <>
            {/* Bold */}
            <button type="button" onMouseDown={saveSelection} onClick={handleBold} className={buttonClass} title="Bold">
                <iconify-icon icon="mdi:format-bold" width="16" height="16"></iconify-icon>
            </button>
            {/* Italic */}
            <button type="button" onMouseDown={saveSelection} onClick={handleItalic} className={buttonClass} title="Italic">
                <iconify-icon icon="mdi:format-italic" width="16" height="16"></iconify-icon>
            </button>
            {/* Underline */}
            <button type="button" onMouseDown={saveSelection} onClick={handleUnderline} className={buttonClass} title="Underline">
                <iconify-icon icon="mdi:format-underline" width="16" height="16"></iconify-icon>
            </button>

            <div className="w-px h-5 bg-gray-200 mx-0.5"></div>

            {/* Align Left */}
            <button type="button" onClick={() => onAlignChange('left')} className={align === 'left' ? activeButtonClass : buttonClass} title="Align Left">
                <iconify-icon icon="mdi:format-align-left" width="16" height="16"></iconify-icon>
            </button>
            {/* Align Center */}
            <button type="button" onClick={() => onAlignChange('center')} className={align === 'center' ? activeButtonClass : buttonClass} title="Align Center">
                <iconify-icon icon="mdi:format-align-center" width="16" height="16"></iconify-icon>
            </button>
            {/* Align Right */}
            <button type="button" onClick={() => onAlignChange('right')} className={align === 'right' ? activeButtonClass : buttonClass} title="Align Right">
                <iconify-icon icon="mdi:format-align-right" width="16" height="16"></iconify-icon>
            </button>
            {/* Align Justify */}
            <button type="button" onClick={() => onAlignChange('justify')} className={align === 'justify' ? activeButtonClass : buttonClass} title="Justify">
                <iconify-icon icon="mdi:format-align-justify" width="16" height="16"></iconify-icon>
            </button>

            {showLink && (
                <>
                    <div className="w-px h-5 bg-gray-200 mx-0.5"></div>
                    <div className="relative">
                        <button type="button" onClick={handleLinkClick} className={buttonClass} title="Insert Link">
                            <iconify-icon icon="mdi:link-variant" width="16" height="16"></iconify-icon>
                        </button>
                        {showLinkInput && (
                            <div className="absolute left-0 top-full mt-2 z-50">
                                <div className="flex items-center gap-2 px-3 py-2 bg-white rounded-lg shadow-lg border border-gray-200">
                                    <input
                                        ref={linkInputRef}
                                        type="url"
                                        value={linkUrl}
                                        onChange={(e) => setLinkUrl(e.target.value)}
                                        onKeyDown={handleLinkKeyDown}
                                        placeholder="Enter URL..."
                                        className="w-48 px-2 py-1 text-sm bg-gray-50 border border-gray-200 rounded text-gray-700 placeholder-gray-400 focus:outline-none focus:border-primary"
                                    />
                                    <button type="button" onClick={handleLinkSubmit} className="p-1 rounded bg-primary text-white hover:bg-primary/80" title="Apply">
                                        <iconify-icon icon="mdi:check" width="14" height="14"></iconify-icon>
                                    </button>
                                    <button type="button" onClick={() => { setShowLinkInput(false); setLinkUrl(''); }} className="p-1 rounded text-gray-400 hover:text-gray-600" title="Cancel">
                                        <iconify-icon icon="mdi:close" width="14" height="14"></iconify-icon>
                                    </button>
                                </div>
                            </div>
                        )}
                    </div>
                </>
            )}

            <div className="w-px h-5 bg-gray-200 mx-0.5"></div>

            {/* Clear Formatting */}
            <button type="button" onMouseDown={saveSelection} onClick={handleClearFormat} className={buttonClass} title="Clear Formatting">
                <iconify-icon icon="mdi:format-clear" width="16" height="16"></iconify-icon>
            </button>
        </>
    );
};

// Blocks that support alignment-only toolbar (non-text blocks with alignment)
const ALIGN_ONLY_BLOCKS = ['image', 'button', 'quote', 'video', 'countdown', 'social', 'footer'];

// Heading level controls for heading block (dropdown)
const HeadingLevelControls = ({ level, onLevelChange }) => {
    return (
        <select
            value={level || 'h1'}
            onChange={(e) => onLevelChange(e.target.value)}
            className="px-2 py-1 text-xs font-semibold uppercase bg-gray-100 border-0 rounded cursor-pointer hover:bg-gray-200 focus:outline-none focus:ring-1 focus:ring-primary text-gray-700"
            title="Heading Level"
        >
            <option value="h1">H1</option>
            <option value="h2">H2</option>
            <option value="h3">H3</option>
            <option value="h4">H4</option>
            <option value="h5">H5</option>
            <option value="h6">H6</option>
        </select>
    );
};

// Column selector controls for columns block
const ColumnControls = ({ columns, onColumnsChange }) => {
    const columnOptions = [1, 2, 3, 4, 5, 6];

    return (
        <div className="flex items-center gap-1">
            {columnOptions.map((num) => (
                <button
                    key={num}
                    type="button"
                    onClick={() => onColumnsChange(num)}
                    className={`w-6 h-6 flex items-center justify-center rounded text-xs font-medium transition-colors ${
                        parseInt(columns) === num
                            ? 'bg-primary text-white'
                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                    }`}
                    title={`${num} Column${num > 1 ? 's' : ''}`}
                >
                    {num}
                </button>
            ))}
        </div>
    );
};

const BlockToolbar = ({
    block,
    onMoveUp,
    onMoveDown,
    onDelete,
    onDuplicate,
    canMoveUp,
    canMoveDown,
    // Text format props (optional - for text-based blocks)
    textFormatProps,
    // Alignment props (optional - for align-only blocks)
    alignProps,
    // Column props (optional - for columns block)
    columnsProps,
    // Heading level props (optional - for heading block)
    headingLevelProps,
    // Position: 'top' (default) or 'bottom'
    position = 'top'
}) => {
    const [showMenu, setShowMenu] = useState(false);
    const menuRef = useRef(null);

    const blockConfig = getBlock(block.type);
    // Blocks that have their own rich text editor (like TinyMCE) - don't show our text format controls
    const SELF_EDITING_BLOCKS = ['text-editor'];
    const hasSelfEditor = SELF_EDITING_BLOCKS.includes(block.type);
    // text-editor block has its own TinyMCE toolbar, so exclude it from text formatting controls
    const isTextBlock = (block.type === 'heading' || block.type === 'text' || block.type === 'list') && !hasSelfEditor;
    const isAlignOnlyBlock = ALIGN_ONLY_BLOCKS.includes(block.type);
    const isColumnsBlock = block.type === 'columns';

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

    // Position classes based on toolbar position
    const positionClasses = position === 'bottom'
        ? 'absolute -bottom-10 left-1/2 -translate-x-1/2'
        : 'absolute -top-10 left-1/2 -translate-x-1/2';

    return (
        <div
            className={`${positionClasses} flex items-center gap-0.5 bg-white border border-gray-200 rounded-lg shadow-lg px-1 py-1 z-50`}
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

            {/* Heading Level Controls - only for heading block */}
            {block.type === 'heading' && headingLevelProps && (
                <>
                    <HeadingLevelControls
                        level={headingLevelProps.level}
                        onLevelChange={headingLevelProps.onLevelChange}
                    />
                    <div className="w-px h-5 bg-gray-200 mx-1"></div>
                </>
            )}

            {/* Text Format Controls - only for text-based blocks */}
            {isTextBlock && textFormatProps && (
                <>
                    <TextFormatControls
                        editorRef={textFormatProps.editorRef}
                        align={textFormatProps.align}
                        onAlignChange={textFormatProps.onAlignChange}
                        showLink={block.type === 'text' || block.type === 'list'}
                    />
                    <div className="w-px h-5 bg-gray-200 mx-1"></div>
                </>
            )}

            {/* Alignment-only Controls - for non-text blocks with alignment */}
            {isAlignOnlyBlock && alignProps && (
                <>
                    <AlignOnlyControls
                        align={alignProps.align}
                        onAlignChange={alignProps.onAlignChange}
                    />
                    <div className="w-px h-5 bg-gray-200 mx-1"></div>
                </>
            )}

            {/* Column Controls - for columns block */}
            {isColumnsBlock && columnsProps && (
                <>
                    <ColumnControls
                        columns={columnsProps.columns}
                        onColumnsChange={columnsProps.onColumnsChange}
                    />
                    <div className="w-px h-5 bg-gray-200 mx-1"></div>
                </>
            )}

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
