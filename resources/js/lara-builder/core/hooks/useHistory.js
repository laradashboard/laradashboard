/**
 * useHistory - Hook for Undo/Redo with Keyboard Shortcuts
 *
 * Provides undo/redo functionality with keyboard shortcuts:
 * - Cmd/Ctrl + Z: Undo
 * - Cmd/Ctrl + Shift + Z: Redo
 * - Cmd/Ctrl + Y: Redo (alternative)
 *
 * @example
 * const { canUndo, canRedo, undo, redo } = useHistory();
 */

import { useEffect, useCallback } from 'react';
import { useBuilder } from '../BuilderContext';
import { LaraHooks } from '../../hooks-system/LaraHooks';
import { BuilderHooks } from '../../hooks-system/HookNames';

/**
 * Hook for history management with keyboard shortcuts
 *
 * @param {Object} options
 * @param {boolean} options.enableKeyboardShortcuts - Enable keyboard shortcuts (default: true)
 * @param {boolean} options.preventDefault - Prevent default browser behavior (default: true)
 */
export function useHistory(options = {}) {
    const { enableKeyboardShortcuts = true, preventDefault = true } = options;

    const { canUndo, canRedo, undo, redo, historyLength, futureLength, state } = useBuilder();

    /**
     * Handle keyboard shortcuts
     */
    const handleKeyDown = useCallback(
        (event) => {
            // Check if Cmd (Mac) or Ctrl (Windows/Linux) is pressed
            const isMod = event.metaKey || event.ctrlKey;

            if (!isMod) return;

            // Undo: Cmd/Ctrl + Z (without Shift)
            if (event.key === 'z' && !event.shiftKey) {
                if (canUndo) {
                    if (preventDefault) {
                        event.preventDefault();
                    }
                    undo();
                    LaraHooks.doAction(BuilderHooks.ACTION_KEYBOARD_SHORTCUT, 'undo', event);
                }
                return;
            }

            // Redo: Cmd/Ctrl + Shift + Z
            if (event.key === 'z' && event.shiftKey) {
                if (canRedo) {
                    if (preventDefault) {
                        event.preventDefault();
                    }
                    redo();
                    LaraHooks.doAction(BuilderHooks.ACTION_KEYBOARD_SHORTCUT, 'redo', event);
                }
                return;
            }

            // Redo: Cmd/Ctrl + Y (alternative shortcut)
            if (event.key === 'y') {
                if (canRedo) {
                    if (preventDefault) {
                        event.preventDefault();
                    }
                    redo();
                    LaraHooks.doAction(BuilderHooks.ACTION_KEYBOARD_SHORTCUT, 'redo', event);
                }
                return;
            }
        },
        [canUndo, canRedo, undo, redo, preventDefault]
    );

    /**
     * Register keyboard event listeners
     */
    useEffect(() => {
        if (!enableKeyboardShortcuts) return;

        document.addEventListener('keydown', handleKeyDown);

        return () => {
            document.removeEventListener('keydown', handleKeyDown);
        };
    }, [enableKeyboardShortcuts, handleKeyDown]);

    return {
        // State
        canUndo,
        canRedo,
        historyLength,
        futureLength,

        // Actions
        undo,
        redo,

        // Info
        isAtStart: historyLength === 0,
        isAtEnd: futureLength === 0,
        totalHistory: historyLength + futureLength,
    };
}

export default useHistory;
