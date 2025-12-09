/**
 * TextFormatToolbar - Reusable formatting toolbar for text-based blocks
 *
 * Provides Bold, Italic, Underline, Alignment, and Link controls
 * for Heading and Text blocks.
 */

import { useState, useRef, useEffect } from 'react';

const TextFormatToolbar = ({ textareaRef, value, onChange, align, onAlignChange, showLink = true }) => {
    const [showLinkInput, setShowLinkInput] = useState(false);
    const [linkUrl, setLinkUrl] = useState('');
    const [selection, setSelection] = useState({ start: 0, end: 0 });
    const linkInputRef = useRef(null);

    // Focus link input when shown
    useEffect(() => {
        if (showLinkInput && linkInputRef.current) {
            linkInputRef.current.focus();
        }
    }, [showLinkInput]);

    // Get current selection from textarea
    const getSelection = () => {
        if (textareaRef?.current) {
            return {
                start: textareaRef.current.selectionStart,
                end: textareaRef.current.selectionEnd,
                text: value.substring(textareaRef.current.selectionStart, textareaRef.current.selectionEnd)
            };
        }
        return { start: 0, end: 0, text: '' };
    };

    // Wrap selected text with tags
    const wrapSelection = (openTag, closeTag) => {
        const sel = getSelection();
        if (sel.start === sel.end) return; // No selection

        const before = value.substring(0, sel.start);
        const selected = value.substring(sel.start, sel.end);
        const after = value.substring(sel.end);

        // Check if already wrapped - if so, unwrap
        if (selected.startsWith(openTag) && selected.endsWith(closeTag)) {
            const unwrapped = selected.slice(openTag.length, -closeTag.length);
            onChange(before + unwrapped + after);
        } else {
            onChange(before + openTag + selected + closeTag + after);
        }

        // Restore focus to textarea
        setTimeout(() => {
            if (textareaRef?.current) {
                textareaRef.current.focus();
            }
        }, 0);
    };

    // Handle formatting buttons
    const handleBold = () => wrapSelection('<strong>', '</strong>');
    const handleItalic = () => wrapSelection('<em>', '</em>');
    const handleUnderline = () => wrapSelection('<u>', '</u>');

    // Handle link
    const handleLinkClick = () => {
        const sel = getSelection();
        if (sel.start === sel.end) return; // No selection
        setSelection({ start: sel.start, end: sel.end });
        setShowLinkInput(true);
    };

    const handleLinkSubmit = () => {
        if (!linkUrl.trim()) {
            setShowLinkInput(false);
            return;
        }

        const before = value.substring(0, selection.start);
        const selected = value.substring(selection.start, selection.end);
        const after = value.substring(selection.end);

        onChange(before + `<a href="${linkUrl}">${selected}</a>` + after);
        setLinkUrl('');
        setShowLinkInput(false);

        // Restore focus to textarea
        setTimeout(() => {
            if (textareaRef?.current) {
                textareaRef.current.focus();
            }
        }, 0);
    };

    const handleLinkKeyDown = (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            handleLinkSubmit();
        } else if (e.key === 'Escape') {
            setShowLinkInput(false);
            setLinkUrl('');
        }
    };

    // Clear formatting
    const handleClearFormat = () => {
        const sel = getSelection();
        if (sel.start === sel.end) return;

        const before = value.substring(0, sel.start);
        const selected = value.substring(sel.start, sel.end);
        const after = value.substring(sel.end);

        // Remove HTML tags from selected text (loop until fully sanitized)
        let cleaned = selected;
        let prevCleaned;
        do {
            prevCleaned = cleaned;
            cleaned = cleaned.replace(/<[^>]*>/g, '');
        } while (cleaned !== prevCleaned);
        onChange(before + cleaned + after);

        setTimeout(() => {
            if (textareaRef?.current) {
                textareaRef.current.focus();
            }
        }, 0);
    };

    const buttonClass = "p-1.5 rounded hover:bg-gray-600 transition-colors text-gray-300 hover:text-white";
    const activeButtonClass = "p-1.5 rounded bg-gray-600 text-white";
    const dividerClass = "w-px h-5 bg-gray-600 mx-1";

    return (
        <div className="absolute -top-10 left-1/2 -translate-x-1/2 z-50">
            <div className="flex items-center gap-0.5 px-2 py-1.5 bg-gray-800 rounded-lg shadow-lg border border-gray-700">
                {/* Bold */}
                <button
                    type="button"
                    onClick={handleBold}
                    className={buttonClass}
                    title="Bold (Ctrl+B)"
                >
                    <iconify-icon icon="mdi:format-bold" width="18" height="18"></iconify-icon>
                </button>

                {/* Italic */}
                <button
                    type="button"
                    onClick={handleItalic}
                    className={buttonClass}
                    title="Italic (Ctrl+I)"
                >
                    <iconify-icon icon="mdi:format-italic" width="18" height="18"></iconify-icon>
                </button>

                {/* Underline */}
                <button
                    type="button"
                    onClick={handleUnderline}
                    className={buttonClass}
                    title="Underline (Ctrl+U)"
                >
                    <iconify-icon icon="mdi:format-underline" width="18" height="18"></iconify-icon>
                </button>

                <div className={dividerClass}></div>

                {/* Align Left */}
                <button
                    type="button"
                    onClick={() => onAlignChange('left')}
                    className={align === 'left' ? activeButtonClass : buttonClass}
                    title="Align Left"
                >
                    <iconify-icon icon="mdi:format-align-left" width="18" height="18"></iconify-icon>
                </button>

                {/* Align Center */}
                <button
                    type="button"
                    onClick={() => onAlignChange('center')}
                    className={align === 'center' ? activeButtonClass : buttonClass}
                    title="Align Center"
                >
                    <iconify-icon icon="mdi:format-align-center" width="18" height="18"></iconify-icon>
                </button>

                {/* Align Right */}
                <button
                    type="button"
                    onClick={() => onAlignChange('right')}
                    className={align === 'right' ? activeButtonClass : buttonClass}
                    title="Align Right"
                >
                    <iconify-icon icon="mdi:format-align-right" width="18" height="18"></iconify-icon>
                </button>

                {/* Align Justify */}
                <button
                    type="button"
                    onClick={() => onAlignChange('justify')}
                    className={align === 'justify' ? activeButtonClass : buttonClass}
                    title="Justify"
                >
                    <iconify-icon icon="mdi:format-align-justify" width="18" height="18"></iconify-icon>
                </button>

                {showLink && (
                    <>
                        <div className={dividerClass}></div>

                        {/* Link */}
                        <button
                            type="button"
                            onClick={handleLinkClick}
                            className={buttonClass}
                            title="Insert Link"
                        >
                            <iconify-icon icon="mdi:link-variant" width="18" height="18"></iconify-icon>
                        </button>
                    </>
                )}

                <div className={dividerClass}></div>

                {/* Clear Formatting */}
                <button
                    type="button"
                    onClick={handleClearFormat}
                    className={buttonClass}
                    title="Clear Formatting"
                >
                    <iconify-icon icon="mdi:format-clear" width="18" height="18"></iconify-icon>
                </button>
            </div>

            {/* Link Input Popup */}
            {showLinkInput && (
                <div className="absolute top-full left-1/2 -translate-x-1/2 mt-2 z-50">
                    <div className="flex items-center gap-2 px-3 py-2 bg-gray-800 rounded-lg shadow-lg border border-gray-700">
                        <iconify-icon icon="mdi:link-variant" width="16" height="16" class="text-gray-400"></iconify-icon>
                        <input
                            ref={linkInputRef}
                            type="url"
                            value={linkUrl}
                            onChange={(e) => setLinkUrl(e.target.value)}
                            onKeyDown={handleLinkKeyDown}
                            placeholder="Enter URL..."
                            className="w-48 px-2 py-1 text-sm bg-gray-700 border border-gray-600 rounded text-white placeholder-gray-400 focus:outline-none focus:border-primary"
                        />
                        <button
                            type="button"
                            onClick={handleLinkSubmit}
                            className="p-1 rounded bg-primary text-white hover:bg-primary/80 transition-colors"
                            title="Apply"
                        >
                            <iconify-icon icon="mdi:check" width="16" height="16"></iconify-icon>
                        </button>
                        <button
                            type="button"
                            onClick={() => { setShowLinkInput(false); setLinkUrl(''); }}
                            className="p-1 rounded text-gray-400 hover:text-white transition-colors"
                            title="Cancel"
                        >
                            <iconify-icon icon="mdi:close" width="16" height="16"></iconify-icon>
                        </button>
                    </div>
                </div>
            )}
        </div>
    );
};

export default TextFormatToolbar;
