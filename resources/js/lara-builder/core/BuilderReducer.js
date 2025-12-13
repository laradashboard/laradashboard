/**
 * BuilderReducer - State Management with Undo/Redo History
 *
 * Provides immutable state updates with full undo/redo support.
 * Uses a history-aware reducer pattern.
 */

import { LaraHooks } from '../hooks-system/LaraHooks';
import { BuilderHooks } from '../hooks-system/HookNames';

// Maximum history entries to keep
const MAX_HISTORY_SIZE = 50;

/**
 * Action Types
 */
export const ActionTypes = {
    // Block actions
    SET_BLOCKS: 'SET_BLOCKS',
    ADD_BLOCK: 'ADD_BLOCK',
    UPDATE_BLOCK: 'UPDATE_BLOCK',
    DELETE_BLOCK: 'DELETE_BLOCK',
    MOVE_BLOCK: 'MOVE_BLOCK',
    DUPLICATE_BLOCK: 'DUPLICATE_BLOCK',

    // Nested block actions (for columns)
    ADD_NESTED_BLOCK: 'ADD_NESTED_BLOCK',
    UPDATE_NESTED_BLOCK: 'UPDATE_NESTED_BLOCK',
    DELETE_NESTED_BLOCK: 'DELETE_NESTED_BLOCK',
    MOVE_NESTED_BLOCK: 'MOVE_NESTED_BLOCK',

    // Selection
    SELECT_BLOCK: 'SELECT_BLOCK',
    DESELECT_ALL: 'DESELECT_ALL',

    // Canvas settings
    UPDATE_CANVAS_SETTINGS: 'UPDATE_CANVAS_SETTINGS',

    // History
    UNDO: 'UNDO',
    REDO: 'REDO',
    CLEAR_HISTORY: 'CLEAR_HISTORY',

    // State management
    SET_DIRTY: 'SET_DIRTY',
    MARK_SAVED: 'MARK_SAVED',
    RESET_STATE: 'RESET_STATE',
    LOAD_STATE: 'LOAD_STATE',
};

/**
 * Initial state structure
 */
export const initialState = {
    // Current state
    blocks: [],
    selectedBlockId: null,
    canvasSettings: {},

    // History
    past: [],
    future: [],

    // Meta
    isDirty: false,
    context: 'email',
    config: {},
};

/**
 * Helper: Deep clone an object
 */
function deepClone(obj) {
    return JSON.parse(JSON.stringify(obj));
}

/**
 * Helper: Generate unique block ID
 */
function generateId() {
    return `block-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
}

/**
 * Helper: Create a default empty text block
 */
function createDefaultTextBlock() {
    return {
        id: generateId(),
        type: 'text',
        props: {
            content: '',
            align: 'left',
            color: '#666666',
            fontSize: '16px',
            lineHeight: '1.6',
        },
    };
}

/**
 * Helper: Find block by ID in a nested structure
 */
function findBlockById(blocks, id) {
    for (const block of blocks) {
        if (block.id === id) return block;

        // Check nested blocks (columns)
        if (block.props?.children) {
            for (const column of block.props.children) {
                const found = findBlockById(column, id);
                if (found) return found;
            }
        }
    }
    return null;
}

/**
 * Helper: Update block in tree by ID
 */
function updateBlockInTree(blocks, id, updates) {
    return blocks.map((block) => {
        if (block.id === id) {
            return {
                ...block,
                props: {
                    ...block.props,
                    ...updates,
                },
            };
        }

        // Check nested blocks
        if (block.props?.children) {
            return {
                ...block,
                props: {
                    ...block.props,
                    children: block.props.children.map((column) => updateBlockInTree(column, id, updates)),
                },
            };
        }

        return block;
    });
}

/**
 * Helper: Delete block from tree by ID
 */
function deleteBlockFromTree(blocks, id) {
    return blocks
        .filter((block) => block.id !== id)
        .map((block) => {
            if (block.props?.children) {
                return {
                    ...block,
                    props: {
                        ...block.props,
                        children: block.props.children.map((column) => deleteBlockFromTree(column, id)),
                    },
                };
            }
            return block;
        });
}

/**
 * Helper: Insert block at index
 */
function insertBlockAt(blocks, block, index) {
    const newBlocks = [...blocks];
    newBlocks.splice(index, 0, block);
    return newBlocks;
}

/**
 * Helper: Move block within array
 */
function moveBlock(blocks, fromIndex, toIndex) {
    const newBlocks = [...blocks];
    const [removed] = newBlocks.splice(fromIndex, 1);
    newBlocks.splice(toIndex, 0, removed);
    return newBlocks;
}

/**
 * Helper: Push to history with size limit
 */
function pushToHistory(history, state) {
    const newHistory = [
        ...history,
        {
            blocks: deepClone(state.blocks),
            canvasSettings: deepClone(state.canvasSettings),
            selectedBlockId: state.selectedBlockId,
        },
    ];

    // Trim history if exceeds max size
    if (newHistory.length > MAX_HISTORY_SIZE) {
        return newHistory.slice(-MAX_HISTORY_SIZE);
    }

    return newHistory;
}

/**
 * Helper: Check if action should create history entry
 */
function shouldRecordHistory(action) {
    // These actions modify content and should be recorded
    const recordableActions = [
        ActionTypes.SET_BLOCKS,
        ActionTypes.ADD_BLOCK,
        ActionTypes.UPDATE_BLOCK,
        ActionTypes.DELETE_BLOCK,
        ActionTypes.MOVE_BLOCK,
        ActionTypes.DUPLICATE_BLOCK,
        ActionTypes.ADD_NESTED_BLOCK,
        ActionTypes.UPDATE_NESTED_BLOCK,
        ActionTypes.DELETE_NESTED_BLOCK,
        ActionTypes.MOVE_NESTED_BLOCK,
        ActionTypes.UPDATE_CANVAS_SETTINGS,
    ];

    return recordableActions.includes(action.type);
}

/**
 * Main reducer function
 */
export function builderReducer(state, action) {
    // Apply filter to action before processing
    action = LaraHooks.applyFilters('builder.action.before', action, state);

    let newState = state;

    // Record history for content-modifying actions
    const shouldRecord = shouldRecordHistory(action);
    const pastWithCurrent = shouldRecord ? pushToHistory(state.past, state) : state.past;

    switch (action.type) {
        // ========================================
        // Block Actions
        // ========================================

        case ActionTypes.SET_BLOCKS:
            newState = {
                ...state,
                blocks: action.payload,
                past: pastWithCurrent,
                future: [],
                isDirty: true,
            };
            break;

        case ActionTypes.ADD_BLOCK: {
            const { block, index = state.blocks.length } = action.payload;
            const newBlock = {
                ...block,
                id: block.id || generateId(),
            };

            newState = {
                ...state,
                blocks: insertBlockAt(state.blocks, newBlock, index),
                selectedBlockId: newBlock.id,
                past: pastWithCurrent,
                future: [],
                isDirty: true,
            };

            LaraHooks.doAction(BuilderHooks.ACTION_BLOCK_ADDED, newBlock, state.context);
            break;
        }

        case ActionTypes.UPDATE_BLOCK: {
            const { id, props } = action.payload;

            newState = {
                ...state,
                blocks: updateBlockInTree(state.blocks, id, props),
                past: pastWithCurrent,
                future: [],
                isDirty: true,
            };

            LaraHooks.doAction(BuilderHooks.ACTION_BLOCK_UPDATED, id, props, state.context);
            break;
        }

        case ActionTypes.DELETE_BLOCK: {
            const { id } = action.payload;

            // Delete the block
            let newBlocks = deleteBlockFromTree(state.blocks, id);

            // Ensure at least one text block always exists
            let nextSelectedId = state.selectedBlockId;
            if (newBlocks.length === 0) {
                const defaultBlock = createDefaultTextBlock();
                newBlocks = [defaultBlock];
                nextSelectedId = defaultBlock.id;
            } else if (state.selectedBlockId === id) {
                // Find the index of the deleted block to select previous/next
                const deletedIndex = state.blocks.findIndex((b) => b.id === id);
                if (deletedIndex !== -1) {
                    // Select previous block if exists, otherwise next
                    if (deletedIndex > 0) {
                        nextSelectedId = state.blocks[deletedIndex - 1].id;
                    } else if (state.blocks.length > 1) {
                        nextSelectedId = state.blocks[deletedIndex + 1].id;
                    } else {
                        nextSelectedId = newBlocks[0]?.id || null;
                    }
                } else {
                    nextSelectedId = newBlocks[0]?.id || null;
                }
            }

            newState = {
                ...state,
                blocks: newBlocks,
                selectedBlockId: nextSelectedId,
                past: pastWithCurrent,
                future: [],
                isDirty: true,
            };

            LaraHooks.doAction(BuilderHooks.ACTION_BLOCK_REMOVED, id, state.context);
            break;
        }

        case ActionTypes.MOVE_BLOCK: {
            const { fromIndex, toIndex } = action.payload;

            newState = {
                ...state,
                blocks: moveBlock(state.blocks, fromIndex, toIndex),
                past: pastWithCurrent,
                future: [],
                isDirty: true,
            };

            LaraHooks.doAction(BuilderHooks.ACTION_BLOCK_MOVED, fromIndex, toIndex, state.context);
            break;
        }

        case ActionTypes.DUPLICATE_BLOCK: {
            const { id } = action.payload;
            const originalBlock = findBlockById(state.blocks, id);

            if (originalBlock) {
                const duplicatedBlock = deepClone(originalBlock);
                duplicatedBlock.id = generateId();

                // Also regenerate IDs for nested blocks
                if (duplicatedBlock.props?.children) {
                    duplicatedBlock.props.children = duplicatedBlock.props.children.map((column) =>
                        column.map((nestedBlock) => ({
                            ...nestedBlock,
                            id: generateId(),
                        }))
                    );
                }

                const originalIndex = state.blocks.findIndex((b) => b.id === id);

                newState = {
                    ...state,
                    blocks: insertBlockAt(state.blocks, duplicatedBlock, originalIndex + 1),
                    selectedBlockId: duplicatedBlock.id,
                    past: pastWithCurrent,
                    future: [],
                    isDirty: true,
                };

                LaraHooks.doAction(BuilderHooks.ACTION_BLOCK_DUPLICATED, duplicatedBlock, state.context);
            }
            break;
        }

        // ========================================
        // Nested Block Actions
        // ========================================

        case ActionTypes.ADD_NESTED_BLOCK: {
            const { parentId, columnIndex, block, index } = action.payload;
            const newBlock = {
                ...block,
                id: block.id || generateId(),
            };

            const newBlocks = state.blocks.map((b) => {
                if (b.id === parentId && b.props?.children) {
                    const newChildren = [...b.props.children];
                    const column = [...newChildren[columnIndex]];
                    column.splice(index ?? column.length, 0, newBlock);
                    newChildren[columnIndex] = column;

                    return {
                        ...b,
                        props: { ...b.props, children: newChildren },
                    };
                }
                return b;
            });

            newState = {
                ...state,
                blocks: newBlocks,
                selectedBlockId: newBlock.id,
                past: pastWithCurrent,
                future: [],
                isDirty: true,
            };

            LaraHooks.doAction(BuilderHooks.ACTION_BLOCK_ADDED, newBlock, state.context);
            break;
        }

        case ActionTypes.UPDATE_NESTED_BLOCK: {
            const { id, props } = action.payload;

            newState = {
                ...state,
                blocks: updateBlockInTree(state.blocks, id, props),
                past: pastWithCurrent,
                future: [],
                isDirty: true,
            };

            LaraHooks.doAction(BuilderHooks.ACTION_BLOCK_UPDATED, id, props, state.context);
            break;
        }

        case ActionTypes.DELETE_NESTED_BLOCK: {
            const { parentId, columnIndex, blockId } = action.payload;

            // Determine next selection after deletion for nested blocks
            let nextSelectedId = state.selectedBlockId;
            if (state.selectedBlockId === blockId) {
                // Find the parent block and the column
                const parentBlock = state.blocks.find((b) => b.id === parentId);
                if (parentBlock?.props?.children?.[columnIndex]) {
                    const column = parentBlock.props.children[columnIndex];
                    const deletedIndex = column.findIndex((nb) => nb.id === blockId);
                    if (deletedIndex !== -1) {
                        // Select previous block in column if exists, otherwise next, otherwise parent
                        if (deletedIndex > 0) {
                            nextSelectedId = column[deletedIndex - 1].id;
                        } else if (column.length > 1) {
                            nextSelectedId = column[deletedIndex + 1].id;
                        } else {
                            // No more blocks in column, select the parent columns block
                            nextSelectedId = parentId;
                        }
                    } else {
                        nextSelectedId = parentId;
                    }
                } else {
                    nextSelectedId = null;
                }
            }

            const newBlocks = state.blocks.map((b) => {
                if (b.id === parentId && b.props?.children) {
                    const newChildren = [...b.props.children];
                    newChildren[columnIndex] = newChildren[columnIndex].filter((nb) => nb.id !== blockId);

                    return {
                        ...b,
                        props: { ...b.props, children: newChildren },
                    };
                }
                return b;
            });

            newState = {
                ...state,
                blocks: newBlocks,
                selectedBlockId: nextSelectedId,
                past: pastWithCurrent,
                future: [],
                isDirty: true,
            };

            LaraHooks.doAction(BuilderHooks.ACTION_BLOCK_REMOVED, blockId, state.context);
            break;
        }

        case ActionTypes.MOVE_NESTED_BLOCK: {
            const { parentId, fromColumn, fromIndex, toColumn, toIndex } = action.payload;

            const newBlocks = state.blocks.map((b) => {
                if (b.id === parentId && b.props?.children) {
                    const newChildren = b.props.children.map((col) => [...col]);

                    // Remove from source
                    const [movedBlock] = newChildren[fromColumn].splice(fromIndex, 1);

                    // Insert at destination
                    newChildren[toColumn].splice(toIndex, 0, movedBlock);

                    return {
                        ...b,
                        props: { ...b.props, children: newChildren },
                    };
                }
                return b;
            });

            newState = {
                ...state,
                blocks: newBlocks,
                past: pastWithCurrent,
                future: [],
                isDirty: true,
            };
            break;
        }

        // ========================================
        // Selection
        // ========================================

        case ActionTypes.SELECT_BLOCK:
            newState = {
                ...state,
                selectedBlockId: action.payload,
            };

            LaraHooks.doAction(BuilderHooks.ACTION_BLOCK_SELECTED, action.payload, state.context);
            break;

        case ActionTypes.DESELECT_ALL:
            if (state.selectedBlockId) {
                LaraHooks.doAction(BuilderHooks.ACTION_BLOCK_DESELECTED, state.selectedBlockId, state.context);
            }
            newState = {
                ...state,
                selectedBlockId: null,
            };
            break;

        // ========================================
        // Canvas Settings
        // ========================================

        case ActionTypes.UPDATE_CANVAS_SETTINGS:
            newState = {
                ...state,
                canvasSettings: {
                    ...state.canvasSettings,
                    ...action.payload,
                },
                past: pastWithCurrent,
                future: [],
                isDirty: true,
            };

            LaraHooks.doAction(BuilderHooks.ACTION_CANVAS_SETTINGS_CHANGED, newState.canvasSettings, state.context);
            break;

        // ========================================
        // History (Undo/Redo)
        // ========================================

        case ActionTypes.UNDO: {
            if (state.past.length === 0) break;

            const previous = state.past[state.past.length - 1];
            const newPast = state.past.slice(0, -1);

            newState = {
                ...state,
                blocks: previous.blocks,
                canvasSettings: previous.canvasSettings,
                selectedBlockId: previous.selectedBlockId,
                past: newPast,
                future: [
                    {
                        blocks: deepClone(state.blocks),
                        canvasSettings: deepClone(state.canvasSettings),
                        selectedBlockId: state.selectedBlockId,
                    },
                    ...state.future,
                ],
                isDirty: true,
            };

            LaraHooks.doAction(BuilderHooks.ACTION_UNDO, newState, state.context);
            LaraHooks.doAction(BuilderHooks.ACTION_HISTORY_CHANGED, newState.past.length, newState.future.length);
            break;
        }

        case ActionTypes.REDO: {
            if (state.future.length === 0) break;

            const next = state.future[0];
            const newFuture = state.future.slice(1);

            newState = {
                ...state,
                blocks: next.blocks,
                canvasSettings: next.canvasSettings,
                selectedBlockId: next.selectedBlockId,
                past: [
                    ...state.past,
                    {
                        blocks: deepClone(state.blocks),
                        canvasSettings: deepClone(state.canvasSettings),
                        selectedBlockId: state.selectedBlockId,
                    },
                ],
                future: newFuture,
                isDirty: true,
            };

            LaraHooks.doAction(BuilderHooks.ACTION_REDO, newState, state.context);
            LaraHooks.doAction(BuilderHooks.ACTION_HISTORY_CHANGED, newState.past.length, newState.future.length);
            break;
        }

        case ActionTypes.CLEAR_HISTORY:
            newState = {
                ...state,
                past: [],
                future: [],
            };
            break;

        // ========================================
        // State Management
        // ========================================

        case ActionTypes.SET_DIRTY:
            newState = {
                ...state,
                isDirty: action.payload,
            };
            break;

        case ActionTypes.MARK_SAVED:
            newState = {
                ...state,
                isDirty: false,
            };
            break;

        case ActionTypes.RESET_STATE:
            newState = {
                ...initialState,
                context: state.context,
                config: state.config,
            };
            break;

        case ActionTypes.LOAD_STATE: {
            const { blocks, canvasSettings } = action.payload;

            newState = {
                ...state,
                blocks: blocks || [],
                canvasSettings: canvasSettings || {},
                past: [],
                future: [],
                isDirty: false,
                selectedBlockId: null,
            };

            // Apply filter for loaded state
            newState = LaraHooks.applyFilters(BuilderHooks.FILTER_STATE_AFTER_LOAD, newState, state.context);
            break;
        }

        default:
            break;
    }

    // Apply filter to state after processing
    newState = LaraHooks.applyFilters('builder.state.after', newState, action);

    return newState;
}

/**
 * Action Creators
 */
export const actions = {
    // Blocks
    setBlocks: (blocks) => ({ type: ActionTypes.SET_BLOCKS, payload: blocks }),
    addBlock: (block, index) => ({ type: ActionTypes.ADD_BLOCK, payload: { block, index } }),
    updateBlock: (id, props) => ({ type: ActionTypes.UPDATE_BLOCK, payload: { id, props } }),
    deleteBlock: (id) => ({ type: ActionTypes.DELETE_BLOCK, payload: { id } }),
    moveBlock: (fromIndex, toIndex) => ({ type: ActionTypes.MOVE_BLOCK, payload: { fromIndex, toIndex } }),
    duplicateBlock: (id) => ({ type: ActionTypes.DUPLICATE_BLOCK, payload: { id } }),

    // Nested blocks
    addNestedBlock: (parentId, columnIndex, block, index) => ({
        type: ActionTypes.ADD_NESTED_BLOCK,
        payload: { parentId, columnIndex, block, index },
    }),
    updateNestedBlock: (id, props) => ({ type: ActionTypes.UPDATE_NESTED_BLOCK, payload: { id, props } }),
    deleteNestedBlock: (parentId, columnIndex, blockId) => ({
        type: ActionTypes.DELETE_NESTED_BLOCK,
        payload: { parentId, columnIndex, blockId },
    }),
    moveNestedBlock: (parentId, fromColumn, fromIndex, toColumn, toIndex) => ({
        type: ActionTypes.MOVE_NESTED_BLOCK,
        payload: { parentId, fromColumn, fromIndex, toColumn, toIndex },
    }),

    // Selection
    selectBlock: (id) => ({ type: ActionTypes.SELECT_BLOCK, payload: id }),
    deselectAll: () => ({ type: ActionTypes.DESELECT_ALL }),

    // Canvas
    updateCanvasSettings: (settings) => ({ type: ActionTypes.UPDATE_CANVAS_SETTINGS, payload: settings }),

    // History
    undo: () => ({ type: ActionTypes.UNDO }),
    redo: () => ({ type: ActionTypes.REDO }),
    clearHistory: () => ({ type: ActionTypes.CLEAR_HISTORY }),

    // State
    setDirty: (isDirty) => ({ type: ActionTypes.SET_DIRTY, payload: isDirty }),
    markSaved: () => ({ type: ActionTypes.MARK_SAVED }),
    resetState: () => ({ type: ActionTypes.RESET_STATE }),
    loadState: (blocks, canvasSettings) => ({ type: ActionTypes.LOAD_STATE, payload: { blocks, canvasSettings } }),
};

export { createDefaultTextBlock };
export default builderReducer;
