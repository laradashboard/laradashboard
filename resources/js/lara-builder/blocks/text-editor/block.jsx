import { useRef, useEffect, useLayoutEffect, useCallback, useState } from 'react';
import { __ } from '@lara-builder/i18n';
import { applyLayoutStyles } from '../../components/layout-styles/styleHelpers';
import { resolvePageTextColor } from '@lara-builder/tokens/contentTokens';

const TINYMCE_SCRIPT_SRC = '/vendor/tinymce/tinymce.min.js';

let tinymceLoadPromise = null;

/**
 * Load TinyMCE once and reuse the same promise across block instances.
 */
function loadTinyMCE() {
    if (typeof window === 'undefined') {
        return Promise.reject(new Error('TinyMCE requires a browser environment'));
    }

    if (typeof window.tinymce !== 'undefined') {
        return Promise.resolve(window.tinymce);
    }

    if (tinymceLoadPromise) {
        return tinymceLoadPromise;
    }

    tinymceLoadPromise = new Promise((resolve, reject) => {
        const existing = document.querySelector(`script[src="${TINYMCE_SCRIPT_SRC}"]`);
        if (existing) {
            existing.addEventListener('load', () => resolve(window.tinymce));
            existing.addEventListener('error', () => {
                tinymceLoadPromise = null;
                reject(new Error('Failed to load TinyMCE'));
            });
            return;
        }

        const script = document.createElement('script');
        script.src = TINYMCE_SCRIPT_SRC;
        script.async = true;
        script.onload = () => resolve(window.tinymce);
        script.onerror = () => {
            tinymceLoadPromise = null;
            reject(new Error('Failed to load TinyMCE'));
        };
        document.head.appendChild(script);
    });

    return tinymceLoadPromise;
}

/**
 * Remove orphaned TinyMCE dialog chrome that can freeze the builder after destroy.
 */
function scrubOrphanedTinyMCEUi() {
    if (typeof document === 'undefined') {
        return;
    }

    // If no live editors remain, drop leftover aux dialog wrappers.
    const hasLiveEditor =
        typeof window.tinymce !== 'undefined' &&
        Array.from(window.tinymce.get() || []).some((ed) => ed && !ed.removed);

    if (hasLiveEditor) {
        return;
    }

    document
        .querySelectorAll('.tox-dialog-wrap, .tox-dialog-wrap__backdrop')
        .forEach((el) => el.remove());
}

/**
 * Safely destroy a TinyMCE editor and close open dialogs.
 * Prevents orphaned .tox-dialog backdrops that freeze the builder UI.
 */
function destroyTinyMCEEditor(editor) {
    if (!editor) {
        return '';
    }

    let content = '';
    const target = editor.getElement?.() || editor.targetElm || null;

    try {
        if (!editor.removed) {
            content = editor.getContent() || '';
            editor.windowManager?.close?.();
            editor.destroy();
        }
    } catch (_error) {
        // Editor may already be partially torn down by React unmount.
    }

    // TinyMCE leaves id/mce classes on the host node; strip them so a reused
    // React fiber cannot keep mce_* residue in the deselected preview.
    if (target && target.getAttribute) {
        if ((target.id || '').startsWith('mce_')) {
            target.removeAttribute('id');
        }
        target.classList?.remove('mce-content-body', 'mce-edit-focus');
        target.removeAttribute('contenteditable');
        target.removeAttribute('spellcheck');
    }

    scrubOrphanedTinyMCEUi();

    return content;
}

/**
 * TextEditorBlock - Rich text editor using TinyMCE inline mode.
 */
const TextEditorBlock = ({
    props,
    onUpdate,
    isSelected,
    onRegisterTextFormat,
}) => {
    const containerRef = useRef(null);
    const propsRef = useRef(props);
    const onUpdateRef = useRef(onUpdate);
    const editorInstanceRef = useRef(null);
    const mountedRef = useRef(true);
    const initGenerationRef = useRef(0);
    const lastSyncedContent = useRef(props.content || '');

    const [editorReady, setEditorReady] = useState(false);
    const [isInitializing, setIsInitializing] = useState(false);

    propsRef.current = props;
    onUpdateRef.current = onUpdate;

    // Initialize / destroy TinyMCE with selection lifecycle.
    // useLayoutEffect so we tear down (and close dialogs) before paint when
    // deselected — avoids orphaned backdrops and mce_* DOM reuse flashes.
    useLayoutEffect(() => {
        mountedRef.current = true;

        if (!isSelected) {
            return undefined;
        }

        const generation = ++initGenerationRef.current;
        let cancelled = false;

        const initEditor = async () => {
            setIsInitializing(true);

            try {
                await loadTinyMCE();
            } catch (_error) {
                if (!cancelled && mountedRef.current) {
                    setIsInitializing(false);
                }
                return;
            }

            if (
                cancelled ||
                !mountedRef.current ||
                generation !== initGenerationRef.current ||
                !containerRef.current ||
                editorInstanceRef.current
            ) {
                return;
            }

            // Seed DOM so content survives a slow / failed init and avoids blank flash
            const initialContent = propsRef.current.content || '';
            containerRef.current.innerHTML = initialContent;
            lastSyncedContent.current = initialContent;

            const currentProps = propsRef.current;
            const resolvedColor =
                resolvePageTextColor(
                    currentProps.color,
                    currentProps.layoutStyles?.typography?.color
                ) || currentProps.color || '#333333';

            window.tinymce.init({
                target: containerRef.current,
                inline: true,
                menubar: false,
                statusbar: false,
                branding: false,
                promotion: false,
                toolbar_mode: 'floating',
                toolbar:
                    'bold italic underline strikethrough | forecolor backcolor | alignleft aligncenter alignright | bullist numlist | link | removeformat',
                plugins: 'link lists quickbars',
                placeholder: __('Start typing...'),
                skin: document.documentElement.classList.contains('dark')
                    ? 'oxide-dark'
                    : 'oxide',
                content_style: `
                    font-family: system-ui, -apple-system, sans-serif;
                    font-size: ${currentProps.fontSize || '16px'};
                    line-height: ${currentProps.lineHeight || '1.6'};
                    color: ${resolvedColor};
                    margin: 0;
                    padding: 0;
                    p { margin: 0 0 1em 0; }
                    p:last-child { margin-bottom: 0; }
                    ul { list-style-type: disc; list-style-position: outside; margin: 0.75em 0; padding-left: 1.5em; }
                    ol { list-style-type: decimal; list-style-position: outside; margin: 0.75em 0; padding-left: 1.5em; }
                    li { margin: 0.25em 0; display: list-item; }
                    ul ul { list-style-type: circle; }
                    ol ol { list-style-type: lower-alpha; }
                `,
                quickbars_selection_toolbar:
                    'bold italic underline | forecolor | quicklink',
                quickbars_insert_toolbar: false,
                toolbar_sticky: false,
                setup: (editor) => {
                    editorInstanceRef.current = editor;

                    editor.on('init', () => {
                        if (
                            cancelled ||
                            !mountedRef.current ||
                            generation !== initGenerationRef.current
                        ) {
                            destroyTinyMCEEditor(editor);
                            if (editorInstanceRef.current === editor) {
                                editorInstanceRef.current = null;
                            }
                            return;
                        }

                        editor.setContent(propsRef.current.content || '');
                        lastSyncedContent.current = propsRef.current.content || '';
                        setEditorReady(true);
                        setIsInitializing(false);
                        editor.focus();
                    });

                    const syncFromEditor = () => {
                        const newContent = editor.getContent();
                        if (newContent !== propsRef.current.content) {
                            lastSyncedContent.current = newContent;
                            onUpdateRef.current({
                                ...propsRef.current,
                                content: newContent,
                            });
                        }
                    };

                    editor.on('change keyup NodeChange SetContent', syncFromEditor);
                    editor.on('blur', syncFromEditor);
                },
            });
        };

        initEditor();

        return () => {
            cancelled = true;
            initGenerationRef.current += 1;

            const editor = editorInstanceRef.current;
            editorInstanceRef.current = null;

            if (editor) {
                const content = destroyTinyMCEEditor(editor);
                lastSyncedContent.current = content;
                if (content !== propsRef.current.content) {
                    onUpdateRef.current({
                        ...propsRef.current,
                        content,
                    });
                }
            }

            setEditorReady(false);
            setIsInitializing(false);
        };
    }, [isSelected]);

    useEffect(() => {
        return () => {
            mountedRef.current = false;
        };
    }, []);

    // Sync wrapper-level style props while the editor is open (sidebar / undo)
    useEffect(() => {
        if (!isSelected || !containerRef.current) {
            return;
        }

        containerRef.current.style.textAlign = props.align || 'left';
        const resolvedColor =
            resolvePageTextColor(
                props.color,
                props.layoutStyles?.typography?.color
            ) || props.color || '';
        if (resolvedColor) {
            containerRef.current.style.color = resolvedColor;
        }
        if (props.fontSize) {
            containerRef.current.style.fontSize = props.fontSize;
        }
        if (props.lineHeight) {
            containerRef.current.style.lineHeight = props.lineHeight;
        }
    }, [
        isSelected,
        props.align,
        props.color,
        props.fontSize,
        props.lineHeight,
        props.layoutStyles,
    ]);

    // Sync external content changes (undo/redo, AI fill) when editor is not focused
    useEffect(() => {
        if (!isSelected || !editorInstanceRef.current || !editorReady) {
            return;
        }

        const incoming = props.content || '';
        if (incoming === lastSyncedContent.current) {
            return;
        }

        const editor = editorInstanceRef.current;
        if (editor.hasFocus?.()) {
            return;
        }

        lastSyncedContent.current = incoming;
        editor.setContent(incoming);
    }, [props.content, isSelected, editorReady]);

    const handleAlignChange = useCallback((newAlign) => {
        onUpdateRef.current({ ...propsRef.current, align: newAlign });
    }, []);

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
    }, [isSelected, onRegisterTextFormat, handleAlignChange, props.align]);

    const defaultStyle = {
        textAlign: props.align || 'left',
        fontSize: props.fontSize || '16px',
        lineHeight: props.lineHeight || '1.6',
        padding: '8px',
        borderRadius: '4px',
        minHeight: '40px',
    };

    const resolvedColor = resolvePageTextColor(
        props.color,
        props.layoutStyles?.typography?.color
    );
    if (resolvedColor) {
        defaultStyle.color = resolvedColor;
    } else if (props.color) {
        defaultStyle.color = props.color;
    }

    const baseStyle = applyLayoutStyles(defaultStyle, props.layoutStyles);
    const isEmpty =
        !props.content ||
        props.content === '<p></p>' ||
        props.content === '<p><br></p>' ||
        props.content === '<p><br data-mce-bogus="1"></p>';

    // Separate keys force remount when toggling selection so React does not
    // reuse the TinyMCE-mutated DOM node as the static preview container.
    if (isSelected) {
        return (
            <div
                key="text-editor-editing"
                data-text-editing="true"
                data-no-selection-style="true"
                className="relative"
            >
                <div
                    ref={containerRef}
                    className="rounded focus-within:ring-2 focus-within:ring-primary"
                    style={{
                        ...baseStyle,
                        width: '100%',
                        outline: 'none',
                        cursor: 'text',
                    }}
                    suppressContentEditableWarning
                />
                {isInitializing && !editorReady && (
                    <div className="absolute inset-0 flex items-center justify-center bg-white/50 dark:bg-gray-900/50 rounded">
                        <span className="text-sm text-gray-500 dark:text-gray-400">
                            {__('Loading editor...')}
                        </span>
                    </div>
                )}
            </div>
        );
    }

    return (
        <div key="text-editor-preview" style={baseStyle}>
            {isEmpty ? (
                <span style={{ color: '#9ca3af' }}>
                    {__('Click to edit with rich text editor...')}
                </span>
            ) : (
                <div dangerouslySetInnerHTML={{ __html: props.content }} />
            )}
        </div>
    );
};

export default TextEditorBlock;
