/**
 * useBlocks - Hook for Block Management
 *
 * Provides convenient methods for managing blocks in the builder.
 *
 * @example
 * const { blocks, addBlock, updateBlock, deleteBlock } = useBlocks();
 */

import { useCallback, useMemo } from 'react';
import { useBuilder } from '../BuilderContext';
import { blockRegistry } from '../../registry/BlockRegistry';

/**
 * Hook for block management operations
 */
export function useBlocks() {
    const { state, actions, context } = useBuilder();

    const { blocks, selectedBlockId } = state;

    /**
     * Get the currently selected block
     */
    const selectedBlock = useMemo(() => {
        if (!selectedBlockId) return null;

        const findBlock = (blockList) => {
            for (const block of blockList) {
                if (block.id === selectedBlockId) return block;

                if (block.props?.children) {
                    for (const column of block.props.children) {
                        const found = findBlock(column);
                        if (found) return found;
                    }
                }
            }
            return null;
        };

        return findBlock(blocks);
    }, [blocks, selectedBlockId]);

    /**
     * Add a new block by type
     */
    const addBlockByType = useCallback(
        (type, index, props = {}) => {
            const instance = blockRegistry.createInstance(type, props);
            if (instance) {
                actions.addBlock(instance, index);
                return instance;
            }
            return null;
        },
        [actions]
    );

    /**
     * Add a block after the selected block
     */
    const addBlockAfterSelected = useCallback(
        (type, props = {}) => {
            const selectedIndex = blocks.findIndex((b) => b.id === selectedBlockId);
            const insertIndex = selectedIndex > -1 ? selectedIndex + 1 : blocks.length;

            return addBlockByType(type, insertIndex, props);
        },
        [blocks, selectedBlockId, addBlockByType]
    );

    /**
     * Get block index in array
     */
    const getBlockIndex = useCallback(
        (blockId) => {
            return blocks.findIndex((b) => b.id === blockId);
        },
        [blocks]
    );

    /**
     * Check if a block can be moved up
     */
    const canMoveUp = useCallback(
        (blockId) => {
            const index = getBlockIndex(blockId);
            return index > 0;
        },
        [getBlockIndex]
    );

    /**
     * Check if a block can be moved down
     */
    const canMoveDown = useCallback(
        (blockId) => {
            const index = getBlockIndex(blockId);
            return index > -1 && index < blocks.length - 1;
        },
        [blocks.length, getBlockIndex]
    );

    /**
     * Move block up
     */
    const moveBlockUp = useCallback(
        (blockId) => {
            const index = getBlockIndex(blockId);
            if (index > 0) {
                actions.moveBlock(index, index - 1);
            }
        },
        [getBlockIndex, actions]
    );

    /**
     * Move block down
     */
    const moveBlockDown = useCallback(
        (blockId) => {
            const index = getBlockIndex(blockId);
            if (index > -1 && index < blocks.length - 1) {
                actions.moveBlock(index, index + 1);
            }
        },
        [blocks.length, getBlockIndex, actions]
    );

    /**
     * Get blocks of a specific type
     */
    const getBlocksByType = useCallback(
        (type) => {
            return blocks.filter((b) => b.type === type);
        },
        [blocks]
    );

    /**
     * Check if builder has any blocks
     */
    const hasBlocks = blocks.length > 0;

    /**
     * Get total block count (including nested)
     */
    const totalBlockCount = useMemo(() => {
        let count = 0;

        const countBlocks = (blockList) => {
            for (const block of blockList) {
                count++;
                if (block.props?.children) {
                    for (const column of block.props.children) {
                        countBlocks(column);
                    }
                }
            }
        };

        countBlocks(blocks);
        return count;
    }, [blocks]);

    return {
        // State
        blocks,
        selectedBlockId,
        selectedBlock,
        hasBlocks,
        totalBlockCount,

        // Actions from context
        addBlock: actions.addBlock,
        updateBlock: actions.updateBlock,
        deleteBlock: actions.deleteBlock,
        moveBlock: actions.moveBlock,
        duplicateBlock: actions.duplicateBlock,
        selectBlock: actions.selectBlock,
        deselectAll: actions.deselectAll,

        // Extended actions
        addBlockByType,
        addBlockAfterSelected,

        // Movement
        canMoveUp,
        canMoveDown,
        moveBlockUp,
        moveBlockDown,

        // Utilities
        getBlockIndex,
        getBlocksByType,

        // Context
        context,
    };
}

export default useBlocks;
