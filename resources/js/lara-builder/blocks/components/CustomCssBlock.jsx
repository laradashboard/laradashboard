import { useRef, useEffect, useCallback } from 'react';
import { applyLayoutStyles } from '../../components/layout-styles/styleHelpers';

/**
 * CustomCssBlock - Custom CSS block for email templates
 *
 * Features:
 * - Inline CSS editor with syntax highlighting styles
 * - CSS will be injected into email template <style> tag
 * - Supports all standard CSS properties
 */
const CustomCssBlock = ({ props, onUpdate, isSelected }) => {
    const editorRef = useRef(null);
    const lastPropsCode = useRef(props.css);
    const propsRef = useRef(props);
    const onUpdateRef = useRef(onUpdate);

    // Keep refs updated
    propsRef.current = props;
    onUpdateRef.current = onUpdate;

    const handleInput = useCallback(() => {
        if (editorRef.current) {
            const newCss = editorRef.current.textContent;
            lastPropsCode.current = newCss;
            onUpdateRef.current({ ...propsRef.current, css: newCss });
        }
    }, []);

    // Set initial content only once when becoming selected
    useEffect(() => {
        if (isSelected && editorRef.current) {
            if (editorRef.current.textContent === '') {
                editorRef.current.textContent = props.css || '';
                lastPropsCode.current = props.css;
            }
        }
    }, [isSelected]);

    // Handle external prop changes
    useEffect(() => {
        if (isSelected && editorRef.current) {
            if (props.css !== lastPropsCode.current) {
                editorRef.current.textContent = props.css || '';
                lastPropsCode.current = props.css;
            }
        }
    }, [props.css, isSelected]);

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

    // Base styles for CSS block
    const defaultStyle = {
        fontFamily: 'ui-monospace, SFMono-Regular, "SF Mono", Menlo, Consolas, "Liberation Mono", monospace',
        fontSize: '13px',
        lineHeight: '1.5',
        padding: '16px',
        borderRadius: '6px',
        backgroundColor: '#1e293b',
        color: '#e2e8f0',
        overflowX: 'auto',
        whiteSpace: 'pre-wrap',
        wordBreak: 'break-word',
    };

    // Apply layout styles (typography, background, spacing, border, shadow)
    const baseStyle = applyLayoutStyles(defaultStyle, props.layoutStyles);

    if (isSelected) {
        return (
            <div data-text-editing="true" className="relative">
                <div
                    style={{
                        ...baseStyle,
                        border: '2px solid #635bff',
                        position: 'relative',
                    }}
                >
                    <div
                        style={{
                            position: 'absolute',
                            top: '4px',
                            right: '8px',
                            fontSize: '10px',
                            fontWeight: 'bold',
                            color: '#94a3b8',
                            textTransform: 'uppercase',
                            letterSpacing: '0.05em',
                            pointerEvents: 'none',
                        }}
                    >
                        CSS
                    </div>
                    <pre
                        ref={editorRef}
                        contentEditable
                        suppressContentEditableWarning
                        onInput={handleInput}
                        onBlur={handleInput}
                        style={{
                            outline: 'none',
                            margin: 0,
                            minHeight: '80px',
                            paddingTop: '24px',
                            cursor: 'text',
                            userSelect: 'text',
                            WebkitUserSelect: 'text',
                            MozUserSelect: 'text',
                            msUserSelect: 'text',
                        }}
                    />
                </div>
                <p
                    style={{
                        margin: '8px 0 0 0',
                        fontSize: '11px',
                        color: '#64748b',
                        fontStyle: 'italic',
                    }}
                >
                    ✓ This CSS will be injected into the final email template. It won't appear as visible content.
                </p>
            </div>
        );
    }

    return (
        <div style={{ position: 'relative', opacity: 0.7 }}>
            <div
                style={{
                    position: 'absolute',
                    top: '4px',
                    right: '8px',
                    fontSize: '10px',
                    fontWeight: 'bold',
                    color: '#94a3b8',
                    textTransform: 'uppercase',
                    letterSpacing: '0.05em',
                    zIndex: 1,
                }}
            >
                CSS
            </div>
            <div
                style={{
                    position: 'absolute',
                    top: '50%',
                    left: '50%',
                    transform: 'translate(-50%, -50%)',
                    fontSize: '12px',
                    color: '#94a3b8',
                    fontWeight: '500',
                    textAlign: 'center',
                    zIndex: 1,
                    pointerEvents: 'none',
                }}
            >
                <div style={{ marginBottom: '4px' }}>✓ CSS Applied</div>
                <div style={{ fontSize: '10px', opacity: 0.7 }}>Not visible in email</div>
            </div>
            <pre style={{ ...baseStyle, margin: 0, paddingTop: '24px', minHeight: '60px', position: 'relative' }}>
                {props.css || '/* Add your custom CSS here */\n.my-class {\n  color: #333;\n  font-size: 16px;\n}'}
            </pre>
        </div>
    );
};

export default CustomCssBlock;
