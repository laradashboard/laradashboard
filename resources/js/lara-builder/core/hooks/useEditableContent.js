/**
 * useEditableContent - Shared hook for content-editable blocks
 *
 * Provides consistent content change detection to prevent false dirty state.
 * Used by text, heading, code, button, preformatted, and other editable blocks.
 *
 * @example
 * const { handleContentChange, isContentEmpty } = useEditableContent({
 *     editorRef,
 *     contentKey: 'content', // or 'text', 'code'
 *     useInnerHTML: true,    // false for textContent (code blocks)
 *     propsRef,
 *     onUpdateRef,
 *     lastContentRef,
 * });
 */

import { useCallback } from "react";

/**
 * Check if HTML content is effectively empty
 * Handles browser quirks like <br> tags in empty contenteditable
 */
export function isHtmlContentEmpty(content) {
    if (!content) return true;
    if (content === "<br>" || content === "<br/>") return true;
    return content.replace(/<br\s*\/?>/gi, "").trim() === "";
}

/**
 * Check if plain text content is effectively empty
 */
export function isTextContentEmpty(content) {
    if (!content) return true;
    return content.trim() === "";
}

/**
 * Check if content has meaningfully changed
 * Treats all empty variants as equal to prevent false dirty state
 */
export function hasContentChanged(oldContent, newContent, isEmpty) {
    const oldIsEmpty = isEmpty(oldContent);
    const newIsEmpty = isEmpty(newContent);

    // If both empty or both non-empty with same content, no change
    if (oldIsEmpty && newIsEmpty) return false;
    if (oldIsEmpty !== newIsEmpty) return true;

    // Both non-empty, compare actual content
    return newContent !== oldContent;
}

/**
 * Custom hook for editable content blocks
 *
 * @param {Object} options
 * @param {React.RefObject} options.editorRef - Ref to the contenteditable element
 * @param {string} options.contentKey - The prop key for content ('content', 'text', 'code')
 * @param {boolean} options.useInnerHTML - Whether to use innerHTML (true) or textContent (false)
 * @param {React.RefObject} options.propsRef - Ref to current props
 * @param {React.RefObject} options.onUpdateRef - Ref to onUpdate callback
 * @param {React.RefObject} options.lastContentRef - Ref to track last known content
 * @returns {Object} Hook utilities
 */
export function useEditableContent({
    editorRef,
    contentKey = "content",
    useInnerHTML = true,
    propsRef,
    onUpdateRef,
    lastContentRef,
}) {
    // Select appropriate empty check based on content type
    const isEmpty = useCallback(
        (content) => {
            return useInnerHTML
                ? isHtmlContentEmpty(content)
                : isTextContentEmpty(content);
        },
        [useInnerHTML]
    );

    /**
     * Get current content from the editor
     */
    const getContent = useCallback(() => {
        if (!editorRef.current) return "";
        return useInnerHTML
            ? editorRef.current.innerHTML
            : editorRef.current.textContent;
    }, [editorRef, useInnerHTML]);

    /**
     * Handle content change - only triggers update if content actually changed
     * Call this on input and blur events
     *
     * @returns {boolean} Whether content was updated
     */
    const handleContentChange = useCallback(() => {
        if (!editorRef.current) return false;

        const newContent = getContent();
        const oldContent = lastContentRef.current;

        if (hasContentChanged(oldContent, newContent, isEmpty)) {
            lastContentRef.current = newContent;
            onUpdateRef.current({
                ...propsRef.current,
                [contentKey]: newContent,
            });
            return true;
        }

        return false;
    }, [editorRef, getContent, lastContentRef, isEmpty, onUpdateRef, propsRef, contentKey]);

    /**
     * Check if current editor content is empty
     */
    const isCurrentContentEmpty = useCallback(() => {
        return isEmpty(getContent());
    }, [isEmpty, getContent]);

    return {
        handleContentChange,
        getContent,
        isEmpty,
        isCurrentContentEmpty,
        isHtmlContentEmpty,
        isTextContentEmpty,
        hasContentChanged: (oldContent, newContent) =>
            hasContentChanged(oldContent, newContent, isEmpty),
    };
}

export default useEditableContent;
