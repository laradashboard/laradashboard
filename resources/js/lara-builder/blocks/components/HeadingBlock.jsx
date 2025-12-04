import { useRef, useEffect, useCallback } from 'react';
import { layoutStylesToCSS } from '../../components/layout-styles/styleHelpers';

const HeadingBlock = ({ props, onUpdate, isSelected, onRegisterTextFormat }) => {
    const editorRef = useRef(null);
    const lastPropsText = useRef(props.text);
    const propsRef = useRef(props);
    const onUpdateRef = useRef(onUpdate);

    // Keep refs updated
    propsRef.current = props;
    onUpdateRef.current = onUpdate;

    const handleInput = useCallback(() => {
        if (editorRef.current) {
            const newText = editorRef.current.innerHTML;
            lastPropsText.current = newText;
            onUpdateRef.current({ ...propsRef.current, text: newText });
        }
    }, []);

    // Stable align change handler that uses refs
    const handleAlignChange = useCallback((newAlign) => {
        onUpdateRef.current({ ...propsRef.current, align: newAlign });
    }, []);

    // Set initial content only once when becoming selected
    useEffect(() => {
        if (isSelected && editorRef.current) {
            // Only set innerHTML if it's empty or different from what we expect
            if (editorRef.current.innerHTML === '' || editorRef.current.innerHTML === '<br>') {
                editorRef.current.innerHTML = props.text || '';
                lastPropsText.current = props.text;
            }
        }
    }, [isSelected]);

    // Handle external prop changes (e.g., from formatting toolbar)
    useEffect(() => {
        if (isSelected && editorRef.current) {
            // Only update if props changed externally (not from our own input)
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
                align: propsRef.current.align || 'left',
                onAlignChange: handleAlignChange,
            });
        } else if (!isSelected && onRegisterTextFormat) {
            onRegisterTextFormat(null);
        }
    }, [isSelected, onRegisterTextFormat, handleAlignChange]);

    // Focus the editor when selected
    useEffect(() => {
        if (isSelected && editorRef.current) {
            editorRef.current.focus();
            // Place cursor at the end
            const range = document.createRange();
            range.selectNodeContents(editorRef.current);
            range.collapse(false);
            const selection = window.getSelection();
            selection.removeAllRanges();
            selection.addRange(range);
        }
    }, [isSelected]);

    // Get layout styles (typography, background, spacing, etc.)
    const layoutStyles = layoutStylesToCSS(props.layoutStyles || {});

    const baseStyle = {
        textAlign: layoutStyles.textAlign || props.align || 'left',
        color: layoutStyles.color || props.color || '#333333',
        fontSize: layoutStyles.fontSize || props.fontSize || '32px',
        fontWeight: layoutStyles.fontWeight || props.fontWeight || 'bold',
        fontFamily: layoutStyles.fontFamily,
        fontStyle: layoutStyles.fontStyle,
        letterSpacing: layoutStyles.letterSpacing,
        textTransform: layoutStyles.textTransform,
        textDecoration: layoutStyles.textDecoration,
        lineHeight: layoutStyles.lineHeight || '1.3',
        backgroundColor: layoutStyles.backgroundColor,
        backgroundImage: layoutStyles.backgroundImage,
        backgroundSize: layoutStyles.backgroundSize,
        backgroundPosition: layoutStyles.backgroundPosition,
        backgroundRepeat: layoutStyles.backgroundRepeat,
        margin: 0,
        padding: '8px',
        borderRadius: '4px',
        // Apply margin/padding from layout styles
        ...( layoutStyles.marginTop && { marginTop: layoutStyles.marginTop }),
        ...( layoutStyles.marginRight && { marginRight: layoutStyles.marginRight }),
        ...( layoutStyles.marginBottom && { marginBottom: layoutStyles.marginBottom }),
        ...( layoutStyles.marginLeft && { marginLeft: layoutStyles.marginLeft }),
        ...( layoutStyles.paddingTop && { paddingTop: layoutStyles.paddingTop }),
        ...( layoutStyles.paddingRight && { paddingRight: layoutStyles.paddingRight }),
        ...( layoutStyles.paddingBottom && { paddingBottom: layoutStyles.paddingBottom }),
        ...( layoutStyles.paddingLeft && { paddingLeft: layoutStyles.paddingLeft }),
    };

    if (isSelected) {
        return (
            <div data-text-editing="true" className="relative">
                <div
                    ref={editorRef}
                    contentEditable
                    suppressContentEditableWarning
                    onInput={handleInput}
                    onBlur={handleInput}
                    data-placeholder="Enter heading text..."
                    style={{
                        ...baseStyle,
                        width: '100%',
                        border: '2px solid #635bff',
                        outline: 'none',
                        background: 'white',
                        minHeight: '1.5em',
                    }}
                />
            </div>
        );
    }

    const Tag = props.level || 'h1';

    // Render HTML content safely for display
    const renderContent = () => {
        const text = props.text || 'Click to edit heading';
        return <span dangerouslySetInnerHTML={{ __html: text }} />;
    };

    return (
        <Tag style={baseStyle}>
            {renderContent()}
        </Tag>
    );
};

export default HeadingBlock;
