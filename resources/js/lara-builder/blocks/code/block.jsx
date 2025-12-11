/**
 * Code Block - Canvas Component
 *
 * Renders the code block in the builder canvas.
 */

import { useRef, useEffect, useCallback } from 'react';
import { applyLayoutStyles } from '../../components/layout-styles/styleHelpers';
import { useEditableContent } from '../../core/hooks/useEditableContent';

const CodeBlock = ({ props, onUpdate, isSelected }) => {
    const editorRef = useRef(null);
    const lastPropsCode = useRef(props.code);
    const propsRef = useRef(props);
    const onUpdateRef = useRef(onUpdate);

    // Keep refs updated
    propsRef.current = props;
    onUpdateRef.current = onUpdate;

    // Use shared hook for content change detection (textContent for code blocks)
    const { handleContentChange } = useEditableContent({
        editorRef,
        contentKey: "code",
        useInnerHTML: false, // Use textContent for code blocks
        propsRef,
        onUpdateRef,
        lastContentRef: lastPropsCode,
    });

    const handleInput = useCallback(() => {
        handleContentChange();
    }, [handleContentChange]);

    // Set initial content only once when becoming selected
    useEffect(() => {
        if (isSelected && editorRef.current) {
            if (editorRef.current.textContent === '') {
                editorRef.current.textContent = props.code || '';
                lastPropsCode.current = props.code;
            }
        }
    }, [isSelected]);

    // Handle external prop changes
    useEffect(() => {
        if (isSelected && editorRef.current) {
            if (props.code !== lastPropsCode.current) {
                editorRef.current.textContent = props.code || '';
                lastPropsCode.current = props.code;
            }
        }
    }, [props.code, isSelected]);

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

    // Base styles for code block
    const defaultStyle = {
        fontFamily: 'ui-monospace, SFMono-Regular, "SF Mono", Menlo, Consolas, "Liberation Mono", monospace',
        fontSize: props.fontSize || '14px',
        lineHeight: '1.5',
        padding: '16px',
        borderRadius: props.borderRadius || '8px',
        backgroundColor: props.backgroundColor || '#1e1e1e',
        color: props.textColor || '#d4d4d4',
        overflowX: 'auto',
        whiteSpace: 'pre-wrap',
        wordBreak: 'break-word',
    };

    // Apply layout styles (typography, background, spacing, border, shadow)
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
                        border: '2px solid #635bff',
                        outline: 'none',
                        margin: 0,
                        minHeight: '60px',
                    }}
                />
            </div>
        );
    }

    return (
        <pre style={{ ...baseStyle, margin: 0 }}>
            {props.code || 'Click to add code...'}
        </pre>
    );
};

export default CodeBlock;
