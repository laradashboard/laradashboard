import { useRef, useEffect, useCallback, useState } from "react";
import { __ } from "@lara-builder/i18n";
import { applyLayoutStyles } from "../../components/layout-styles/styleHelpers";
import SlashCommandMenu from "../../components/SlashCommandMenu";
import { useEditableContent } from "../../core/hooks/useEditableContent";
import { pendingCursors } from "../../core/pendingCursors";

export default function TextBlock({
    props,
    blockId,
    onUpdate,
    isSelected,
    onRegisterTextFormat,
    onInsertBlockAfter,
    onDelete,
    onReplaceBlock,
    onMergeWithPrevious,
    context = "post",
}) {
    const editorRef = useRef(null);
    const lastPropsContent = useRef(props.content);
    const propsRef = useRef(props);
    const onUpdateRef = useRef(onUpdate);

    // Slash command state
    const [showSlashMenu, setShowSlashMenu] = useState(false);
    const [slashQuery, setSlashQuery] = useState("");
    const [menuPosition, setMenuPosition] = useState({ top: 0, left: 0 });

    // Keep refs updated
    propsRef.current = props;
    onUpdateRef.current = onUpdate;
    const onInsertBlockAfterRef = useRef(onInsertBlockAfter);
    onInsertBlockAfterRef.current = onInsertBlockAfter;
    const onDeleteRef = useRef(onDelete);
    onDeleteRef.current = onDelete;
    const onReplaceBlockRef = useRef(onReplaceBlock);
    onReplaceBlockRef.current = onReplaceBlock;
    const onMergeWithPreviousRef = useRef(onMergeWithPrevious);
    onMergeWithPreviousRef.current = onMergeWithPrevious;

    // Detect if the cursor is at the very start of the editor (no preceding content)
    const isCursorAtStart = useCallback(() => {
        const selection = window.getSelection();
        if (!selection || !selection.isCollapsed || selection.rangeCount === 0) return false;
        const range = selection.getRangeAt(0);
        if (range.startOffset !== 0) return false;
        // Walk up from startContainer; every node must be the first child up to editorRef
        let node = range.startContainer;
        while (node && node !== editorRef.current) {
            if (node.previousSibling) return false;
            node = node.parentNode;
        }
        return node === editorRef.current;
    }, []);

    // Get plain text content from editor
    const getPlainContent = useCallback(() => {
        if (!editorRef.current) return "";
        return editorRef.current.textContent || "";
    }, []);

    // Calculate menu position
    const calculateMenuPosition = useCallback(() => {
        if (!editorRef.current) return { top: 40, left: 8 };
        const rect = editorRef.current.getBoundingClientRect();
        return {
            top: rect.height + 4,
            left: 0,
        };
    }, []);

    // Handle slash command selection
    const handleSlashSelect = useCallback((blockType) => {
        setShowSlashMenu(false);
        setSlashQuery("");

        // Replace current block with selected type
        if (onReplaceBlockRef.current) {
            onReplaceBlockRef.current(blockType);
        }
    }, []);

    // Use shared hook for content change detection
    const { handleContentChange, isEmpty: isContentEmpty } = useEditableContent({
        editorRef,
        contentKey: "content",
        useInnerHTML: true,
        propsRef,
        onUpdateRef,
        lastContentRef: lastPropsContent,
    });

    // Handle Enter key to create new block, Shift+Enter for line break
    // Handle Backspace on empty content to delete block
    const handleKeyDown = useCallback((e) => {
        // If slash menu is open, let it handle navigation keys
        if (showSlashMenu) {
            if (["ArrowDown", "ArrowUp", "Escape"].includes(e.key)) {
                return; // Let SlashCommandMenu handle these
            }
            if (e.key === "Enter") {
                e.preventDefault();
                e.stopPropagation();
                return; // SlashCommandMenu will handle selection
            }
        }

        // Shift+Enter: insert a line break within the block
        if (e.key === "Enter" && e.shiftKey) {
            e.preventDefault();
            e.stopPropagation();

            const selection = window.getSelection();
            if (selection && selection.rangeCount > 0) {
                const range = selection.getRangeAt(0);
                range.deleteContents();

                const br = document.createElement("br");
                range.insertNode(br);
                range.setStartAfter(br);
                range.collapse(true);

                // A trailing <br> at the end of a contenteditable is invisible —
                // the cursor won't move to the next line unless there is content
                // after it. Add a second <br> so the new line is actually visible.
                if (!br.nextSibling) {
                    const trailingBr = document.createElement("br");
                    br.parentNode.appendChild(trailingBr);
                }

                selection.removeAllRanges();
                selection.addRange(range);
            }

            handleContentChange();
            return;
        }

        if (e.key === "Enter" && !e.shiftKey) {
            e.preventDefault();
            e.stopPropagation();

            // If slash menu is open, don't create new block
            if (showSlashMenu) return;

            if (editorRef.current) {
                // Split content at the cursor: keep before-cursor in this block,
                // move after-cursor content into the new block.
                const selection = window.getSelection();
                let afterContent = "";

                if (selection && selection.rangeCount > 0) {
                    const range = selection.getRangeAt(0);
                    // Collapse to cursor (discard any selection)
                    range.collapse(true);
                    // Select from cursor to end of editor
                    const afterRange = document.createRange();
                    afterRange.setStart(range.startContainer, range.startOffset);
                    afterRange.setEnd(
                        editorRef.current,
                        editorRef.current.childNodes.length
                    );
                    // Extract the fragment after the cursor
                    const fragment = afterRange.extractContents();
                    const temp = document.createElement("div");
                    temp.appendChild(fragment);
                    afterContent = temp.innerHTML;
                    // Remove trailing empty <br> left by extract
                    afterContent = afterContent.replace(/^(<br\s*\/?>)+/, "");
                }

                // Save the current (now trimmed) content
                const beforeContent = editorRef.current.innerHTML;
                lastPropsContent.current = beforeContent;
                onUpdateRef.current({
                    ...propsRef.current,
                    content: beforeContent,
                });

                // Insert new block with the after-cursor content
                if (onInsertBlockAfterRef.current) {
                    onInsertBlockAfterRef.current("text", { content: afterContent });
                }
            }
        }

        // Backspace on empty content deletes the block;
        // Backspace at start of non-empty content merges with previous block
        if (e.key === "Backspace") {
            const content = editorRef.current?.innerHTML || "";
            if (isContentEmpty(content)) {
                e.preventDefault();
                e.stopPropagation();
                if (onDeleteRef.current) {
                    onDeleteRef.current();
                }
            } else if (isCursorAtStart()) {
                e.preventDefault();
                e.stopPropagation();
                if (onMergeWithPreviousRef.current) {
                    onMergeWithPreviousRef.current(editorRef.current.innerHTML);
                }
            }
        }

        // Escape closes slash menu
        if (e.key === "Escape" && showSlashMenu) {
            e.preventDefault();
            setShowSlashMenu(false);
            setSlashQuery("");
        }
    }, [showSlashMenu, isContentEmpty, handleContentChange]);

    /**
     * Sanitize pasted HTML content to remove LaraBuilder wrapper elements
     * and browser-specific paste artifacts
     * This prevents issues when users copy content from rendered frontend pages
     */
    const sanitizePastedContent = useCallback((html) => {
        // Create a temporary container to parse the HTML
        const temp = document.createElement('div');
        temp.innerHTML = html;

        // Remove meta tags (browsers add these when copying)
        const metaTags = temp.querySelectorAll('meta');
        metaTags.forEach(el => el.remove());

        // Remove Apple-interchange-newline (Safari/macOS artifact)
        const appleBreaks = temp.querySelectorAll('.Apple-interchange-newline, br.Apple-interchange-newline');
        appleBreaks.forEach(el => el.remove());

        // Remove lb-block wrapper elements but keep their content
        const lbBlocks = temp.querySelectorAll('.lb-block, [class*="lb-block"]');
        lbBlocks.forEach(el => {
            // Replace the wrapper with its inner content
            const parent = el.parentNode;
            while (el.firstChild) {
                parent.insertBefore(el.firstChild, el);
            }
            parent.removeChild(el);
        });

        // Also strip data-lara-block attributes if present
        const laraBlocks = temp.querySelectorAll('[data-lara-block]');
        laraBlocks.forEach(el => {
            el.removeAttribute('data-lara-block');
            el.removeAttribute('data-props');
            el.removeAttribute('data-block-id');
        });

        return temp.innerHTML;
    }, []);

    // Handle paste to sanitize content from rendered pages
    const handlePaste = useCallback((e) => {
        const html = e.clipboardData?.getData('text/html');

        // Sanitize if pasting HTML that contains lb-block elements or browser artifacts
        const needsSanitization = html && (
            html.includes('lb-block') ||
            html.includes('data-lara-block') ||
            html.includes('<meta') ||
            html.includes('Apple-interchange-newline')
        );

        if (needsSanitization) {
            e.preventDefault();

            const sanitized = sanitizePastedContent(html);

            // Insert sanitized content at cursor position
            const selection = window.getSelection();
            if (selection.rangeCount > 0) {
                const range = selection.getRangeAt(0);
                range.deleteContents();

                const fragment = range.createContextualFragment(sanitized);
                range.insertNode(fragment);

                // Move cursor to end of inserted content
                range.collapse(false);
                selection.removeAllRanges();
                selection.addRange(range);
            }

            // Trigger content change
            handleContentChange();
        }
        // If no problematic content, let default paste behavior happen
    }, [sanitizePastedContent, handleContentChange]);

    const handleInput = useCallback(() => {
        // Handle content change (only updates if actually changed)
        handleContentChange();

        // Check for slash command
        const plainContent = getPlainContent();
        if (plainContent.startsWith("/")) {
            const query = plainContent.slice(1);
            setSlashQuery(query);
            setMenuPosition(calculateMenuPosition());
            setShowSlashMenu(true);
        } else {
            setShowSlashMenu(false);
            setSlashQuery("");
        }
    }, [handleContentChange, getPlainContent, calculateMenuPosition]);

    // Stable align change handler that uses refs
    const handleAlignChange = useCallback((newAlign) => {
        onUpdateRef.current({ ...propsRef.current, align: newAlign });
    }, []);

    // Set initial content only once when becoming selected
    useEffect(() => {
        if (isSelected && editorRef.current) {
            // Only set innerHTML if it's empty or different from what we expect
            if (
                editorRef.current.innerHTML === "" ||
                editorRef.current.innerHTML === "<br>"
            ) {
                editorRef.current.innerHTML = props.content || "";
                lastPropsContent.current = props.content;
            }
        }
        // Reset slash menu when selection changes
        if (!isSelected) {
            setShowSlashMenu(false);
            setSlashQuery("");
        }
    }, [isSelected]);

    // Handle external prop changes (e.g., from formatting toolbar)
    useEffect(() => {
        if (isSelected && editorRef.current) {
            // Only update if props changed externally (not from our own input)
            if (props.content !== lastPropsContent.current) {
                // Save cursor position
                const selection = window.getSelection();
                let cursorOffset = 0;

                if (selection.rangeCount > 0) {
                    const range = selection.getRangeAt(0);
                    const preCaretRange = range.cloneRange();
                    preCaretRange.selectNodeContents(editorRef.current);
                    preCaretRange.setEnd(range.endContainer, range.endOffset);
                    cursorOffset = preCaretRange.toString().length;
                }

                editorRef.current.innerHTML = props.content || "";
                lastPropsContent.current = props.content;

                // Restore cursor position
                try {
                    const newRange = document.createRange();
                    const textNodes = [];
                    const walker = document.createTreeWalker(
                        editorRef.current,
                        NodeFilter.SHOW_TEXT,
                        null,
                        false
                    );
                    let node;
                    while ((node = walker.nextNode())) {
                        textNodes.push(node);
                    }

                    let currentOffset = 0;
                    for (const textNode of textNodes) {
                        const nodeLength = textNode.textContent.length;
                        if (currentOffset + nodeLength >= cursorOffset) {
                            newRange.setStart(
                                textNode,
                                cursorOffset - currentOffset
                            );
                            newRange.collapse(true);
                            selection.removeAllRanges();
                            selection.addRange(newRange);
                            break;
                        }
                        currentOffset += nodeLength;
                    }
                } catch (e) {
                    // If cursor restoration fails, just focus at the end
                    editorRef.current.focus();
                }
            }
        }
    }, [props.content, isSelected]);

    // Register text format props with parent when selected
    useEffect(() => {
        if (isSelected && onRegisterTextFormat) {
            onRegisterTextFormat({
                editorRef,
                isContentEditable: true,
                align: propsRef.current.align || "left",
                onAlignChange: handleAlignChange,
            });
        } else if (!isSelected && onRegisterTextFormat) {
            onRegisterTextFormat(null);
        }
    }, [isSelected, onRegisterTextFormat, handleAlignChange]);

    // Focus the editor when selected, placing cursor at the position requested
    // by insert/merge operations (via pendingCursors), or at the end by default.
    useEffect(() => {
        if (isSelected && editorRef.current) {
            // Use requestAnimationFrame to ensure focus happens after click event completes
            // This is necessary when inserting blocks via click from the BlockPanel
            requestAnimationFrame(() => {
                if (!editorRef.current) return;

                editorRef.current.focus();

                // Check for a pending cursor position set by an insert/merge operation
                const pendingPos = blockId !== undefined ? pendingCursors.get(blockId) : undefined;
                if (pendingPos !== undefined) {
                    pendingCursors.delete(blockId);
                }

                const selection = window.getSelection();

                if (pendingPos === "start") {
                    // New block created via Enter with split content — cursor at beginning
                    const range = document.createRange();
                    range.selectNodeContents(editorRef.current);
                    range.collapse(true);
                    selection.removeAllRanges();
                    selection.addRange(range);
                } else if (typeof pendingPos === "number") {
                    // Backspace merge — cursor at the junction between prev and appended content
                    const walker = document.createTreeWalker(
                        editorRef.current,
                        NodeFilter.SHOW_TEXT,
                        null
                    );
                    let remaining = pendingPos;
                    let placed = false;
                    let node;
                    while ((node = walker.nextNode())) {
                        const len = node.textContent.length;
                        if (remaining <= len) {
                            const range = document.createRange();
                            range.setStart(node, remaining);
                            range.collapse(true);
                            selection.removeAllRanges();
                            selection.addRange(range);
                            placed = true;
                            break;
                        }
                        remaining -= len;
                    }
                    if (!placed) {
                        // Offset beyond content length — fall back to end
                        const range = document.createRange();
                        range.selectNodeContents(editorRef.current);
                        range.collapse(false);
                        selection.removeAllRanges();
                        selection.addRange(range);
                    }
                } else {
                    // Default: cursor at the end
                    const range = document.createRange();
                    range.selectNodeContents(editorRef.current);
                    range.collapse(false);
                    selection.removeAllRanges();
                    selection.addRange(range);
                }
            });
        }
    }, [isSelected]);

    // Base styles for the text block
    const defaultStyle = {
        textAlign: props.align || "left",
        color: props.color || "#666666",
        fontSize: props.fontSize || "16px",
        lineHeight: props.lineHeight || "1.6",
        padding: "8px",
        borderRadius: "4px",
        minHeight: "40px",
    };

    // Apply layout styles (typography, background, spacing, border, shadow)
    const baseStyle = applyLayoutStyles(defaultStyle, props.layoutStyles);

    // Check if content is empty for placeholder display
    const isEmpty = isContentEmpty(props.content);

    // Check if showing slash command (content is just "/..." )
    const isSlashCommand = props.content && getPlainContent().startsWith("/");

    if (isSelected) {
        return (
            <div
                data-text-editing="true"
                data-no-selection-style="true"
                className="relative"
            >
                <div
                    ref={editorRef}
                    contentEditable
                    suppressContentEditableWarning
                    onInput={handleInput}
                    onBlur={handleInput}
                    onKeyDown={handleKeyDown}
                    onPaste={handlePaste}
                    style={{
                        ...baseStyle,
                        width: "100%",
                        outline: "none",
                    }}
                />
                {isEmpty && !isSlashCommand && (
                    <div
                        style={{
                            position: "absolute",
                            top: baseStyle.padding || "8px",
                            left: baseStyle.padding || "8px",
                            color: "#9ca3af",
                            pointerEvents: "none",
                            fontSize: baseStyle.fontSize || "16px",
                            lineHeight: baseStyle.lineHeight || "1.6",
                        }}
                    >
                        {__("Type / to choose a block")}
                    </div>
                )}
                {showSlashMenu && (
                    <SlashCommandMenu
                        isOpen={showSlashMenu}
                        searchQuery={slashQuery}
                        onSelect={handleSlashSelect}
                        onClose={() => {
                            setShowSlashMenu(false);
                            setSlashQuery("");
                        }}
                        position={menuPosition}
                        context={context}
                    />
                )}
            </div>
        );
    }

    // Render HTML content safely for display
    const renderContent = () => {
        if (!props.content) {
            return (
                <span style={{ color: "#9ca3af" }}>
                    {__("Type / to choose a block")}
                </span>
            );
        }
        return <span dangerouslySetInnerHTML={{ __html: props.content }} />;
    };

    return <div style={baseStyle}>{renderContent()}</div>;
}
