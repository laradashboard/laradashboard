/**
 * Registry for block-level flush handlers invoked before save.
 * Ensures contenteditable edits (e.g. list items) are synced to state.
 */

const flushHandlers = new Set();

export function registerSaveFlush(handler) {
    flushHandlers.add(handler);

    return () => {
        flushHandlers.delete(handler);
    };
}

export function runSaveFlushes() {
    flushHandlers.forEach((handler) => {
        try {
            handler();
        } catch (error) {
            console.error("Save flush handler failed:", error);
        }
    });
}

/**
 * Blur active editors and run registered flush handlers so pending
 * contenteditable changes are committed before save.
 */
export function flushPendingEdits() {
    runSaveFlushes();

    const active = document.activeElement;

    if (!(active instanceof HTMLElement)) {
        return;
    }

    const editable = active.isContentEditable
        ? active
        : active.closest('[contenteditable="true"]');

    if (editable instanceof HTMLElement) {
        editable.blur();
        runSaveFlushes();
    }
}
