/**
 * Preformatted Block - Canvas Component
 *
 * Supports inline editing and text formatting toolbar integration.
 */

import { useRef, useEffect, useCallback } from 'react';
import { applyLayoutStyles } from '../../components/layout-styles/styleHelpers';
import { useEditableContent } from '../../core/hooks/useEditableContent';

const PreformattedBlock = ({ props, onUpdate, isSelected, onRegisterTextFormat }) => {
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
        useInnerHTML: true, // Preserve formatting (bold, italic, etc.)
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

    // Set initial content only once when becoming selected
    useEffect(() => {
        if (isSelected && editorRef.current) {
            if (editorRef.current.innerHTML === '' || editorRef.current.innerHTML === '<br>') {
                editorRef.current.innerHTML = props.text || '';
                lastPropsText.current = props.text;
            }
        }
    }, [isSelected]);

    // Handle external prop changes
    useEffect(() => {
        if (isSelected && editorRef.current) {
            if (props.text !== lastPropsText.current) {
                editorRef.current.innerHTML = props.text || '';
                lastPropsText.current = props.text;
            }
        }
    }, [props.text, isSelected]);

    // Register text format props with parent when selected (for toolbar integration)
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

    // Base styles for preformatted block (minimal defaults, rest from layoutStyles)
    const defaultStyle = {
        fontFamily: 'ui-monospace, SFMono-Regular, "SF Mono", Menlo, Consolas, "Liberation Mono", monospace',
        fontSize: '14px',
        lineHeight: '1.6',
        padding: '16px',
        borderRadius: '4px',
        backgroundColor: '#f5f5f5',
        color: '#333333',
        border: '1px solid #e0e0e0',
        overflowX: 'auto',
        whiteSpace: 'pre-wrap',
        wordBreak: 'break-word',
    };

    // Apply layout styles (typography, background, spacing, border, shadow)
    // Layout styles will override the defaults above
    const baseStyle = applyLayoutStyles(defaultStyle, props.layoutStyles);

    if (isSelected) {
        return (
            <div data-text-editing="true" className="relative">
                <pre
                    ref={editorRef}
                    contentEditable
                    suppressContentEditableWarning
                    onInput={handleInput}
                    onBlur={handleInput}
                    style={{
                        ...baseStyle,
                        border: '2px solid var(--color-primary, #635bff)',
                        outline: 'none',
                        margin: 0,
                        minHeight: '60px',
                    }}
                />
            </div>
        );
    }

    return (
        <pre
            style={{ ...baseStyle, margin: 0 }}
            dangerouslySetInnerHTML={{ __html: props.text || 'Write preformatted text...' }}
        />
    );
};

export default PreformattedBlock;
