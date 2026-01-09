/**
 * Markdown Block - Canvas Component
 *
 * Supports two modes:
 * 1. Direct content: Write markdown directly in the editor (inline editing)
 * 2. URL: Fetch markdown from external URLs (GitHub, GitLab, etc.)
 */

import { useState, useEffect, useCallback, useRef } from 'react';
import { applyLayoutStyles } from '../../components/layout-styles/styleHelpers';

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

const MarkdownBlock = ({ props, onUpdate, isSelected }) => {
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);
    const [htmlContent, setHtmlContent] = useState('');
    const [cached, setCached] = useState(false);
    const [copySuccess, setCopySuccess] = useState(false);
    const [rawMarkdown, setRawMarkdown] = useState('');
    const fetchedUrlRef = useRef('');
    const convertedContentRef = useRef('');
    const contentRef = useRef(null);
    const editorRef = useRef(null);
    const lastContentRef = useRef(props.content);

    const sourceType = props.sourceType || 'content';

    // Copy markdown to clipboard
    const handleCopyMarkdown = useCallback(async () => {
        const markdownToCopy = sourceType === 'content' ? props.content : rawMarkdown;
        if (!markdownToCopy) return;

        try {
            await navigator.clipboard.writeText(markdownToCopy);
            setCopySuccess(true);
            setTimeout(() => setCopySuccess(false), 2000);
        } catch (err) {
            console.error('Failed to copy:', err);
        }
    }, [sourceType, props.content, rawMarkdown]);

    // Highlight code blocks when content changes
    // For URL mode: always highlight (not editable)
    // For content mode: only highlight when not selected (preview mode)
    useEffect(() => {
        const shouldHighlight = htmlContent && contentRef.current && (sourceType === 'url' || !isSelected);
        if (shouldHighlight) {
            loadPrism().then(() => {
                setTimeout(() => {
                    if (window.Prism && contentRef.current) {
                        window.Prism.highlightAllUnder(contentRef.current);
                    }
                }, 0);
            });
        }
    }, [htmlContent, isSelected, sourceType]);

    // Convert markdown content to HTML (for direct content mode)
    const convertMarkdown = useCallback(async (markdown) => {
        if (!markdown) {
            setHtmlContent('');
            return;
        }

        setLoading(true);
        setError(null);

        try {
            const response = await fetch('/api/admin/builder/markdown/convert', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                credentials: 'same-origin',
                body: JSON.stringify({ content: markdown }),
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(data.error || 'Failed to convert markdown');
            }

            setHtmlContent(data.html);
            convertedContentRef.current = markdown;
        } catch (err) {
            setError(err.message);
            setHtmlContent('');
        } finally {
            setLoading(false);
        }
    }, []);

    // Fetch markdown content from URL
    const fetchMarkdown = useCallback(async (url, refresh = false) => {
        if (!url) {
            setHtmlContent('');
            setError(null);
            return;
        }

        setLoading(true);
        setError(null);

        try {
            const response = await fetch('/api/admin/builder/markdown/fetch', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                credentials: 'same-origin',
                body: JSON.stringify({ url, refresh }),
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(data.error || 'Failed to fetch markdown');
            }

            setHtmlContent(data.html);
            setCached(data.cached || false);
            setRawMarkdown(data.markdown || '');
            fetchedUrlRef.current = url;
        } catch (err) {
            setError(err.message);
            setHtmlContent('');
        } finally {
            setLoading(false);
        }
    }, []);

    // Handle content changes based on source type
    useEffect(() => {
        if (sourceType === 'url' && props.url && props.url !== fetchedUrlRef.current) {
            fetchMarkdown(props.url, false);
        } else if (sourceType === 'content' && props.content && props.content !== convertedContentRef.current) {
            convertMarkdown(props.content);
        }
    }, [sourceType, props.url, props.content, fetchMarkdown, convertMarkdown]);

    // Convert when switching back to preview mode (deselected)
    useEffect(() => {
        if (!isSelected && sourceType === 'content' && props.content) {
            if (props.content !== convertedContentRef.current) {
                convertMarkdown(props.content);
            }
        }
    }, [isSelected, sourceType, props.content, convertMarkdown]);

    // Handle textarea changes
    const handleContentChange = useCallback((e) => {
        const newContent = e.target.value;
        onUpdate({ ...props, content: newContent });
    }, [props, onUpdate]);

    // Focus editor when selected
    useEffect(() => {
        if (isSelected && sourceType === 'content' && editorRef.current) {
            editorRef.current.focus();
        }
    }, [isSelected, sourceType]);

    const handleRefresh = () => {
        if (sourceType === 'url' && props.url) {
            fetchMarkdown(props.url, true);
        }
    };

    // Base styles
    const defaultStyle = {
        borderRadius: '4px',
        minHeight: '100px',
    };

    const containerStyle = applyLayoutStyles(defaultStyle, props.layoutStyles);

    // Markdown content styles
    const markdownStyles = `
        .markdown-content {
            font-family: system-ui, -apple-system, sans-serif;
            line-height: 1.6;
            color: #1f2937;
        }
        .markdown-content h1 { font-size: 2em; font-weight: 700; margin: 1em 0 0.5em; border-bottom: 1px solid #e5e7eb; padding-bottom: 0.3em; }
        .markdown-content h2 { font-size: 1.5em; font-weight: 600; margin: 1em 0 0.5em; border-bottom: 1px solid #e5e7eb; padding-bottom: 0.3em; }
        .markdown-content h3 { font-size: 1.25em; font-weight: 600; margin: 1em 0 0.5em; }
        .markdown-content h4 { font-size: 1em; font-weight: 600; margin: 1em 0 0.5em; }
        .markdown-content p { margin: 0 0 1em; }
        .markdown-content ul { margin: 0 0 1em; padding-left: 2em; list-style-type: disc; }
        .markdown-content ol { margin: 0 0 1em; padding-left: 2em; list-style-type: decimal; }
        .markdown-content li { margin: 0.25em 0; display: list-item; }
        .markdown-content code { background: #f3f4f6; padding: 0.2em 0.4em; border-radius: 4px; font-size: 0.875em; font-family: ui-monospace, monospace; color: #e83e8c; }
        .markdown-content pre[class*="language-"] { background: #1e1e1e; padding: 1em !important; border-radius: 8px; overflow-x: auto; margin: 0 0 1em; }
        .markdown-content pre code { display: block; background: transparent !important; font-size: 0.875em; line-height: 1.5; }
        .markdown-content blockquote { border-left: 4px solid #6366f1; padding-left: 1em; margin: 0 0 1em; color: #6b7280; font-style: italic; }
        .markdown-content a { color: #6366f1; text-decoration: underline; }
        .markdown-content a:hover { color: #4f46e5; }
        .markdown-content table { border-collapse: collapse; width: 100%; margin: 0 0 1em; }
        .markdown-content th, .markdown-content td { border: 1px solid #e5e7eb; padding: 0.5em 1em; text-align: left; }
        .markdown-content th { background: #f9fafb; font-weight: 600; }
        .markdown-content hr { border: none; border-top: 1px solid #e5e7eb; margin: 2em 0; }
        .markdown-content img { max-width: 100%; height: auto; border-radius: 8px; }
        .markdown-content input[type="checkbox"] { margin-right: 0.5em; }

        /* Copy button styling for Prism */
        .markdown-content .code-toolbar .toolbar { opacity: 1; }
        .markdown-content .code-toolbar .toolbar-item button {
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
        .markdown-content .code-toolbar .toolbar-item button:hover {
            background: #4b4b4b !important;
            color: #fff !important;
        }
    `;

    // Editing mode for content type - show textarea
    if (isSelected && sourceType === 'content') {
        return (
            <div style={containerStyle} data-text-editing="true">
                <textarea
                    ref={editorRef}
                    value={props.content || ''}
                    onChange={handleContentChange}
                    placeholder="# Hello World

Write your **markdown** content here...

- Item 1
- Item 2

```php
echo 'Hello';
```"
                    className="w-full px-4 py-3 border-0 rounded-lg bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white font-mono text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    style={{
                        minHeight: '200px',
                        resize: 'vertical',
                        lineHeight: '1.6',
                    }}
                />
            </div>
        );
    }

    // Preview mode (deselected or URL mode)
    return (
        <div style={containerStyle}>
            <style>{markdownStyles}</style>

            {/* Loading state */}
            {loading && (
                <div className="p-6 text-center">
                    <div className="flex items-center justify-center gap-2 text-gray-500">
                        <svg className="animate-spin h-5 w-5" viewBox="0 0 24 24">
                            <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" fill="none" />
                            <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                        </svg>
                        <span>{sourceType === 'url' ? 'Fetching markdown...' : 'Converting markdown...'}</span>
                    </div>
                </div>
            )}

            {/* Error state */}
            {error && !loading && (
                <div className="p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div className="flex items-center gap-2 text-red-600">
                        <iconify-icon icon="mdi:alert-circle" width="20" height="20"></iconify-icon>
                        <span className="text-sm font-medium">Error loading markdown</span>
                    </div>
                    <p className="text-xs text-red-500 mt-1">{error}</p>
                    {sourceType === 'url' && (
                        <button
                            type="button"
                            onClick={handleRefresh}
                            className="mt-2 text-xs text-red-600 underline hover:text-red-700"
                        >
                            Try again
                        </button>
                    )}
                </div>
            )}

            {/* Content display */}
            {!loading && !error && htmlContent && (
                <div className="relative">
                    {/* Copy Markdown button - shown when selected */}
                    {isSelected && (props.content || rawMarkdown) && (
                        <div className="absolute top-2 right-2 z-10">
                            <button
                                type="button"
                                onClick={handleCopyMarkdown}
                                className={`flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-md transition-all ${
                                    copySuccess
                                        ? 'bg-green-100 text-green-700 border border-green-200'
                                        : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50 hover:border-gray-300 shadow-sm'
                                }`}
                                title="Copy raw markdown"
                            >
                                <iconify-icon
                                    icon={copySuccess ? 'mdi:check' : 'mdi:content-copy'}
                                    width="14"
                                    height="14"
                                ></iconify-icon>
                                {copySuccess ? 'Copied!' : 'Copy Markdown'}
                            </button>
                        </div>
                    )}

                    {/* Source indicator (URL mode only) */}
                    {props.showSource && sourceType === 'url' && props.url && (
                        <div className="mb-3 flex items-center justify-between text-xs text-gray-500 bg-gray-50 dark:bg-gray-800 px-3 py-2 rounded-md">
                            <div className="flex items-center gap-2 truncate">
                                <iconify-icon icon="mdi:link-variant" width="14" height="14"></iconify-icon>
                                <span className="truncate" title={props.url}>{props.url}</span>
                            </div>
                            <div className="flex items-center gap-2 shrink-0 ml-2">
                                {cached && (
                                    <span className="text-green-600 flex items-center gap-1">
                                        <iconify-icon icon="mdi:cached" width="14" height="14"></iconify-icon>
                                        cached
                                    </span>
                                )}
                                <button
                                    type="button"
                                    onClick={(e) => { e.stopPropagation(); handleRefresh(); }}
                                    className="text-gray-500 hover:text-gray-700 p-1"
                                    title="Refresh content"
                                >
                                    <iconify-icon icon="mdi:refresh" width="14" height="14"></iconify-icon>
                                </button>
                            </div>
                        </div>
                    )}

                    {/* Rendered markdown */}
                    <div
                        key={isSelected ? 'selected' : 'preview'}
                        ref={contentRef}
                        className="markdown-content"
                        dangerouslySetInnerHTML={{ __html: htmlContent }}
                    />
                </div>
            )}

            {/* Placeholder when no content */}
            {!loading && !error && !htmlContent && (
                <div className="p-6 text-center text-gray-400 bg-gray-50 dark:bg-gray-800 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg">
                    <iconify-icon icon="mdi:language-markdown" width="48" height="48" class="mb-2 opacity-50"></iconify-icon>
                    <div className="text-sm font-medium">Markdown Block</div>
                    <div className="text-xs mt-1">
                        {sourceType === 'content'
                            ? 'Click to start writing markdown'
                            : 'Enter a markdown URL in the sidebar'
                        }
                    </div>
                </div>
            )}
        </div>
    );
};

export default MarkdownBlock;
