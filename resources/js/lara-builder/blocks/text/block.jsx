import { useRef, useEffect, useCallback, useState } from "react";
import { __ } from "@lara-builder/i18n";
import { applyLayoutStyles } from "../../components/layout-styles/styleHelpers";
import SlashCommandMenu from "../../components/SlashCommandMenu";
import { useEditableContent } from "../../core/hooks/useEditableContent";

export default function TextBlock({
    props,
    onUpdate,
    isSelected,
    onRegisterTextFormat,
    onInsertBlockAfter,
    onDelete,
    onReplaceBlock,
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

        if (e.key === "Enter" && !e.shiftKey) {
            e.preventDefault();
            e.stopPropagation();

            // If slash menu is open, don't create new block
            if (showSlashMenu) return;

            // Save current content first
            if (editorRef.current) {
                const newContent = editorRef.current.innerHTML;
                lastPropsContent.current = newContent;
                onUpdateRef.current({
                    ...propsRef.current,
                    content: newContent,
                });
            }
            // Insert new text block after this one
            if (onInsertBlockAfterRef.current) {
                onInsertBlockAfterRef.current("text");
            }
        }

        // Backspace on empty content deletes the block
        if (e.key === "Backspace") {
            const content = editorRef.current?.innerHTML || "";
            if (isContentEmpty(content)) {
                e.preventDefault();
                e.stopPropagation();
                if (onDeleteRef.current) {
                    onDeleteRef.current();
                }
            }
        }

        // Escape closes slash menu
        if (e.key === "Escape" && showSlashMenu) {
            e.preventDefault();
            setShowSlashMenu(false);
            setSlashQuery("");
        }
        // Shift+Enter allows default behavior (line break)
    }, [showSlashMenu, isContentEmpty]);

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

    // Focus the editor when selected
    useEffect(() => {
        if (isSelected && editorRef.current) {
            // Use requestAnimationFrame to ensure focus happens after click event completes
            // This is necessary when inserting blocks via click from the BlockPanel
            requestAnimationFrame(() => {
                if (editorRef.current) {
                    editorRef.current.focus();
                    // Place cursor at the end
                    const range = document.createRange();
                    range.selectNodeContents(editorRef.current);
                    range.collapse(false);
                    const selection = window.getSelection();
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
