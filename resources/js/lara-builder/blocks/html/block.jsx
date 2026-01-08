/**
 * HTML Block - Canvas Component
 *
 * Custom HTML block with TinyMCE editor modal.
 * Double-click to open the editor and edit HTML content.
 */

import { useState, useRef, useEffect } from 'react';
import { applyLayoutStyles } from '../../components/layout-styles/styleHelpers';

const HtmlBlock = ({ props, onUpdate }) => {
    const [isEditing, setIsEditing] = useState(false);
    const [editorReady, setEditorReady] = useState(false);
    const editorContainerRef = useRef(null);
    const editorInstanceRef = useRef(null);

    const handleDoubleClick = () => {
        setIsEditing(true);
    };

    const handleClose = () => {
        // Destroy editor before closing
        if (editorInstanceRef.current) {
            editorInstanceRef.current.destroy();
            editorInstanceRef.current = null;
            setEditorReady(false);
        }
        setIsEditing(false);
    };

    const handleSave = () => {
        if (editorInstanceRef.current) {
            const newCode = editorInstanceRef.current.getContent();
            onUpdate({ ...props, code: newCode });
        }
        handleClose();
    };

    // Initialize TinyMCE when modal opens
    useEffect(() => {
        if (!isEditing || !editorContainerRef.current) return;

        // Check if TinyMCE is loaded
        const initEditor = () => {
            if (typeof window.tinymce === 'undefined') {
                // Load TinyMCE script if not available
                const script = document.createElement('script');
                script.src = '/vendor/tinymce/tinymce.min.js';
                script.onload = () => initTinyMCE();
                document.head.appendChild(script);
            } else {
                initTinyMCE();
            }
        };

        const initTinyMCE = () => {
            if (!editorContainerRef.current || editorInstanceRef.current) return;

            window.tinymce.init({
                target: editorContainerRef.current,
                height: 450,
                menubar: false,
                branding: false,
                promotion: false,
                plugins: [
                    'advlist', 'autolink', 'lists', 'link', 'image', 'charmap',
                    'anchor', 'searchreplace', 'visualblocks', 'code',
                    'insertdatetime', 'media', 'table', 'wordcount'
                ],
                toolbar: 'undo redo | blocks | bold italic forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image table | removeformat code',
                toolbar_mode: 'wrap',
                content_style: `
                    body {
                        font-family: system-ui, -apple-system, sans-serif;
                        font-size: 14px;
                        line-height: 1.6;
                        padding: 12px;
                    }
                `,
                setup: (editor) => {
                    editorInstanceRef.current = editor;

                    editor.on('init', () => {
                        setEditorReady(true);
                        // Set initial content
                        editor.setContent(props.code || '');
                    });
                },
                // Enable code view
                code_dialog_height: 400,
                code_dialog_width: 600,
                // Fix z-index for dialogs to appear above our modal
                ui_options: {
                    z_index: 10100
                },
            });
        };

        initEditor();

        return () => {
            // Cleanup on unmount
            if (editorInstanceRef.current) {
                editorInstanceRef.current.destroy();
                editorInstanceRef.current = null;
                setEditorReady(false);
            }
        };
    }, [isEditing, props.code]);

    // Base styles for the container
    const defaultStyle = {
        padding: '8px',
        borderRadius: '4px',
        cursor: 'pointer',
        minHeight: '60px',
    };

    // Apply layout styles if provided
    const containerStyle = applyLayoutStyles(defaultStyle, props.layoutStyles);

    const placeholderContent = `
        <div style="padding: 24px; padding-top: 10px; text-align: center; color: #9ca3af; background: #f9fafb; border: 2px dashed #e5e7eb; border-radius: 8px;">
            <div style="font-size: 14px; font-weight: 500;">Custom HTML Block</div>
            <div style="font-size: 12px; margin-top: 4px;">Double-click to edit</div>
        </div>
    `;

    return (
        <div style={containerStyle} onDoubleClick={handleDoubleClick}>
            {/* Render HTML content or placeholder */}
            <div
                dangerouslySetInnerHTML={{
                    __html: props.code || placeholderContent
                }}
            />

            {/* TinyMCE dialog z-index fix - must be higher than our modal (z-9999) */}
            {isEditing && (
                <style>{`
                    .tox-tinymce-aux,
                    .tox .tox-dialog-wrap,
                    .tox .tox-dialog,
                    .tox .tox-dialog-wrap__backdrop,
                    div.tox.tox-silver-sink.tox-tinymce-aux {
                        z-index: 10100 !important;
                    }
                    .tox .tox-dialog-wrap__backdrop {
                        background-color: rgba(0, 0, 0, 0.5) !important;
                    }
                `}</style>
            )}

            {/* Editor Modal */}
            {isEditing && (
                <div
                    className="fixed inset-0 bg-black/50 flex items-center justify-center z-[9999]"
                    onClick={handleClose}
                >
                    <div
                        className="bg-white rounded-lg shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col mx-4"
                        onClick={e => e.stopPropagation()}
                    >
                        {/* Modal Header */}
                        <div className="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                            <div className="flex items-center gap-3">
                                <iconify-icon icon="mdi:code-tags" width="24" height="24" class="text-gray-600"></iconify-icon>
                                <h3 className="text-lg font-semibold text-gray-900">Edit Custom HTML</h3>
                            </div>
                            <button
                                type="button"
                                onClick={handleClose}
                                className="p-2 rounded-md hover:bg-gray-100 text-gray-500 transition-colors"
                            >
                                <iconify-icon icon="mdi:close" width="20" height="20"></iconify-icon>
                            </button>
                        </div>

                        {/* Editor Area */}
                        <div className="flex-1 overflow-hidden p-6">
                            {!editorReady && (
                                <div className="flex items-center justify-center h-[450px] bg-gray-50 rounded-md">
                                    <div className="flex items-center gap-2 text-gray-500">
                                        <svg className="animate-spin h-5 w-5" viewBox="0 0 24 24">
                                            <circle
                                                className="opacity-25"
                                                cx="12"
                                                cy="12"
                                                r="10"
                                                stroke="currentColor"
                                                strokeWidth="4"
                                                fill="none"
                                            />
                                            <path
                                                className="opacity-75"
                                                fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                            />
                                        </svg>
                                        <span>Loading editor...</span>
                                    </div>
                                </div>
                            )}
                            <div
                                ref={editorContainerRef}
                                className={editorReady ? '' : 'hidden'}
                            />
                        </div>

                        {/* Modal Footer */}
                        <div className="flex items-center justify-between px-6 py-4 border-t border-gray-200 bg-gray-50 rounded-b-lg">
                            <p className="text-sm text-gray-500">
                                Use the toolbar above to format content, or click "Code" to edit raw HTML.
                            </p>
                            <div className="flex gap-3">
                                <button
                                    type="button"
                                    onClick={handleClose}
                                    className="btn-default"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="button"
                                    onClick={handleSave}
                                    className="btn-primary"
                                >
                                    Save Changes
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
};

export default HtmlBlock;
