import { useState, useEffect, useRef } from 'react';

/**
 * CodeEditor - HTML code editor view for the builder
 *
 * Allows editing the raw HTML output directly
 */
const CodeEditor = ({ html, onHtmlChange, canvasSettings, onExitCodeEditor }) => {
    const [localHtml, setLocalHtml] = useState(html);
    const textareaRef = useRef(null);
    const [lineCount, setLineCount] = useState(1);

    // Sync with external html prop
    useEffect(() => {
        setLocalHtml(html);
    }, [html]);

    // Count lines for line numbers
    useEffect(() => {
        const lines = (localHtml || '').split('\n').length;
        setLineCount(Math.max(lines, 20));
    }, [localHtml]);

    const handleChange = (e) => {
        const newHtml = e.target.value;
        setLocalHtml(newHtml);
    };

    const handleBlur = () => {
        onHtmlChange(localHtml);
    };

    // Handle Tab key for indentation
    const handleKeyDown = (e) => {
        if (e.key === 'Tab') {
            e.preventDefault();
            const start = e.target.selectionStart;
            const end = e.target.selectionEnd;
            const newValue = localHtml.substring(0, start) + '    ' + localHtml.substring(end);
            setLocalHtml(newValue);

            // Set cursor position after the tab
            setTimeout(() => {
                e.target.selectionStart = e.target.selectionEnd = start + 4;
            }, 0);
        }
    };

    // Format/prettify HTML
    const formatHtml = () => {
        try {
            // Simple HTML formatting
            let formatted = localHtml
                .replace(/></g, '>\n<')
                .replace(/\n\s*\n/g, '\n');

            // Basic indentation
            let indent = 0;
            const lines = formatted.split('\n');
            formatted = lines.map(line => {
                line = line.trim();
                if (!line) return '';

                // Decrease indent for closing tags
                if (line.match(/^<\//)) {
                    indent = Math.max(0, indent - 1);
                }

                const indentedLine = '    '.repeat(indent) + line;

                // Increase indent for opening tags (not self-closing)
                if (line.match(/^<[^/!][^>]*[^/]>$/) && !line.match(/^<(br|hr|img|input|meta|link)/i)) {
                    indent++;
                }

                return indentedLine;
            }).join('\n');

            setLocalHtml(formatted);
            onHtmlChange(formatted);
        } catch (error) {
            console.error('Error formatting HTML:', error);
        }
    };

    // Default settings
    const settings = {
        width: canvasSettings?.width || '700px',
        backgroundColor: canvasSettings?.backgroundColor || '#f3f4f6',
        contentBackgroundColor: canvasSettings?.contentBackgroundColor || '#ffffff',
        contentPadding: canvasSettings?.contentPadding || '32px',
        contentMargin: canvasSettings?.contentMargin || '40px',
        contentBorderRadius: canvasSettings?.contentBorderRadius || '8px',
    };

    return (
        <div className="flex-1 flex flex-col overflow-hidden">
            {/* Top bar with "Editing code" and "Exit code editor" */}
            <div className="flex items-center justify-between px-4 py-2 bg-white border-b border-gray-200 flex-shrink-0">
                <span className="text-sm font-medium text-gray-700">Editing code</span>
                <button
                    onClick={onExitCodeEditor}
                    className="text-sm text-primary hover:text-primary/80 font-medium transition-colors"
                >
                    Exit code editor
                </button>
            </div>

            <div
                className="flex-1 overflow-auto"
                style={{ backgroundColor: settings.backgroundColor, padding: settings.contentMargin }}
            >
                <div className="mx-auto" style={{ maxWidth: settings.width }}>
                    <div
                        className="shadow-lg"
                        style={{
                            backgroundColor: settings.contentBackgroundColor,
                            borderRadius: settings.contentBorderRadius,
                        }}
                    >
                        {/* Toolbar */}
                        <div className="flex items-center justify-between px-4 py-2 border-b border-gray-200 bg-gray-50 rounded-t-lg">
                            <div className="flex items-center gap-2">
                                <iconify-icon icon="mdi:code-tags" width="18" height="18" class="text-gray-500"></iconify-icon>
                                <span className="text-sm font-medium text-gray-700">HTML Code Editor</span>
                            </div>
                            <div className="flex items-center gap-2">
                                <button
                                    onClick={formatHtml}
                                    className="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-600 bg-white border border-gray-200 rounded-md hover:bg-gray-50 transition-colors"
                                    title="Format HTML"
                                >
                                    <iconify-icon icon="mdi:auto-fix" width="14" height="14"></iconify-icon>
                                    Format
                                </button>
                            </div>
                        </div>

                        {/* Editor area with line numbers */}
                        <div className="flex min-h-[500px]">
                            {/* Line numbers */}
                            <div className="flex-shrink-0 bg-gray-50 border-r border-gray-200 text-right select-none">
                                <div className="py-4 px-2">
                                    {Array.from({ length: lineCount }, (_, i) => (
                                        <div
                                            key={i}
                                            className="text-xs text-gray-400 leading-6 pr-2"
                                            style={{ fontFamily: 'ui-monospace, monospace' }}
                                        >
                                            {i + 1}
                                        </div>
                                    ))}
                                </div>
                            </div>

                            {/* Code textarea */}
                            <div className="flex-1 relative">
                                <textarea
                                    ref={textareaRef}
                                    value={localHtml}
                                    onChange={handleChange}
                                    onBlur={handleBlur}
                                    onKeyDown={handleKeyDown}
                                    className="w-full h-full min-h-[500px] p-4 text-sm leading-6 text-gray-800 bg-transparent resize-none focus:outline-none"
                                    style={{
                                        fontFamily: 'ui-monospace, SFMono-Regular, "SF Mono", Menlo, Consolas, monospace',
                                        tabSize: 4,
                                    }}
                                    spellCheck={false}
                                    placeholder="<!-- Paste or write your HTML here -->"
                                />
                            </div>
                        </div>

                        {/* Footer info */}
                        <div className="flex items-center justify-between px-4 py-2 border-t border-gray-200 bg-gray-50 rounded-b-lg">
                            <span className="text-xs text-gray-500">
                                {localHtml.length} characters • {(localHtml || '').split('\n').length} lines
                            </span>
                            <span className="text-xs text-gray-400">
                                Press Tab for indent • Changes save on blur
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default CodeEditor;
