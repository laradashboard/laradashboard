/**
 * Code Block - Canvas Component
 *
 * Renders the code block in the builder canvas with syntax highlighting.
 * Uses Prism.js for syntax highlighting when not editing.
 */

import { useRef, useEffect, useCallback, useState } from 'react';
import { applyLayoutStyles } from '../../components/layout-styles/styleHelpers';
import { useEditableContent } from '../../core/hooks/useEditableContent';

// Escape HTML to prevent XSS when using dangerouslySetInnerHTML
const escapeHtml = (text) => {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
};

// Load Prism.js for syntax highlighting
const loadPrism = () => {
    if (window.Prism) return Promise.resolve();

    const loadScript = (src) => {
        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = src;
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });
    };

    // Load CSS
    if (!document.querySelector('link[href*="prism-tomorrow"]')) {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css';
        document.head.appendChild(link);
    }

    // Load toolbar CSS for copy button
    if (!document.querySelector('link[href*="prism-toolbar"]')) {
        const toolbarCss = document.createElement('link');
        toolbarCss.rel = 'stylesheet';
        toolbarCss.href = 'https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/toolbar/prism-toolbar.min.css';
        document.head.appendChild(toolbarCss);
    }

    const baseUrl = 'https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/';
    const languages = ['markup', 'css', 'clike', 'javascript', 'markup-templating', 'php', 'typescript', 'jsx', 'tsx', 'scss', 'bash', 'json', 'yaml', 'sql', 'python'];

    return loadScript(baseUrl + 'prism.min.js').then(() => {
        return languages.reduce((promise, lang) => {
            return promise.then(() => {
                return loadScript(baseUrl + 'components/prism-' + lang + '.min.js').catch(() => {
                    console.warn('Failed to load Prism language:', lang);
                });
            });
        }, Promise.resolve());
    }).then(() => {
        return loadScript(baseUrl + 'plugins/toolbar/prism-toolbar.min.js');
    }).then(() => {
        return loadScript(baseUrl + 'plugins/copy-to-clipboard/prism-copy-to-clipboard.min.js');
    });
};

const CodeBlock = ({ props, onUpdate, isSelected }) => {
    const editorRef = useRef(null);
    const previewRef = useRef(null);
    const lastPropsCode = useRef(props.code);
    const propsRef = useRef(props);
    const onUpdateRef = useRef(onUpdate);
    const [prismLoaded, setPrismLoaded] = useState(!!window.Prism);

    // Keep refs updated
    propsRef.current = props;
    onUpdateRef.current = onUpdate;

    // Load Prism on mount
    useEffect(() => {
        if (!window.Prism) {
            loadPrism().then(() => {
                setPrismLoaded(true);
            });
        }
    }, []);

    // Highlight code when not selected and Prism is loaded
    useEffect(() => {
        if (!isSelected && prismLoaded && previewRef.current && window.Prism) {
            // Use setTimeout to ensure DOM is ready
            setTimeout(() => {
                if (previewRef.current && window.Prism) {
                    window.Prism.highlightAllUnder(previewRef.current);
                }
            }, 0);
        }
    }, [isSelected, prismLoaded, props.code, props.language]);

    // Use shared hook for content change detection
    const { handleContentChange } = useEditableContent({
        editorRef,
        contentKey: "code",
        useInnerHTML: false,
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
            requestAnimationFrame(() => {
                if (editorRef.current) {
                    editorRef.current.focus();
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

    const language = props.language || 'php';

    // Base styles for code block
    const defaultStyle = {
        fontFamily: 'ui-monospace, SFMono-Regular, "SF Mono", Menlo, Consolas, "Liberation Mono", monospace',
        fontSize: props.fontSize || '14px',
        lineHeight: '1.5',
        borderRadius: props.borderRadius || '8px',
        backgroundColor: '#1e1e1e',
        overflowX: 'auto',
    };

    const baseStyle = applyLayoutStyles(defaultStyle, props.layoutStyles);

    // Custom styles for Prism
    const prismStyles = `
        .code-block-preview pre[class*="language-"] {
            margin: 0 !important;
            padding: 16px !important;
            background: #1e1e1e !important;
            border-radius: ${props.borderRadius || '8px'} !important;
            font-size: ${props.fontSize || '14px'} !important;
        }
        .code-block-preview code[class*="language-"] {
            font-size: inherit !important;
            font-family: ui-monospace, SFMono-Regular, "SF Mono", Menlo, Consolas, "Liberation Mono", monospace !important;
        }
        .code-block-preview .code-toolbar .toolbar {
            opacity: 1;
            top: 8px;
            right: 8px;
        }
        .code-block-preview .code-toolbar .toolbar-item button {
            background: #3b3b3b !important;
            color: #e5e7eb !important;
            border-radius: 4px !important;
            padding: 4px 10px !important;
            font-size: 12px !important;
            box-shadow: none !important;
            border: 1px solid #4b4b4b !important;
            transition: all 0.2s ease !important;
            cursor: pointer;
        }
        .code-block-preview .code-toolbar .toolbar-item button:hover {
            background: #4b4b4b !important;
            color: #fff !important;
        }
    `;

    // Use key to force complete remount when switching modes
    // This prevents React from trying to reconcile Prism-modified DOM
    if (isSelected) {
        return (
            <div key="code-editor" data-text-editing="true" className="relative">
                <pre
                    ref={editorRef}
                    contentEditable
                    suppressContentEditableWarning
                    onInput={handleInput}
                    onBlur={handleInput}
                    style={{
                        ...baseStyle,
                        padding: '16px',
                        color: '#d4d4d4',
                        whiteSpace: 'pre-wrap',
                        wordBreak: 'break-word',
                        outline: 'none',
                        margin: 0,
                        minHeight: '60px',
                    }}
                />
            </div>
        );
    }

    // Preview mode - with syntax highlighting
    // Use dangerouslySetInnerHTML to prevent React from tracking the code content
    // since Prism will modify it
    return (
        <div key="code-preview" ref={previewRef} className="code-block-preview" style={{ position: 'relative' }}>
            <style>{prismStyles}</style>
            <pre className={`language-${language}`} style={{ ...baseStyle, margin: 0 }}>
                <code
                    className={`language-${language}`}
                    dangerouslySetInnerHTML={{ __html: escapeHtml(props.code || 'Click to add code...') }}
                />
            </pre>
        </div>
    );
};

export default CodeBlock;
