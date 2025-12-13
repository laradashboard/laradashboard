import { useRef, useEffect, useCallback, useState } from 'react';

/**
 * TextEditorBlock - A rich text editor block using TinyMCE with a floating toolbar
 *
 * Features:
 * - Minimalistic TinyMCE editor
 * - Floating toolbar that appears on selection
 * - Clean, distraction-free editing experience
 */
const TextEditorBlock = ({ props, onUpdate, isSelected, onRegisterTextFormat }) => {
    const editorRef = useRef(null);
    const containerRef = useRef(null);
    const [editorReady, setEditorReady] = useState(false);
    const [isInitializing, setIsInitializing] = useState(false);
    const propsRef = useRef(props);
    const onUpdateRef = useRef(onUpdate);
    const editorInstanceRef = useRef(null);

    // Keep refs updated
    propsRef.current = props;
    onUpdateRef.current = onUpdate;

    // Initialize TinyMCE when selected
    useEffect(() => {
        if (!isSelected || editorReady || isInitializing) return;

        // Check if TinyMCE is loaded
        if (typeof window.tinymce === 'undefined') {
            // Load TinyMCE script if not available
            const script = document.createElement('script');
            script.src = '/vendor/tinymce/tinymce.min.js';
            script.onload = () => initEditor();
            document.head.appendChild(script);
        } else {
            initEditor();
        }

        function initEditor() {
            if (!containerRef.current || editorInstanceRef.current) return;

            setIsInitializing(true);

            window.tinymce.init({
                target: containerRef.current,
                inline: true,
                menubar: false,
                statusbar: false,
                toolbar_mode: 'floating',
                toolbar:
                    'bold italic underline strikethrough | forecolor backcolor | alignleft aligncenter alignright | bullist numlist | link | removeformat',
                plugins: 'link lists',
                placeholder: 'Start typing...',
                content_style: `
                    body {
                        font-family: system-ui, -apple-system, sans-serif;
                        font-size: ${props.fontSize || '16px'};
                        line-height: ${props.lineHeight || '1.6'};
                        color: ${props.color || '#333333'};
                        margin: 0;
                        padding: 0;
                    }
                    p { margin: 0 0 1em 0; }
                    p:last-child { margin-bottom: 0; }
                `,
                setup: (editor) => {
                    editorInstanceRef.current = editor;

                    editor.on('init', () => {
                        setEditorReady(true);
                        setIsInitializing(false);
                        // Set initial content
                        editor.setContent(propsRef.current.content || '');
                        editor.focus();
                    });

                    editor.on('change keyup', () => {
                        const newContent = editor.getContent();
                        if (newContent !== propsRef.current.content) {
                            onUpdateRef.current({
                                ...propsRef.current,
                                content: newContent,
                            });
                        }
                    });

                    editor.on('blur', () => {
                        const newContent = editor.getContent();
                        // Only update if content has actually changed
                        if (newContent !== propsRef.current.content) {
                            onUpdateRef.current({
                                ...propsRef.current,
                                content: newContent,
                            });
                        }
                    });
                },
                // Floating toolbar configuration
                toolbar_sticky: false,
                ui_mode: 'split',
                quickbars_selection_toolbar:
                    'bold italic underline | forecolor | link',
                quickbars_insert_toolbar: false,
            });
        }

        return () => {
            // Cleanup on unmount or when deselected
            if (editorInstanceRef.current) {
                // Save content before destroying
                const content = editorInstanceRef.current.getContent();
                if (content !== propsRef.current.content) {
                    onUpdateRef.current({
                        ...propsRef.current,
                        content: content,
                    });
                }
                editorInstanceRef.current.destroy();
                editorInstanceRef.current = null;
                setEditorReady(false);
                setIsInitializing(false);
            }
        };
    }, [isSelected]);

    // Cleanup when deselected
    useEffect(() => {
        if (!isSelected && editorInstanceRef.current) {
            // Save content before destroying
            const content = editorInstanceRef.current.getContent();
            if (content !== propsRef.current.content) {
                onUpdateRef.current({
                    ...propsRef.current,
                    content: content,
                });
            }
            editorInstanceRef.current.destroy();
            editorInstanceRef.current = null;
            setEditorReady(false);
        }
    }, [isSelected]);

    // Handle external prop changes for alignment
    const handleAlignChange = useCallback((newAlign) => {
        onUpdateRef.current({ ...propsRef.current, align: newAlign });
    }, []);

    // Register with parent for text formatting
    useEffect(() => {
        if (isSelected && onRegisterTextFormat) {
            onRegisterTextFormat({
                editorRef: containerRef,
                isContentEditable: true,
                align: propsRef.current.align || 'left',
                onAlignChange: handleAlignChange,
                isTinyMCE: true,
            });
        } else if (!isSelected && onRegisterTextFormat) {
            onRegisterTextFormat(null);
        }
    }, [isSelected, onRegisterTextFormat, handleAlignChange]);

    const baseStyle = {
        textAlign: props.align || 'left',
        color: props.color || '#333333',
        fontSize: props.fontSize || '16px',
        lineHeight: props.lineHeight || '1.6',
        padding: '8px',
        borderRadius: '4px',
        minHeight: '40px',
    };

    if (isSelected) {
        return (
            <div data-text-editing="true" className="relative">
                <div
                    ref={containerRef}
                    className="focus-within:ring-2 focus-within:ring-primary rounded"
                    style={{
                        ...baseStyle,
                        width: '100%',
                        outline: 'none',
                        background: 'white',
                        cursor: 'text',
                    }}
                />
                {isInitializing && (
                    <div className="absolute inset-0 flex items-center justify-center bg-white/50">
                        <span className="text-sm text-gray-500">Loading editor...</span>
                    </div>
                )}
            </div>
        );
    }

    // Render HTML content safely for display
    const renderContent = () => {
        const content = props.content || '<p>Click to edit with rich text editor...</p>';
        return <div dangerouslySetInnerHTML={{ __html: content }} />;
    };

    return (
        <div style={baseStyle}>
            {renderContent()}
        </div>
    );
};

export default TextEditorBlock;
