import { useState, useRef, useEffect, useMemo, useCallback } from "react";
import { blockRegistry } from "../registry/BlockRegistry";
import { getBlockSupports } from "../blocks/blockLoader";
import { __ } from "@lara-builder/i18n";
import { LaraHooks } from "../hooks-system/LaraHooks";
import { BuilderHooks } from "../hooks-system/HookNames";
import AITextAssistant from "./AITextAssistant";

// Get block config from registry
const getBlock = (type) => blockRegistry.get(type);

/**
 * AlignmentDropdown - Compact dropdown for text alignment options
 */
const AlignmentDropdown = ({ align, onAlignChange, showJustify = false }) => {
    const [showDropdown, setShowDropdown] = useState(false);
    const dropdownRef = useRef(null);

    const alignOptions = [
        {
            value: "left",
            label: __("Align text left"),
            icon: "mdi:format-align-left",
        },
        {
            value: "center",
            label: __("Align text center"),
            icon: "mdi:format-align-center",
        },
        {
            value: "right",
            label: __("Align text right"),
            icon: "mdi:format-align-right",
        },
        ...(showJustify
            ? [
                  {
                      value: "justify",
                      label: __("Justify text"),
                      icon: "mdi:format-align-justify",
                  },
              ]
            : []),
    ];

    const currentAlign =
        alignOptions.find((opt) => opt.value === align) || alignOptions[0];

    // Close dropdown when clicking outside
    useEffect(() => {
        const handleClickOutside = (event) => {
            if (
                dropdownRef.current &&
                !dropdownRef.current.contains(event.target)
            ) {
                setShowDropdown(false);
            }
        };

        document.addEventListener("mousedown", handleClickOutside);
        return () =>
            document.removeEventListener("mousedown", handleClickOutside);
    }, []);

    const handleSelect = (value) => {
        onAlignChange(value);
        setShowDropdown(false);
    };

    const buttonClass =
        "p-1.5 pb-1 rounded hover:bg-gray-100 transition-colors text-gray-600";

    return (
        <div className="relative" ref={dropdownRef}>
            <button
                type="button"
                onClick={() => setShowDropdown(!showDropdown)}
                className={`${buttonClass} flex items-center gap-0.5 ${
                    showDropdown ? "bg-gray-100" : ""
                }`}
                title={currentAlign.label}
            >
                <iconify-icon
                    icon={currentAlign.icon}
                    width="16"
                    height="16"
                ></iconify-icon>
                <iconify-icon
                    icon="mdi:chevron-down"
                    width="12"
                    height="12"
                    class="text-gray-400"
                ></iconify-icon>
            </button>

            {showDropdown && (
                <div className="absolute left-0 top-full mt-1 w-44 bg-white border border-gray-200 rounded-lg shadow-lg py-1 z-50">
                    {alignOptions.map((option) => (
                        <button
                            key={option.value}
                            type="button"
                            onClick={() => handleSelect(option.value)}
                            className={`w-full px-3 py-2 text-left text-sm hover:bg-gray-50 flex items-center gap-2 ${
                                align === option.value
                                    ? "text-primary bg-primary/5"
                                    : "text-gray-700"
                            }`}
                        >
                            <iconify-icon
                                icon={option.icon}
                                width="16"
                                height="16"
                                class={
                                    align === option.value
                                        ? "text-primary"
                                        : "text-gray-500"
                                }
                            ></iconify-icon>
                            <span>{option.label}</span>
                        </button>
                    ))}
                </div>
            )}
        </div>
    );
};

// Alignment-only controls for non-text blocks (image, button, video, etc.)
// Now uses AlignmentDropdown for consistency
const AlignOnlyControls = ({ align, onAlignChange }) => {
    return (
        <AlignmentDropdown
            align={align}
            onAlignChange={onAlignChange}
            showJustify={false}
        />
    );
};

/**
 * Default text control items for the "More" dropdown
 * Each item has: id, label, icon, tag (HTML tag to wrap), command (optional execCommand)
 */
const getDefaultMoreTextControls = () => [
    {
        id: "highlight",
        label: __("Highlight"),
        icon: "mdi:marker",
        tag: "mark",
        position: 10,
    },
    {
        id: "inlineCode",
        label: __("Inline code"),
        icon: "mdi:code-tags",
        tag: "code",
        position: 20,
    },
    {
        id: "keyboard",
        label: __("Keyboard input"),
        icon: "mdi:keyboard",
        tag: "kbd",
        position: 30,
    },
    {
        id: "strikethrough",
        label: __("Strikethrough"),
        icon: "mdi:format-strikethrough",
        command: "strikeThrough",
        position: 40,
    },
    {
        id: "subscript",
        label: __("Subscript"),
        icon: "mdi:format-subscript",
        command: "subscript",
        position: 50,
    },
    {
        id: "superscript",
        label: __("Superscript"),
        icon: "mdi:format-superscript",
        command: "superscript",
        position: 60,
    },
];

/**
 * MoreTextControls - Extensible dropdown for additional text formatting options
 *
 * Extensibility via hooks:
 * - Use FILTER_MORE_TEXT_CONTROLS to add/remove/modify items
 *
 * @example Adding a custom control:
 * LaraHooks.addFilter(BuilderHooks.FILTER_MORE_TEXT_CONTROLS, (items) => {
 *   return [
 *     ...items,
 *     {
 *       id: 'myCustomFormat',
 *       label: 'My Format',
 *       icon: 'mdi:star',
 *       tag: 'span',
 *       className: 'my-custom-class', // Optional: add class to the tag
 *       style: { color: 'red' }, // Optional: inline styles
 *       position: 25, // Controls order (lower = earlier)
 *       onClick: (execFormat, wrapWithTag) => { // Optional: custom handler
 *         wrapWithTag('span', { className: 'custom' });
 *       }
 *     }
 *   ];
 * });
 *
 * @example Removing a control:
 * LaraHooks.addFilter(BuilderHooks.FILTER_MORE_TEXT_CONTROLS, (items) => {
 *   return items.filter(item => item.id !== 'strikethrough');
 * });
 */
const MoreTextControls = ({ editorRef, saveSelection, restoreSelection }) => {
    const [showDropdown, setShowDropdown] = useState(false);
    const dropdownRef = useRef(null);

    // Get items from hook system (allows extension)
    const items = useMemo(() => {
        const defaultItems = getDefaultMoreTextControls();
        const filteredItems = LaraHooks.applyFilters(
            BuilderHooks.FILTER_MORE_TEXT_CONTROLS,
            defaultItems
        );
        // Sort by position
        return [...filteredItems].sort(
            (a, b) => (a.position || 100) - (b.position || 100)
        );
    }, []);

    // Close dropdown when clicking outside
    useEffect(() => {
        const handleClickOutside = (event) => {
            if (
                dropdownRef.current &&
                !dropdownRef.current.contains(event.target)
            ) {
                setShowDropdown(false);
            }
        };

        document.addEventListener("mousedown", handleClickOutside);
        return () =>
            document.removeEventListener("mousedown", handleClickOutside);
    }, []);

    // Execute formatting via execCommand
    const execFormat = (command, value = null) => {
        restoreSelection();
        document.execCommand(command, false, value);
        if (editorRef?.current) {
            editorRef.current.focus();
        }
    };

    // Wrap selected text with HTML tag
    const wrapWithTag = (tag, options = {}) => {
        restoreSelection();
        const selection = window.getSelection();
        if (!selection || selection.rangeCount === 0) return;

        const range = selection.getRangeAt(0);
        if (range.collapsed) return; // No text selected

        const selectedText = range.toString();
        if (!selectedText) return;

        // Create the wrapper element
        const wrapper = document.createElement(tag);

        // Apply optional className
        if (options.className) {
            wrapper.className = options.className;
        }

        // Apply optional inline styles
        if (options.style) {
            Object.assign(wrapper.style, options.style);
        }

        // Apply optional attributes
        if (options.attributes) {
            Object.entries(options.attributes).forEach(([key, value]) => {
                wrapper.setAttribute(key, value);
            });
        }

        // Wrap the selection
        try {
            range.surroundContents(wrapper);
        } catch (e) {
            // If surroundContents fails (e.g., partial selection across elements),
            // fall back to extracting and inserting
            const fragment = range.extractContents();
            wrapper.appendChild(fragment);
            range.insertNode(wrapper);
        }

        // Move cursor to after the wrapped element so user can continue typing normally
        const newRange = document.createRange();
        newRange.setStartAfter(wrapper);
        newRange.setEndAfter(wrapper);
        selection.removeAllRanges();
        selection.addRange(newRange);

        if (editorRef?.current) {
            editorRef.current.focus();
        }
    };

    // Handle item click
    const handleItemClick = (item) => {
        // Custom onClick handler takes precedence
        if (item.onClick) {
            item.onClick(execFormat, wrapWithTag);
        } else if (item.command) {
            // Use execCommand
            execFormat(item.command, item.commandValue || null);
        } else if (item.tag) {
            // Wrap with HTML tag
            wrapWithTag(item.tag, {
                className: item.className,
                style: item.style,
                attributes: item.attributes,
            });
        }
        setShowDropdown(false);
    };

    const buttonClass =
        "p-1.5 pb-0 rounded hover:bg-gray-100 transition-colors text-gray-600";

    return (
        <div className="relative" ref={dropdownRef}>
            <button
                type="button"
                onMouseDown={saveSelection}
                onClick={() => setShowDropdown(!showDropdown)}
                className={`${buttonClass} ${
                    showDropdown ? "bg-gray-100" : ""
                }`}
                title={__("More text controls")}
            >
                <iconify-icon
                    icon="mdi:chevron-down"
                    width="16"
                    height="16"
                ></iconify-icon>
            </button>

            {showDropdown && (
                <div className="absolute left-0 top-full mt-1 w-48 bg-white border border-gray-200 rounded-lg shadow-lg py-1 z-50">
                    {items.map((item, index) => (
                        <div key={item.id || index}>
                            {/* Separator support */}
                            {item.type === "separator" ? (
                                <div className="border-t border-gray-100 my-1"></div>
                            ) : (
                                <button
                                    type="button"
                                    onClick={() => handleItemClick(item)}
                                    className="w-full px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2"
                                    disabled={item.disabled}
                                >
                                    <iconify-icon
                                        icon={item.icon || "mdi:format-text"}
                                        width="16"
                                        height="16"
                                        class="text-gray-500"
                                    ></iconify-icon>
                                    <span>{item.label}</span>
                                </button>
                            )}
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
};

// Text formatting controls component - uses execCommand for WYSIWYG contentEditable
const TextFormatControls = ({
    editorRef,
    align,
    onAlignChange,
    supports = {},
    onContentChange,
}) => {
    // Feature flags from block supports
    const showBold = supports.bold !== false;
    const showItalic = supports.italic !== false;
    const showUnderline = supports.underline !== false;
    const showLink = supports.link === true;
    const showJustify = supports.justify === true;
    const showClearFormat = supports.clearFormat !== false;
    const showMoreControls = supports.moreControls !== false;
    const showAIAssistant = supports.aiAssistant !== false;
    const [showLinkInput, setShowLinkInput] = useState(false);
    const [linkUrl, setLinkUrl] = useState("");
    const linkInputRef = useRef(null);
    const savedSelection = useRef(null);

    // AI Assistant state
    const [showAI, setShowAI] = useState(false);
    const [selectedText, setSelectedText] = useState("");
    const [aiPosition, setAiPosition] = useState({ top: 0, left: 0 });
    const aiButtonRef = useRef(null);

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

    const handleBold = () => execFormat("bold");
    const handleItalic = () => execFormat("italic");
    const handleUnderline = () => execFormat("underline");

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
        document.execCommand("createLink", false, linkUrl);
        setLinkUrl("");
        setShowLinkInput(false);

        if (editorRef?.current) {
            editorRef.current.focus();
        }
    };

    const handleLinkKeyDown = (e) => {
        if (e.key === "Enter") {
            e.preventDefault();
            handleLinkSubmit();
        } else if (e.key === "Escape") {
            setShowLinkInput(false);
            setLinkUrl("");
        }
    };

    const handleClearFormat = () => {
        restoreSelection();
        document.execCommand("removeFormat", false, null);
        // Also remove links
        document.execCommand("unlink", false, null);
        if (editorRef?.current) {
            editorRef.current.focus();
        }
    };

    // AI Assistant handlers
    const handleAIClick = (e) => {
        e.stopPropagation();
        e.preventDefault();

        const selection = window.getSelection();
        const text = selection?.toString()?.trim() || "";

        if (!text) {
            // If no selection, use entire content
            const content = editorRef?.current?.innerText || "";
            setSelectedText(content);
        } else {
            saveSelection();
            setSelectedText(text);
        }

        // Position the AI popover near the button
        if (aiButtonRef.current) {
            const rect = aiButtonRef.current.getBoundingClientRect();
            const viewportWidth = window.innerWidth;

            // Calculate left position - ensure it stays within viewport
            let left = rect.left - 140; // Center the 320px popover roughly under the button
            if (left < 10) left = 10;
            if (left + 320 > viewportWidth - 10) left = viewportWidth - 330;

            setAiPosition({
                top: rect.bottom + 8,
                left: left,
            });
        }

        setShowAI(true);
    };

    const handleAIApply = useCallback(
        (newText) => {
            // Check if we have a saved selection (partial text selected)
            if (savedSelection.current && !savedSelection.current.collapsed) {
                restoreSelection();
                document.execCommand("insertText", false, newText);
            } else {
                // Replace entire content
                if (editorRef?.current) {
                    editorRef.current.innerText = newText;
                }
            }

            // Trigger content change callback if provided
            if (onContentChange && editorRef?.current) {
                onContentChange(editorRef.current.innerHTML);
            }

            if (editorRef?.current) {
                editorRef.current.focus();
            }
        },
        [editorRef, onContentChange, restoreSelection]
    );

    const buttonClass =
        "p-1.5 pb-0 rounded hover:bg-gray-100 transition-colors text-gray-600";

    // Check if any text formatting buttons are shown
    const hasTextFormatting = showBold || showItalic || showUnderline;

    return (
        <>
            {/* Text Formatting Buttons */}
            {showBold && (
                <button
                    type="button"
                    onMouseDown={saveSelection}
                    onClick={handleBold}
                    className={buttonClass}
                    title={__("Bold")}
                >
                    <iconify-icon
                        icon="mdi:format-bold"
                        width="16"
                        height="16"
                    ></iconify-icon>
                </button>
            )}
            {showItalic && (
                <button
                    type="button"
                    onMouseDown={saveSelection}
                    onClick={handleItalic}
                    className={buttonClass}
                    title={__("Italic")}
                >
                    <iconify-icon
                        icon="mdi:format-italic"
                        width="16"
                        height="16"
                    ></iconify-icon>
                </button>
            )}
            {showUnderline && (
                <button
                    type="button"
                    onMouseDown={saveSelection}
                    onClick={handleUnderline}
                    className={buttonClass}
                    title={__("Underline")}
                >
                    <iconify-icon
                        icon="mdi:format-underline"
                        width="16"
                        height="16"
                    ></iconify-icon>
                </button>
            )}

            {hasTextFormatting && (
                <div className="w-px h-5 bg-gray-200 mx-0.5"></div>
            )}

            {/* Alignment Dropdown */}
            <AlignmentDropdown
                align={align}
                onAlignChange={onAlignChange}
                showJustify={showJustify}
            />

            {/* Link Button */}
            {showLink && (
                <>
                    <div className="w-px h-5 bg-gray-200 mx-0.5"></div>
                    <div className="relative">
                        <button
                            type="button"
                            onClick={handleLinkClick}
                            className={buttonClass}
                            title={__("Insert Link")}
                        >
                            <iconify-icon
                                icon="mdi:link-variant"
                                width="16"
                                height="16"
                            ></iconify-icon>
                        </button>
                        {showLinkInput && (
                            <div className="absolute left-0 top-full mt-2 z-50">
                                <div className="flex items-center gap-2 px-3 py-2 bg-white rounded-lg shadow-lg border border-gray-200">
                                    <input
                                        ref={linkInputRef}
                                        type="url"
                                        value={linkUrl}
                                        onChange={(e) =>
                                            setLinkUrl(e.target.value)
                                        }
                                        onKeyDown={handleLinkKeyDown}
                                        placeholder={__("Enter URL...")}
                                        className="w-48 px-2 py-1 text-sm bg-gray-50 border border-gray-200 rounded text-gray-700 placeholder-gray-400 focus:outline-none focus:border-primary"
                                    />
                                    <button
                                        type="button"
                                        onClick={handleLinkSubmit}
                                        className="p-1 rounded bg-primary text-white hover:bg-primary/80"
                                        title={__("Apply")}
                                    >
                                        <iconify-icon
                                            icon="mdi:check"
                                            width="14"
                                            height="14"
                                        ></iconify-icon>
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => {
                                            setShowLinkInput(false);
                                            setLinkUrl("");
                                        }}
                                        className="p-1 rounded text-gray-400 hover:text-gray-600"
                                        title={__("Cancel")}
                                    >
                                        <iconify-icon
                                            icon="mdi:close"
                                            width="14"
                                            height="14"
                                        ></iconify-icon>
                                    </button>
                                </div>
                            </div>
                        )}
                    </div>
                </>
            )}

            {/* Clear Formatting */}
            {showClearFormat && (
                <>
                    <div className="w-px h-5 bg-gray-200 mx-0.5"></div>
                    <button
                        type="button"
                        onMouseDown={saveSelection}
                        onClick={handleClearFormat}
                        className={buttonClass}
                        title={__("Clear Formatting")}
                    >
                        <iconify-icon
                            icon="mdi:format-clear"
                            width="16"
                            height="16"
                        ></iconify-icon>
                    </button>
                </>
            )}

            {/* AI Assistant Button */}
            {showAIAssistant && (
                <>
                    <div className="w-px h-5 bg-gray-200 mx-0.5"></div>
                    <button
                        ref={aiButtonRef}
                        type="button"
                        onClick={handleAIClick}
                        className="p-1.5 pb-0 rounded hover:bg-primary/10 transition-colors text-primary hover:opacity-80"
                        title={__("AI Assistant")}
                    >
                        <iconify-icon
                            icon="mdi:lightning-bolt"
                            width="16"
                            height="16"
                        ></iconify-icon>
                    </button>
                </>
            )}

            {/* More Text Controls Dropdown */}
            {showMoreControls && (
                <MoreTextControls
                    editorRef={editorRef}
                    saveSelection={saveSelection}
                    restoreSelection={restoreSelection}
                />
            )}

            {/* AI Text Assistant Popover */}
            {showAI && (
                <AITextAssistant
                    isOpen={showAI}
                    onClose={() => setShowAI(false)}
                    selectedText={selectedText}
                    onApply={handleAIApply}
                    position={aiPosition}
                />
            )}
        </>
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
                            ? "bg-primary text-white"
                            : "bg-gray-100 text-gray-600 hover:bg-gray-200"
                    }`}
                    title={`${num} Column${num > 1 ? "s" : ""}`}
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
    // Drag handle props for dnd-kit
    dragHandleProps,
    // Position: 'top' (default) or 'bottom'
    position = "top",
}) => {
    const [showMenu, setShowMenu] = useState(false);
    const menuRef = useRef(null);

    const blockConfig = getBlock(block.type);

    // Get supports configuration from block.json
    const supports = getBlockSupports(block.type);

    // Determine block capabilities from supports
    const hasTextFormatting =
        supports.bold || supports.italic || supports.underline;
    const hasAlignOnly = supports.align && !hasTextFormatting;
    const hasColumnCount = supports.columnCount === true;

    // Blocks with their own editor (like text-editor with TinyMCE) - check via block type for now
    const SELF_EDITING_BLOCKS = ["text-editor"];
    const hasSelfEditor = SELF_EDITING_BLOCKS.includes(block.type);

    // Close menu when clicking outside
    useEffect(() => {
        const handleClickOutside = (event) => {
            if (menuRef.current && !menuRef.current.contains(event.target)) {
                setShowMenu(false);
            }
        };

        document.addEventListener("mousedown", handleClickOutside);
        return () =>
            document.removeEventListener("mousedown", handleClickOutside);
    }, []);

    // Position classes based on toolbar position
    const positionClasses =
        position === "bottom"
            ? "absolute -bottom-10 left-1/2 -translate-x-1/2"
            : "absolute -top-10 left-1/2 -translate-x-1/2";

    return (
        <div
            className={`lb-block-toolbar ${positionClasses} flex items-center gap-0.5 bg-white border border-gray-200 rounded-lg shadow-md px-0.5 py-0.5 z-50`}
            onClick={(e) => e.stopPropagation()}
        >
            {/* Block type icon/label with drag handle and move controls */}
            <div className="flex items-center gap-1 px-1.5 py-1 border-r border-gray-200 mr-1">
                {/* Block icon */}
                <iconify-icon
                    icon={blockConfig?.icon || "mdi:square-outline"}
                    width="16"
                    height="16"
                    class="text-gray-600"
                ></iconify-icon>

                {/* Drag handle (grip icon) */}
                {dragHandleProps && (
                    <button
                        type="button"
                        className="flex justify-center items-center p-1 hover:bg-gray-100 rounded transition-colors cursor-grab active:cursor-grabbing"
                        title={__("Drag")}
                        {...dragHandleProps}
                    >
                        <iconify-icon
                            icon="mdi:drag"
                            width="20"
                            height="20"
                            class="text-gray-400"
                        ></iconify-icon>
                    </button>
                )}

                {/* Compact move up/down controls */}
                <div className="flex flex-col gap-px">
                    <button
                        type="button"
                        onClick={onMoveUp}
                        disabled={!canMoveUp}
                        className={`p-0.5 hover:bg-gray-100 rounded transition-colors leading-none ${
                            !canMoveUp
                                ? "opacity-30 cursor-not-allowed"
                                : "cursor-pointer"
                        }`}
                        title={__("Move up")}
                    >
                        <iconify-icon
                            icon="mdi:chevron-up"
                            width="12"
                            height="12"
                            class="text-gray-500 block"
                        ></iconify-icon>
                    </button>
                    <button
                        type="button"
                        onClick={onMoveDown}
                        disabled={!canMoveDown}
                        className={`p-0.5 hover:bg-gray-100 rounded transition-colors leading-none ${
                            !canMoveDown
                                ? "opacity-30 cursor-not-allowed"
                                : "cursor-pointer"
                        }`}
                        title={__("Move down")}
                    >
                        <iconify-icon
                            icon="mdi:chevron-down"
                            width="12"
                            height="12"
                            class="text-gray-500 block"
                        ></iconify-icon>
                    </button>
                </div>
            </div>

            {/* Text Format Controls - based on supports (bold, italic, underline, etc.) */}
            {hasTextFormatting && !hasSelfEditor && textFormatProps && (
                <>
                    <TextFormatControls
                        editorRef={textFormatProps.editorRef}
                        align={textFormatProps.align}
                        onAlignChange={textFormatProps.onAlignChange}
                        supports={supports}
                    />
                    <div className="w-px h-5 bg-gray-200 mx-1"></div>
                </>
            )}

            {/* Alignment-only Controls - for blocks with align but no text formatting */}
            {hasAlignOnly && alignProps && (
                <>
                    <AlignOnlyControls
                        align={alignProps.align}
                        onAlignChange={alignProps.onAlignChange}
                    />
                    <div className="w-px h-5 bg-gray-200 mx-1"></div>
                </>
            )}

            {/* Column Controls - based on supports.columnCount */}
            {hasColumnCount && columnsProps && (
                <>
                    <ColumnControls
                        columns={columnsProps.columns}
                        onColumnsChange={columnsProps.onColumnsChange}
                    />
                    <div className="w-px h-5 bg-gray-200 mx-1"></div>
                </>
            )}

            {/* Duplicate */}
            <button
                type="button"
                onClick={onDuplicate}
                className="p-1.5 rounded hover:bg-gray-100 transition-colors"
                title={__("Duplicate")}
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    className="h-4 w-4 text-gray-600"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth={2}
                        d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"
                    />
                </svg>
            </button>

            {/* More Options (Ellipsis Menu) */}
            <div className="relative" ref={menuRef}>
                <button
                    type="button"
                    onClick={() => setShowMenu(!showMenu)}
                    className={`p-1.5 rounded transition-colors ${
                        showMenu ? "bg-gray-100" : "hover:bg-gray-100"
                    }`}
                    title={__("More options")}
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        className="h-4 w-4 text-gray-600"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth={2}
                            d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"
                        />
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
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                className="h-4 w-4"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={2}
                                    d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"
                                />
                            </svg>
                            {__("Duplicate")}
                        </button>
                        <button
                            type="button"
                            onClick={() => {
                                onMoveUp();
                                setShowMenu(false);
                            }}
                            disabled={!canMoveUp}
                            className={`w-full px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2 ${
                                !canMoveUp
                                    ? "opacity-50 cursor-not-allowed"
                                    : ""
                            }`}
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                className="h-4 w-4"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={2}
                                    d="M5 15l7-7 7 7"
                                />
                            </svg>
                            {__("Move Up")}
                        </button>
                        <button
                            type="button"
                            onClick={() => {
                                onMoveDown();
                                setShowMenu(false);
                            }}
                            disabled={!canMoveDown}
                            className={`w-full px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2 ${
                                !canMoveDown
                                    ? "opacity-50 cursor-not-allowed"
                                    : ""
                            }`}
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                className="h-4 w-4"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={2}
                                    d="M19 9l-7 7-7-7"
                                />
                            </svg>
                            {__("Move Down")}
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
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                className="h-4 w-4"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={2}
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                />
                            </svg>
                            {__("Delete")}
                        </button>
                    </div>
                )}
            </div>
        </div>
    );
};

export default BlockToolbar;
