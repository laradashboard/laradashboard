/**
 * useBlockOperations - Hook for block manipulation operations
 *
 * Provides handlers for updating, deleting, moving, and duplicating blocks.
 */

import { useCallback } from "react";
import { blockRegistry } from "../../registry/BlockRegistry";
import { pendingCursors } from "../pendingCursors";

/**
 * @param {Object} options
 * @param {Array} options.blocks - Current blocks array
 * @param {Object} options.actions - Builder actions from context
 * @param {Function} options.addBlockAfterSelected - Add block after selected
 * @returns {Object} Block operation handlers
 */
export function useBlockOperations({ blocks, actions, addBlockAfterSelected }) {
    // Recursive search helper for finding blocks at any nesting depth
    const searchBlocksRecursive = useCallback((items, id) => {
        if (!Array.isArray(items)) return null;

        for (const item of items) {
            if (Array.isArray(item)) {
                const found = searchBlocksRecursive(item, id);
                if (found) return found;
            } else if (item && typeof item === 'object' && item.type) {
                if (item.id === id) return item;
                if (Array.isArray(item.props?.children)) {
                    const found = searchBlocksRecursive(item.props.children, id);
                    if (found) return found;
                }
            }
        }
        return null;
    }, []);

    // Find block helper (including deeply nested blocks)
    const findBlock = useCallback(
        (blockId) => {
            return searchBlocksRecursive(blocks, blockId);
        },
        [blocks, searchBlocksRecursive]
    );

    // Recursive location search helper
    const searchLocationRecursive = useCallback((items, id, parentId) => {
        if (!Array.isArray(items)) return null;

        for (let i = 0; i < items.length; i++) {
            const item = items[i];
            if (Array.isArray(item)) {
                // Item is a column array
                for (let blockIdx = 0; blockIdx < item.length; blockIdx++) {
                    const block = item[blockIdx];
                    if (block.id === id) {
                        return {
                            isNested: true,
                            parentBlockId: parentId,
                            columnIndex: i,
                            blockIndex: blockIdx,
                        };
                    }
                    // Search deeper
                    if (Array.isArray(block.props?.children)) {
                        const found = searchLocationRecursive(block.props.children, id, block.id);
                        if (found) return found;
                    }
                }
            } else if (item && typeof item === 'object' && item.type) {
                if (item.id === id) {
                    return {
                        isNested: parentId !== null,
                        parentBlockId: parentId,
                        columnIndex: null,
                        blockIndex: i,
                    };
                }
                if (Array.isArray(item.props?.children)) {
                    const found = searchLocationRecursive(item.props.children, id, item.id);
                    if (found) return found;
                }
            }
        }
        return null;
    }, []);

    // Find block location (returns info about nested blocks at any depth)
    const findBlockLocation = useCallback(
        (blockId) => {
            return searchLocationRecursive(blocks, blockId, null);
        },
        [blocks]
    );

    // Update block handler
    const handleUpdateBlock = useCallback(
        (blockId, newProps) => {
            actions.updateBlock(blockId, newProps);
        },
        [actions]
    );

    // Delete handlers
    const handleDeleteBlock = useCallback(
        (blockId) => {
            actions.deleteBlock(blockId);
        },
        [actions]
    );

    const handleDeleteNestedBlock = useCallback(
        (blockId, pId, colIdx) => {
            actions.deleteNestedBlock(pId, colIdx, blockId);
        },
        [actions]
    );

    // Move handlers
    const handleMoveBlock = useCallback(
        (blockId, direction) => {
            const index = blocks.findIndex((b) => b.id === blockId);
            if (index === -1) return;

            const newIndex = direction === "up" ? index - 1 : index + 1;
            if (newIndex < 0 || newIndex >= blocks.length) return;

            actions.moveBlock(index, newIndex);
        },
        [blocks, actions]
    );

    const handleMoveNestedBlock = useCallback(
        (blockId, pId, colIdx, direction) => {
            // Search recursively — parent may be deeply nested
            const block = findBlock(pId);
            if (!block?.props?.children?.[colIdx]) return;

            const column = block.props.children[colIdx];
            const index = column.findIndex((b) => b.id === blockId);
            if (index === -1) return;

            const newIndex = direction === "up" ? index - 1 : index + 1;
            if (newIndex < 0 || newIndex >= column.length) return;

            actions.moveNestedBlock(pId, colIdx, index, colIdx, newIndex);
        },
        [findBlock, actions]
    );

    // Duplicate handlers
    const handleDuplicateBlock = useCallback(
        (blockId) => {
            actions.duplicateBlock(blockId);
        },
        [actions]
    );

    const handleDuplicateNestedBlock = useCallback(
        (blockId, pId, colIdx) => {
            // Search recursively — parent may be deeply nested (e.g. columns inside section)
            const block = findBlock(pId);
            if (!block?.props?.children?.[colIdx]) return;

            const column = block.props.children[colIdx];
            const blockToDuplicate = column.find((b) => b.id === blockId);
            if (!blockToDuplicate) return;

            const duplicatedBlock = blockRegistry.createInstance(
                blockToDuplicate.type,
                blockToDuplicate.props
            );
            if (duplicatedBlock) {
                const index = column.findIndex((b) => b.id === blockId);
                actions.addNestedBlock(pId, colIdx, duplicatedBlock, index + 1);
            }
        },
        [findBlock, actions]
    );

    // Add block handler (for click-to-add)
    const handleAddBlock = useCallback(
        (blockType) => {
            addBlockAfterSelected(blockType);
        },
        [addBlockAfterSelected]
    );

    // Insert a new block after a specific block (for Enter key in text/heading)
    const handleInsertBlockAfter = useCallback(
        (afterBlockId, blockType, initialProps = {}) => {
            const newBlock = blockRegistry.createInstance(blockType, initialProps);
            if (!newBlock) return;

            // Check if it's a top-level block
            const topLevelIndex = blocks.findIndex((b) => b.id === afterBlockId);
            if (topLevelIndex !== -1) {
                // If new block has initial content, cursor should start at beginning
                if (initialProps.content) {
                    pendingCursors.set(newBlock.id, "start");
                }
                actions.addBlock(newBlock, topLevelIndex + 1);
                actions.selectBlock(newBlock.id);
                return;
            }

            // Check nested blocks in columns
            for (const block of blocks) {
                if (block.type === "columns" && block.props.children) {
                    for (let colIdx = 0; colIdx < block.props.children.length; colIdx++) {
                        const column = block.props.children[colIdx];
                        const nestedIndex = column.findIndex((b) => b.id === afterBlockId);
                        if (nestedIndex !== -1) {
                            if (initialProps.content) {
                                pendingCursors.set(newBlock.id, "start");
                            }
                            actions.addNestedBlock(
                                block.id,
                                colIdx,
                                newBlock,
                                nestedIndex + 1
                            );
                            actions.selectBlock(newBlock.id);
                            return;
                        }
                    }
                }
            }
        },
        [blocks, actions]
    );

    // Replace a block with a new block of different type (for slash commands)
    const handleReplaceBlock = useCallback(
        (blockId, newBlockType) => {
            const newBlock = blockRegistry.createInstance(newBlockType);
            if (!newBlock) return;

            // Find the block's position
            const blockIndex = blocks.findIndex((b) => b.id === blockId);
            if (blockIndex !== -1) {
                // Delete the old block and insert new one at same position
                actions.deleteBlock(blockId);
                // Use setTimeout to ensure delete completes first
                setTimeout(() => {
                    actions.addBlock(newBlock, blockIndex);
                    actions.selectBlock(newBlock.id);
                }, 0);
                return;
            }

            // Check nested blocks in columns
            for (const block of blocks) {
                if (block.type === "columns" && block.props.children) {
                    for (let colIdx = 0; colIdx < block.props.children.length; colIdx++) {
                        const column = block.props.children[colIdx];
                        const nestedIndex = column.findIndex((b) => b.id === blockId);
                        if (nestedIndex !== -1) {
                            // Delete nested block and insert new one
                            actions.deleteNestedBlock(block.id, colIdx, blockId);
                            setTimeout(() => {
                                actions.addNestedBlock(block.id, colIdx, newBlock, nestedIndex);
                                actions.selectBlock(newBlock.id);
                            }, 0);
                            return;
                        }
                    }
                }
            }
        },
        [blocks, actions]
    );

    // Merge current block's content onto the previous block (Backspace-at-start)
    // Supports merging into text blocks (content key) and heading blocks (text key)
    const handleMergeBlockWithPrevious = useCallback(
        (blockId, contentToAppend) => {
            const index = blocks.findIndex((b) => b.id === blockId);
            if (index <= 0) return; // no previous block

            const prevBlock = blocks[index - 1];

            // Determine the content key based on block type
            let contentKey;
            if (prevBlock.type === "text") {
                contentKey = "content";
            } else if (prevBlock.type === "heading") {
                contentKey = "text";
            } else {
                return; // can only merge into text or heading blocks
            }

            const prevContent = prevBlock.props[contentKey] || "";
            const mergedContent = prevContent + contentToAppend;

            // Compute where the cursor should land: at the junction between
            // the original prev content and the appended content (text char offset)
            const tempDiv = document.createElement("div");
            tempDiv.innerHTML = prevContent;
            const junctionOffset = tempDiv.textContent.length;
            pendingCursors.set(prevBlock.id, junctionOffset);

            // Update previous block with merged content, then delete current block
            actions.updateBlock(prevBlock.id, {
                ...prevBlock.props,
                [contentKey]: mergedContent,
            });
            actions.deleteBlock(blockId);
            actions.selectBlock(prevBlock.id);
        },
        [blocks, actions]
    );

    return {
        // Helpers
        findBlock,
        findBlockLocation,
        // Handlers
        handleUpdateBlock,
        handleDeleteBlock,
        handleDeleteNestedBlock,
        handleMoveBlock,
        handleMoveNestedBlock,
        handleDuplicateBlock,
        handleDuplicateNestedBlock,
        handleAddBlock,
        handleInsertBlockAfter,
        handleReplaceBlock,
        handleMergeBlockWithPrevious,
    };
}

export default useBlockOperations;
