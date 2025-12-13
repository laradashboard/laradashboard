/**
 * Button Block - Canvas Component
 *
 * Renders the button block in the builder canvas.
 * Supports inline text editing when selected.
 */

import { useRef, useEffect, useCallback } from 'react';
import { applyLayoutStyles } from '../../components/layout-styles/styleHelpers';
import { useEditableContent } from '../../core/hooks/useEditableContent';

const ButtonBlock = ({ props, onUpdate, isSelected, onRegisterTextFormat }) => {
    const editorRef = useRef(null);
    const lastPropsText = useRef(props.text);
    const propsRef = useRef(props);
    const onUpdateRef = useRef(onUpdate);

    // Keep refs updated
    propsRef.current = props;
    onUpdateRef.current = onUpdate;

    // Use shared hook for content change detection
    const { handleContentChange } = useEditableContent({
        editorRef,
        contentKey: "text",
        useInnerHTML: true, // Preserve formatting (bold, italic, underline)
        propsRef,
        onUpdateRef,
        lastContentRef: lastPropsText,
    });

    const handleInput = useCallback(() => {
        handleContentChange();
    }, [handleContentChange]);

    // Stable align change handler
    const handleAlignChange = useCallback((newAlign) => {
        onUpdateRef.current({ ...propsRef.current, align: newAlign });
    }, []);

    // Set initial content when becoming selected
    useEffect(() => {
        if (isSelected && editorRef.current) {
            if (editorRef.current.innerHTML === '' || editorRef.current.innerHTML === '<br>') {
                editorRef.current.innerHTML = props.text || 'Button Text';
                lastPropsText.current = props.text;
            }
        }
    }, [isSelected]);

    // Handle external prop changes (e.g., from formatting toolbar)
    useEffect(() => {
        if (isSelected && editorRef.current) {
            if (props.text !== lastPropsText.current) {
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

                editorRef.current.innerHTML = props.text || '';
                lastPropsText.current = props.text;

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
                            newRange.setStart(textNode, cursorOffset - currentOffset);
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
    }, [props.text, isSelected]);

    // Register text format props with parent when selected
    useEffect(() => {
        if (isSelected && onRegisterTextFormat) {
            onRegisterTextFormat({
                editorRef,
                isContentEditable: true,
                align: propsRef.current.align || 'center',
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

    // Base container styles
    const defaultContainerStyle = {
        textAlign: props.align || 'center',
        padding: '10px 8px',
        borderRadius: '4px',
    };

    // Apply layout styles to container
    const containerStyle = applyLayoutStyles(defaultContainerStyle, props.layoutStyles);

    // Base button styles
    const buttonStyle = {
        display: 'inline-block',
        backgroundColor: props.backgroundColor || '#635bff',
        color: props.textColor || '#ffffff',
        padding: props.padding || '12px 24px',
        borderRadius: props.borderRadius || '6px',
        textDecoration: 'none',
        fontSize: props.fontSize || '16px',
        fontWeight: props.fontWeight || '600',
        cursor: isSelected ? 'text' : 'default',
        border: 'none',
        outline: 'none',
        minWidth: '80px',
    };

    if (isSelected) {
        return (
            <div
                data-text-editing="true"
                style={containerStyle}
            >
                <span
                    ref={editorRef}
                    contentEditable
                    suppressContentEditableWarning
                    onInput={handleInput}
                    onBlur={handleInput}
                    onKeyDown={(e) => {
                        // Prevent Enter key from adding new lines
                        if (e.key === 'Enter') {
                            e.preventDefault();
                        }
                    }}
                    style={buttonStyle}
                    role="textbox"
                    aria-label="Button text"
                />
            </div>
        );
    }

    // Render HTML content safely for display
    const renderContent = () => {
        const text = props.text || 'Button Text';
        // Check if text contains HTML tags
        if (/<[^>]+>/.test(text)) {
            return <span dangerouslySetInnerHTML={{ __html: text }} />;
        }
        return text;
    };

    return (
        <div style={containerStyle}>
            <button type="button" style={buttonStyle}>
                {renderContent()}
            </button>
        </div>
    );
};

export default ButtonBlock;
