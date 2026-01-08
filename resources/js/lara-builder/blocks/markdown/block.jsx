/**
 * Markdown Block - Canvas Component
 *
 * Fetches and displays markdown content from external URLs (GitHub, GitLab, etc.)
 * The content is fetched server-side to avoid CORS issues and enable caching.
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
    // Order matters: markup -> markup-templating -> php (dependency chain)
    const languages = ['markup', 'css', 'clike', 'javascript', 'markup-templating', 'php', 'typescript', 'jsx', 'tsx', 'scss', 'bash', 'json', 'yaml', 'sql', 'python'];

    // Load core first, then languages sequentially, then plugins
    return loadScript(baseUrl + 'prism.min.js').then(() => {
        return languages.reduce((promise, lang) => {
            return promise.then(() => {
                return loadScript(baseUrl + 'components/prism-' + lang + '.min.js').catch(() => {
                    console.warn('Failed to load Prism language:', lang);
                });
            });
        }, Promise.resolve());
    }).then(() => {
        // Load toolbar plugin (required for copy button)
        return loadScript(baseUrl + 'plugins/toolbar/prism-toolbar.min.js');
    }).then(() => {
        // Load copy-to-clipboard plugin
        return loadScript(baseUrl + 'plugins/copy-to-clipboard/prism-copy-to-clipboard.min.js');
    });
};

const MarkdownBlock = ({ props, onUpdate }) => {
    const [isEditing, setIsEditing] = useState(false);
    const [urlInput, setUrlInput] = useState(props.url || '');
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);
    const [htmlContent, setHtmlContent] = useState('');
    const [cached, setCached] = useState(false);
    const fetchedUrlRef = useRef('');
    const contentRef = useRef(null);

    // Highlight code blocks when content changes
    useEffect(() => {
        if (htmlContent && contentRef.current) {
            loadPrism().then(() => {
                if (window.Prism && contentRef.current) {
                    window.Prism.highlightAllUnder(contentRef.current);
                }
            });
        }
    }, [htmlContent]);

    // Fetch markdown content from the server
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
                body: JSON.stringify({
                    url,
                    refresh,
                }),
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(data.error || 'Failed to fetch markdown');
            }

            setHtmlContent(data.html);
            setCached(data.cached || false);
            fetchedUrlRef.current = url;
        } catch (err) {
            setError(err.message);
            setHtmlContent('');
        } finally {
            setLoading(false);
        }
    }, []);

    // Fetch content when URL changes
    useEffect(() => {
        if (props.url && props.url !== fetchedUrlRef.current) {
            fetchMarkdown(props.url, false);
        }
    }, [props.url, fetchMarkdown]);

    const handleDoubleClick = () => {
        setIsEditing(true);
        setUrlInput(props.url || '');
    };

    const handleClose = () => {
        setIsEditing(false);
    };

    const handleSave = () => {
        onUpdate({ ...props, url: urlInput });
        setIsEditing(false);

        // Fetch if URL changed
        if (urlInput !== props.url) {
            fetchMarkdown(urlInput, false);
        }
    };

    const handleRefresh = () => {
        if (props.url) {
            fetchMarkdown(props.url, true);
        }
    };

    // Convert GitHub URL to preview (show expected raw URL)
    const getDisplayUrl = (url) => {
        if (!url) return '';

        // GitHub blob -> raw
        if (url.includes('github.com') && url.includes('/blob/')) {
            return url.replace('github.com', 'raw.githubusercontent.com').replace('/blob/', '/');
        }

        return url;
    };

    // Base styles for the container
    const defaultStyle = {
        borderRadius: '4px',
        minHeight: '100px',
    };

    // Apply layout styles if provided
    const containerStyle = applyLayoutStyles(defaultStyle, props.layoutStyles);

    const placeholderContent = (
        <div className="p-6 text-center text-gray-400 bg-gray-50 dark:bg-gray-800 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg">
            <iconify-icon icon="mdi:language-markdown" width="48" height="48" class="mb-2 opacity-50"></iconify-icon>
            <div className="text-sm font-medium">Markdown Block</div>
            <div className="text-xs mt-1">Double-click to add a markdown URL</div>
        </div>
    );

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
        .markdown-content ul, .markdown-content ol { margin: 0 0 1em; padding-left: 2em; }
        .markdown-content li { margin: 0.25em 0; }
        .markdown-content code { background: #f3f4f6; padding: 0.2em 0.4em; border-radius: 4px; font-size: 0.875em; font-family: ui-monospace, monospace; color: #e83e8c; }
        .markdown-content pre { background: #1e1e1e !important; padding: 0 !important; border-radius: 8px; overflow-x: auto; margin: 0 0 1em; }
        .markdown-content pre code { display: block; padding: 1em !important; background: transparent !important; color: #d4d4d4; font-size: 0.875em; line-height: 1.5; }
        .markdown-content pre code[class*="language-"] { color: inherit; }
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

    return (
        <div style={containerStyle} onDoubleClick={handleDoubleClick}>
            <style>{markdownStyles}</style>

            {/* Loading state */}
            {loading && (
                <div className="p-6 text-center">
                    <div className="flex items-center justify-center gap-2 text-gray-500">
                        <svg className="animate-spin h-5 w-5" viewBox="0 0 24 24">
                            <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" fill="none" />
                            <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                        </svg>
                        <span>Fetching markdown...</span>
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
                    <button
                        type="button"
                        onClick={handleRefresh}
                        className="mt-2 text-xs text-red-600 underline hover:text-red-700"
                    >
                        Try again
                    </button>
                </div>
            )}

            {/* Content display */}
            {!loading && !error && htmlContent && (
                <div className="relative">
                    {/* Source indicator */}
                    {props.showSource && props.url && (
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
                        ref={contentRef}
                        className="markdown-content"
                        dangerouslySetInnerHTML={{ __html: htmlContent }}
                    />
                </div>
            )}

            {/* Placeholder when no URL */}
            {!loading && !error && !htmlContent && placeholderContent}

            {/* Editor Modal */}
            {isEditing && (
                <div
                    className="fixed inset-0 bg-black/50 flex items-center justify-center z-[9999]"
                    onClick={handleClose}
                >
                    <div
                        className="bg-white dark:bg-gray-900 rounded-lg shadow-2xl w-full max-w-2xl flex flex-col mx-4"
                        onClick={e => e.stopPropagation()}
                    >
                        {/* Modal Header */}
                        <div className="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <div className="flex items-center gap-3">
                                <iconify-icon icon="mdi:language-markdown" width="24" height="24" class="text-gray-600 dark:text-gray-400"></iconify-icon>
                                <h3 className="text-lg font-semibold text-gray-900 dark:text-white">Markdown URL</h3>
                            </div>
                            <button
                                type="button"
                                onClick={handleClose}
                                className="p-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-500 transition-colors"
                            >
                                <iconify-icon icon="mdi:close" width="20" height="20"></iconify-icon>
                            </button>
                        </div>

                        {/* Modal Content */}
                        <div className="p-6">
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Markdown URL
                            </label>
                            <input
                                type="url"
                                value={urlInput}
                                onChange={(e) => setUrlInput(e.target.value)}
                                placeholder="https://github.com/user/repo/blob/main/README.md"
                                className="form-input w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:text-white"
                            />
                            <p className="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                Supports GitHub, GitLab, Bitbucket, and any direct markdown file URL.
                            </p>

                            {/* URL Preview */}
                            {urlInput && getDisplayUrl(urlInput) !== urlInput && (
                                <div className="mt-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                    <p className="text-xs text-gray-500 dark:text-gray-400 mb-1">Will be fetched from:</p>
                                    <p className="text-xs text-gray-700 dark:text-gray-300 break-all font-mono">
                                        {getDisplayUrl(urlInput)}
                                    </p>
                                </div>
                            )}

                            {/* Supported sources */}
                            <div className="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <p className="text-xs text-gray-500 dark:text-gray-400 mb-2">Supported sources:</p>
                                <div className="flex flex-wrap gap-2">
                                    {['GitHub', 'GitLab', 'Bitbucket', 'Any .md URL'].map((source) => (
                                        <span key={source} className="inline-flex items-center px-2 py-1 text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded">
                                            {source}
                                        </span>
                                    ))}
                                </div>
                            </div>

                            {/* Options */}
                            <div className="mt-4 space-y-3">
                                <label className="flex items-center gap-2 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        checked={props.showSource !== false}
                                        onChange={(e) => onUpdate({ ...props, showSource: e.target.checked })}
                                        className="form-checkbox h-4 w-4 text-indigo-600 rounded"
                                    />
                                    <span className="text-sm text-gray-700 dark:text-gray-300">Show source URL indicator</span>
                                </label>
                            </div>
                        </div>

                        {/* Modal Footer */}
                        <div className="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 rounded-b-lg">
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
                                Save
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
};

export default MarkdownBlock;
