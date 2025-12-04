import { useRef, useEffect, useCallback } from 'react';
import { layoutStylesToCSS } from '../../components/layout-styles/styleHelpers';

const CodeBlock = ({ props, onUpdate, isSelected }) => {
    const editorRef = useRef(null);
    const lastPropsCode = useRef(props.code);
    const propsRef = useRef(props);
    const onUpdateRef = useRef(onUpdate);

    // Keep refs updated
    propsRef.current = props;
    onUpdateRef.current = onUpdate;

    const handleInput = useCallback(() => {
        if (editorRef.current) {
            const newCode = editorRef.current.textContent;
            lastPropsCode.current = newCode;
            onUpdateRef.current({ ...propsRef.current, code: newCode });
        }
    }, []);

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

    // Get layout styles (typography, background, spacing, etc.)
    const layoutStyles = layoutStylesToCSS(props.layoutStyles || {});

    const baseStyle = {
        fontFamily: layoutStyles.fontFamily || 'ui-monospace, SFMono-Regular, "SF Mono", Menlo, Consolas, "Liberation Mono", monospace',
        fontSize: layoutStyles.fontSize || props.fontSize || '14px',
        lineHeight: layoutStyles.lineHeight || '1.5',
        fontWeight: layoutStyles.fontWeight,
        fontStyle: layoutStyles.fontStyle,
        letterSpacing: layoutStyles.letterSpacing,
        textTransform: layoutStyles.textTransform,
        textDecoration: layoutStyles.textDecoration,
        padding: '16px',
        borderRadius: props.borderRadius || '8px',
        backgroundColor: layoutStyles.backgroundColor || props.backgroundColor || '#1e1e1e',
        backgroundImage: layoutStyles.backgroundImage,
        backgroundSize: layoutStyles.backgroundSize,
        backgroundPosition: layoutStyles.backgroundPosition,
        backgroundRepeat: layoutStyles.backgroundRepeat,
        color: layoutStyles.color || props.textColor || '#d4d4d4',
        overflowX: 'auto',
        whiteSpace: 'pre-wrap',
        wordBreak: 'break-word',
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
