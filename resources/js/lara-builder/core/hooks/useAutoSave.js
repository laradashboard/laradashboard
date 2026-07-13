/**
 * useAutoSave - Debounced background save for post builder
 *
 * Saves silently once a title exists and the form has unsaved changes.
 */

import { useEffect, useRef } from "react";

const DEFAULT_DEBOUNCE_MS = 45000;

const AUTO_SAVE_STATUSES = new Set(["draft", "pending"]);

export function shouldAutoSavePost(status) {
    return AUTO_SAVE_STATUSES.has(status);
}

export function useAutoSave({
    enabled = false,
    isDirty = false,
    canSave = false,
    isSaving = false,
    onAutoSave,
    debounceMs = DEFAULT_DEBOUNCE_MS,
}) {
    const timerRef = useRef(null);
    const onAutoSaveRef = useRef(onAutoSave);

    onAutoSaveRef.current = onAutoSave;

    useEffect(() => {
        if (timerRef.current) {
            clearTimeout(timerRef.current);
            timerRef.current = null;
        }

        if (!enabled || !canSave || !isDirty || isSaving) {
            return undefined;
        }

        timerRef.current = window.setTimeout(async () => {
            try {
                await onAutoSaveRef.current();
            } catch (error) {
                console.error("Auto-save failed:", error);
            }
        }, debounceMs);

        return () => {
            if (timerRef.current) {
                clearTimeout(timerRef.current);
                timerRef.current = null;
            }
        };
    }, [enabled, canSave, isDirty, isSaving, debounceMs]);
}

export default useAutoSave;
