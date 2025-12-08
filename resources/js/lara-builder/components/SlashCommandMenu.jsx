/**
 * SlashCommandMenu - Dropdown menu for block selection via "/" command
 *
 * Shows a filterable list of available blocks when user types "/" in an empty block.
 */

import { useState, useEffect, useRef, useCallback } from "react";
import { __ } from "@lara-builder/i18n";
import { blockRegistry } from "../registry/BlockRegistry";

/**
 * SlashCommandMenu Component
 *
 * @param {Object} props
 * @param {boolean} props.isOpen - Whether the menu is open
 * @param {string} props.searchQuery - Current search query (text after "/")
 * @param {Function} props.onSelect - Callback when a block is selected
 * @param {Function} props.onClose - Callback to close the menu
 * @param {Object} props.position - Position {top, left} for the menu
 * @param {string} props.context - Builder context (email, page, etc.)
 */
export function SlashCommandMenu({
    isOpen,
    searchQuery = "",
    onSelect,
    onClose,
    position = { top: 0, left: 0 },
    context = "post",
}) {
    const [selectedIndex, setSelectedIndex] = useState(0);
    const menuRef = useRef(null);
    const itemRefs = useRef([]);

    // Get available blocks for the current context
    const allBlocks = blockRegistry.getBlocksForContext(context);

    // Filter blocks based on search query
    const filteredBlocks = allBlocks.filter((block) => {
        if (!searchQuery) return true;
        const query = searchQuery.toLowerCase();
        return (
            block.label.toLowerCase().includes(query) ||
            block.type.toLowerCase().includes(query) ||
            (block.keywords &&
                block.keywords.some((k) => k.toLowerCase().includes(query)))
        );
    });

    // Reset selected index when filtered blocks change
    useEffect(() => {
        setSelectedIndex(0);
    }, [searchQuery]);

    // Scroll selected item into view
    useEffect(() => {
        if (itemRefs.current[selectedIndex]) {
            itemRefs.current[selectedIndex].scrollIntoView({
                block: "nearest",
            });
        }
    }, [selectedIndex]);

    // Handle keyboard navigation
    const handleKeyDown = useCallback(
        (e) => {
            if (!isOpen) return;

            switch (e.key) {
                case "ArrowDown":
                    e.preventDefault();
                    setSelectedIndex((prev) =>
                        prev < filteredBlocks.length - 1 ? prev + 1 : prev
                    );
                    break;
                case "ArrowUp":
                    e.preventDefault();
                    setSelectedIndex((prev) => (prev > 0 ? prev - 1 : prev));
                    break;
                case "Enter":
                    e.preventDefault();
                    if (filteredBlocks[selectedIndex]) {
                        onSelect(filteredBlocks[selectedIndex].type);
                    }
                    break;
                case "Escape":
                    e.preventDefault();
                    onClose();
                    break;
            }
        },
        [isOpen, filteredBlocks, selectedIndex, onSelect, onClose]
    );

    // Attach keyboard listener
    useEffect(() => {
        if (isOpen) {
            document.addEventListener("keydown", handleKeyDown, true);
            return () =>
                document.removeEventListener("keydown", handleKeyDown, true);
        }
    }, [isOpen, handleKeyDown]);

    // Close menu when clicking outside
    useEffect(() => {
        if (!isOpen) return;

        const handleClickOutside = (e) => {
            if (menuRef.current && !menuRef.current.contains(e.target)) {
                onClose();
            }
        };

        document.addEventListener("mousedown", handleClickOutside);
        return () =>
            document.removeEventListener("mousedown", handleClickOutside);
    }, [isOpen, onClose]);

    if (!isOpen) return null;

    // Group blocks by category
    const blocksByCategory = filteredBlocks.reduce((acc, block) => {
        const category = block.category || "Other";
        if (!acc[category]) acc[category] = [];
        acc[category].push(block);
        return acc;
    }, {});

    let itemIndex = 0;

    return (
        <div
            ref={menuRef}
            className="absolute z-50 bg-white rounded-lg shadow-lg border border-gray-200 w-72 max-h-80 overflow-y-auto"
            style={{
                top: position.top,
                left: position.left,
            }}
        >
            {filteredBlocks.length === 0 ? (
                <div className="p-4 text-center text-gray-500 text-sm">
                    {__("No blocks found")}
                </div>
            ) : (
                <div className="py-2">
                    {Object.entries(blocksByCategory).map(
                        ([category, blocks]) => (
                            <div key={category}>
                                <div className="px-3 py-1.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    {__(category)}
                                </div>
                                {blocks.map((block) => {
                                    const currentIndex = itemIndex++;
                                    const isSelected =
                                        currentIndex === selectedIndex;
                                    return (
                                        <button
                                            key={block.type}
                                            ref={(el) =>
                                                (itemRefs.current[
                                                    currentIndex
                                                ] = el)
                                            }
                                            className={`w-full px-3 py-2 flex items-center gap-3 text-left transition-colors ${
                                                isSelected
                                                    ? "bg-primary/10 text-primary"
                                                    : "hover:bg-gray-50 text-gray-700"
                                            }`}
                                            onClick={() => onSelect(block.type)}
                                            onMouseEnter={() =>
                                                setSelectedIndex(currentIndex)
                                            }
                                        >
                                            {block.icon && (
                                                <iconify-icon
                                                    icon={block.icon}
                                                    width="20"
                                                    height="20"
                                                    className={
                                                        isSelected
                                                            ? "text-primary"
                                                            : "text-gray-400"
                                                    }
                                                />
                                            )}
                                            <div>
                                                <div className="font-medium text-sm">
                                                    {__(block.label)}
                                                </div>
                                                {block.description && (
                                                    <div className="text-xs text-gray-500 truncate max-w-[200px]">
                                                        {__(block.description)}
                                                    </div>
                                                )}
                                            </div>
                                        </button>
                                    );
                                })}
                            </div>
                        )
                    )}
                </div>
            )}
        </div>
    );
}

export default SlashCommandMenu;
