/**
 * BuilderContext - React Context for LaraBuilder State Management
 *
 * Provides centralized state management for the builder using React Context
 * and the builderReducer with history support.
 *
 * @example
 * // Wrap your component with BuilderProvider
 * <BuilderProvider context="post" initialData={data}>
 *   <LaraBuilder />
 * </BuilderProvider>
 *
 * // Access state in child components
 * const { state, dispatch, actions } = useBuilder();
 */

import React, { createContext, useContext, useReducer, useCallback, useMemo, useEffect } from 'react';
import { builderReducer, initialState, actions as actionCreators, createDefaultTextBlock } from './BuilderReducer';
import { LaraHooks } from '../hooks-system/LaraHooks';
import { BuilderHooks } from '../hooks-system/HookNames';
import { OutputAdapterRegistry } from '../registry/OutputAdapterRegistry';

/**
 * @typedef {Object} BuilderContextValue
 * @property {Object} state - Current builder state
 * @property {Function} dispatch - Dispatch function for actions
 * @property {Object} actions - Bound action creators
 * @property {boolean} canUndo - Whether undo is available
 * @property {boolean} canRedo - Whether redo is available
 * @property {Function} undo - Undo action
 * @property {Function} redo - Redo action
 * @property {Function} getHtml - Generate HTML from current state
 * @property {Function} getSelectedBlock - Get currently selected block
 */

// Create context
const BuilderContext = createContext(null);

/**
 * BuilderProvider - Context provider component
 *
 * @param {Object} props
 * @param {React.ReactNode} props.children - Child components
 * @param {string} props.context - Builder context (email, page, campaign)
 * @param {Object} props.config - Context-specific configuration
 * @param {Object} props.initialData - Initial blocks and settings
 * @param {Function} props.onStateChange - Callback when state changes
 */
export function BuilderProvider({
    children,
    context = 'post',
    config = {},
    initialData = null,
    onStateChange = null,
}) {
    // Get default canvas settings for context
    const defaultSettings = useMemo(() => {
        return OutputAdapterRegistry.getDefaultSettings(context);
    }, [context]);

    // Prepare initial state
    const preparedInitialState = useMemo(() => {
        // Use provided blocks, or create a default empty text block
        const initialBlocks = initialData?.blocks?.length > 0
            ? initialData.blocks
            : [createDefaultTextBlock()];

        // Select the first block by default for better UX
        const initialSelectedId = initialBlocks[0]?.id || null;

        let state = {
            ...initialState,
            context,
            config,
            canvasSettings: {
                ...defaultSettings,
                ...initialData?.canvasSettings,
            },
            blocks: initialBlocks,
            selectedBlockId: initialSelectedId,
        };

        // Apply filter for initial state customization
        state = LaraHooks.applyFilters(BuilderHooks.FILTER_INITIAL_STATE, state, context);

        return state;
    }, [context, config, initialData, defaultSettings]);

    // Initialize reducer
    const [state, dispatch] = useReducer(builderReducer, preparedInitialState);

    // Notify on state changes
    useEffect(() => {
        if (onStateChange) {
            onStateChange(state);
        }
    }, [state, onStateChange]);

    // Fire init action on mount
    useEffect(() => {
        LaraHooks.doAction(BuilderHooks.ACTION_INIT, state, context);

        return () => {
            LaraHooks.doAction(BuilderHooks.ACTION_DESTROY, state, context);
        };
    }, [context]);

    // ========================================
    // Bound action creators
    // ========================================

    const boundActions = useMemo(
        () => ({
            // Blocks
            setBlocks: (blocks) => dispatch(actionCreators.setBlocks(blocks)),
            addBlock: (block, index) => dispatch(actionCreators.addBlock(block, index)),
            updateBlock: (id, props) => dispatch(actionCreators.updateBlock(id, props)),
            deleteBlock: (id) => dispatch(actionCreators.deleteBlock(id)),
            moveBlock: (fromIndex, toIndex) => dispatch(actionCreators.moveBlock(fromIndex, toIndex)),
            duplicateBlock: (id) => dispatch(actionCreators.duplicateBlock(id)),

            // Nested blocks
            addNestedBlock: (parentId, columnIndex, block, index) =>
                dispatch(actionCreators.addNestedBlock(parentId, columnIndex, block, index)),
            updateNestedBlock: (id, props) => dispatch(actionCreators.updateNestedBlock(id, props)),
            deleteNestedBlock: (parentId, columnIndex, blockId) =>
                dispatch(actionCreators.deleteNestedBlock(parentId, columnIndex, blockId)),
            moveNestedBlock: (parentId, fromColumn, fromIndex, toColumn, toIndex) =>
                dispatch(actionCreators.moveNestedBlock(parentId, fromColumn, fromIndex, toColumn, toIndex)),

            // Selection
            selectBlock: (id) => dispatch(actionCreators.selectBlock(id)),
            deselectAll: () => dispatch(actionCreators.deselectAll()),

            // Canvas
            updateCanvasSettings: (settings) => dispatch(actionCreators.updateCanvasSettings(settings)),

            // History
            undo: () => dispatch(actionCreators.undo()),
            redo: () => dispatch(actionCreators.redo()),
            clearHistory: () => dispatch(actionCreators.clearHistory()),

            // State
            setDirty: (isDirty) => dispatch(actionCreators.setDirty(isDirty)),
            markSaved: () => dispatch(actionCreators.markSaved()),
            resetState: () => dispatch(actionCreators.resetState()),
            loadState: (blocks, canvasSettings) => dispatch(actionCreators.loadState(blocks, canvasSettings)),
        }),
        [dispatch]
    );

    // ========================================
    // History helpers
    // ========================================

    const canUndo = state.past.length > 0;
    const canRedo = state.future.length > 0;

    const undo = useCallback(() => {
        if (canUndo) {
            dispatch(actionCreators.undo());
        }
    }, [canUndo]);

    const redo = useCallback(() => {
        if (canRedo) {
            dispatch(actionCreators.redo());
        }
    }, [canRedo]);

    // ========================================
    // Utility functions
    // ========================================

    /**
     * Generate HTML from current state
     */
    const getHtml = useCallback(() => {
        return OutputAdapterRegistry.generateHtml(context, state.blocks, state.canvasSettings);
    }, [context, state.blocks, state.canvasSettings]);

    /**
     * Get the currently selected block
     */
    const getSelectedBlock = useCallback(() => {
        if (!state.selectedBlockId) return null;

        // Find in top-level blocks
        for (const block of state.blocks) {
            if (block.id === state.selectedBlockId) return block;

            // Check nested blocks
            if (block.props?.children) {
                for (const column of block.props.children) {
                    const nested = column.find((b) => b.id === state.selectedBlockId);
                    if (nested) return nested;
                }
            }
        }

        return null;
    }, [state.blocks, state.selectedBlockId]);

    /**
     * Find a block by ID in the tree
     */
    const findBlockById = useCallback(
        (id) => {
            const searchBlocks = (blocks) => {
                for (const block of blocks) {
                    if (block.id === id) return block;

                    if (block.props?.children) {
                        for (const column of block.props.children) {
                            const found = searchBlocks(column);
                            if (found) return found;
                        }
                    }
                }
                return null;
            };

            return searchBlocks(state.blocks);
        },
        [state.blocks]
    );

    /**
     * Get data for saving
     */
    const getSaveData = useCallback(() => {
        let data = {
            blocks: state.blocks,
            canvasSettings: state.canvasSettings,
            version: 1,
        };

        // Apply filter before save
        data = LaraHooks.applyFilters(BuilderHooks.FILTER_STATE_BEFORE_SAVE, data, context);

        return data;
    }, [state.blocks, state.canvasSettings, context]);

    // ========================================
    // Context value
    // ========================================

    const contextValue = useMemo(
        () => ({
            // State
            state,
            dispatch,

            // Bound actions
            actions: boundActions,

            // History
            canUndo,
            canRedo,
            undo,
            redo,
            historyLength: state.past.length,
            futureLength: state.future.length,

            // Utilities
            getHtml,
            getSelectedBlock,
            findBlockById,
            getSaveData,

            // Context info
            context,
            config,
        }),
        [
            state,
            dispatch,
            boundActions,
            canUndo,
            canRedo,
            undo,
            redo,
            getHtml,
            getSelectedBlock,
            findBlockById,
            getSaveData,
            context,
            config,
        ]
    );

    return <BuilderContext.Provider value={contextValue}>{children}</BuilderContext.Provider>;
}

/**
 * useBuilder - Hook to access builder context
 *
 * @returns {BuilderContextValue}
 * @throws {Error} If used outside of BuilderProvider
 *
 * @example
 * const { state, actions, canUndo, undo } = useBuilder();
 *
 * // Access blocks
 * const blocks = state.blocks;
 *
 * // Add a block
 * actions.addBlock({ type: 'text', props: { content: 'Hello' } });
 *
 * // Undo
 * if (canUndo) undo();
 */
export function useBuilder() {
    const context = useContext(BuilderContext);

    if (!context) {
        throw new Error('useBuilder must be used within a BuilderProvider');
    }

    return context;
}

/**
 * useBuilderState - Hook to access just the state (for performance)
 */
export function useBuilderState() {
    const { state } = useBuilder();
    return state;
}

/**
 * useBuilderActions - Hook to access just the actions (for performance)
 */
export function useBuilderActions() {
    const { actions } = useBuilder();
    return actions;
}

/**
 * useSelectedBlock - Hook to get the currently selected block
 */
export function useSelectedBlock() {
    const { getSelectedBlock, state } = useBuilder();
    return useMemo(() => getSelectedBlock(), [getSelectedBlock, state.selectedBlockId]);
}

/**
 * useBuilderHistory - Hook for history-related functionality
 */
export function useBuilderHistory() {
    const { canUndo, canRedo, undo, redo, historyLength, futureLength } = useBuilder();
    return { canUndo, canRedo, undo, redo, historyLength, futureLength };
}

export { BuilderContext };
export default BuilderProvider;
