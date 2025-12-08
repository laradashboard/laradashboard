/**
 * useSlashCommand - Hook for "/" command functionality in text blocks
 *
 * Detects when user types "/" in an empty block and manages the slash command menu.
 */

import { useState, useCallback, useRef } from "react";

/**
 * @param {Object} options
 * @param {React.RefObject} options.editorRef - Ref to the contenteditable element
 * @param {Function} options.onSelectBlock - Callback when a block type is selected
 * @param {Function} options.onDeleteCurrentBlock - Callback to delete current block
 * @param {Function} options.getCurrentContent - Function to get current content
 * @returns {Object} Slash command state and handlers
 */
export function useSlashCommand({
    editorRef,
    onSelectBlock,
    onDeleteCurrentBlock,
    getCurrentContent,
}) {
    const [isMenuOpen, setIsMenuOpen] = useState(false);
    const [searchQuery, setSearchQuery] = useState("");
    const [menuPosition, setMenuPosition] = useState({ top: 0, left: 0 });
    const slashIndexRef = useRef(-1);

    // Calculate menu position based on caret
    const calculateMenuPosition = useCallback(() => {
        if (!editorRef.current) return { top: 0, left: 0 };

        const selection = window.getSelection();
        if (!selection.rangeCount) {
            const rect = editorRef.current.getBoundingClientRect();
            return { top: rect.height + 4, left: 0 };
        }

        const range = selection.getRangeAt(0);
        const rect = range.getBoundingClientRect();
        const editorRect = editorRef.current.getBoundingClientRect();

        return {
            top: rect.bottom - editorRect.top + 4,
            left: rect.left - editorRect.left,
        };
    }, [editorRef]);

    // Handle input to detect "/" command
    const handleSlashInput = useCallback(
        () => {
            const content = getCurrentContent();

            // Check if content starts with "/" (slash command)
            if (content.startsWith("/")) {
                const query = content.slice(1); // Remove the "/" prefix
                setSearchQuery(query);
                setMenuPosition(calculateMenuPosition());
                setIsMenuOpen(true);
                slashIndexRef.current = 0;
            } else {
                // Close menu if content doesn't start with "/"
                if (isMenuOpen) {
                    setIsMenuOpen(false);
                    setSearchQuery("");
                    slashIndexRef.current = -1;
                }
            }
        },
        [getCurrentContent, calculateMenuPosition, isMenuOpen]
    );

    // Handle block selection from menu
    const handleSelectBlock = useCallback(
        (blockType) => {
            setIsMenuOpen(false);
            setSearchQuery("");
            slashIndexRef.current = -1;

            // Clear the "/" content and delete current block, then insert new one
            if (onDeleteCurrentBlock && onSelectBlock) {
                // First delete the current block (which has only "/" content)
                onDeleteCurrentBlock();
                // The reducer will auto-create a text block if needed,
                // but we want to insert the selected block type instead
                // So we use onSelectBlock which should handle the insertion
                setTimeout(() => {
                    onSelectBlock(blockType);
                }, 0);
            }
        },
        [onSelectBlock, onDeleteCurrentBlock]
    );

    // Close menu
    const closeMenu = useCallback(() => {
        setIsMenuOpen(false);
        setSearchQuery("");
        slashIndexRef.current = -1;
    }, []);

    // Handle keydown for menu navigation prevention
    const handleKeyDownForMenu = useCallback(
        (e) => {
            if (!isMenuOpen) return false;

            // These keys are handled by the menu component
            if (["ArrowDown", "ArrowUp", "Enter", "Escape"].includes(e.key)) {
                // Let the menu handle these
                return true;
            }

            // Tab also closes menu
            if (e.key === "Tab") {
                closeMenu();
                return false;
            }

            return false;
        },
        [isMenuOpen, closeMenu]
    );

    return {
        isMenuOpen,
        searchQuery,
        menuPosition,
        handleSlashInput,
        handleSelectBlock,
        closeMenu,
        handleKeyDownForMenu,
    };
}

export default useSlashCommand;
