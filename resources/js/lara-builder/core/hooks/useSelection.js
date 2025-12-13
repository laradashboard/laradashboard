/**
 * useSelection - Hook for Block Selection Management
 *
 * Provides selection state and actions for the builder.
 *
 * @example
 * const { selectedBlockId, selectBlock, isSelected } = useSelection();
 */

import { useCallback, useMemo } from 'react';
import { useBuilder } from '../BuilderContext';

/**
 * Hook for selection management
 */
export function useSelection() {
    const { state, actions, findBlockById } = useBuilder();

    const { selectedBlockId, blocks } = state;

    /**
     * Check if a specific block is selected
     */
    const isSelected = useCallback(
        (blockId) => {
            return selectedBlockId === blockId;
        },
        [selectedBlockId]
    );

    /**
     * Get the selected block data
     */
    const selectedBlock = useMemo(() => {
        if (!selectedBlockId) return null;
        return findBlockById(selectedBlockId);
    }, [selectedBlockId, findBlockById]);

    /**
     * Check if selection is a nested block
     */
    const isNestedSelection = useMemo(() => {
        if (!selectedBlockId) return false;

        // Check if selected block is at top level
        const isTopLevel = blocks.some((b) => b.id === selectedBlockId);
        return !isTopLevel;
    }, [selectedBlockId, blocks]);

    /**
     * Get parent block if selection is nested
     */
    const getParentBlock = useCallback(() => {
        if (!selectedBlockId || !isNestedSelection) return null;

        for (const block of blocks) {
            if (block.props?.children) {
                for (const column of block.props.children) {
                    if (column.some((b) => b.id === selectedBlockId)) {
                        return block;
                    }
                }
            }
        }

        return null;
    }, [selectedBlockId, isNestedSelection, blocks]);

    /**
     * Select next block
     */
    const selectNext = useCallback(() => {
        if (blocks.length === 0) return;

        if (!selectedBlockId) {
            actions.selectBlock(blocks[0].id);
            return;
        }

        const currentIndex = blocks.findIndex((b) => b.id === selectedBlockId);
        if (currentIndex > -1 && currentIndex < blocks.length - 1) {
            actions.selectBlock(blocks[currentIndex + 1].id);
        }
    }, [blocks, selectedBlockId, actions]);

    /**
     * Select previous block
     */
    const selectPrevious = useCallback(() => {
        if (blocks.length === 0) return;

        if (!selectedBlockId) {
            actions.selectBlock(blocks[blocks.length - 1].id);
            return;
        }

        const currentIndex = blocks.findIndex((b) => b.id === selectedBlockId);
        if (currentIndex > 0) {
            actions.selectBlock(blocks[currentIndex - 1].id);
        }
    }, [blocks, selectedBlockId, actions]);

    /**
     * Select first block
     */
    const selectFirst = useCallback(() => {
        if (blocks.length > 0) {
            actions.selectBlock(blocks[0].id);
        }
    }, [blocks, actions]);

    /**
     * Select last block
     */
    const selectLast = useCallback(() => {
        if (blocks.length > 0) {
            actions.selectBlock(blocks[blocks.length - 1].id);
        }
    }, [blocks, actions]);

    return {
        // State
        selectedBlockId,
        selectedBlock,
        hasSelection: selectedBlockId !== null,
        isNestedSelection,

        // Actions
        selectBlock: actions.selectBlock,
        deselectAll: actions.deselectAll,

        // Utilities
        isSelected,
        getParentBlock,
        selectNext,
        selectPrevious,
        selectFirst,
        selectLast,
    };
}

export default useSelection;
