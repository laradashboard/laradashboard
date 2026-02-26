/**
 * Module-level store for pending cursor positions.
 * Allows insert/merge operations to communicate a desired cursor position
 * to the TextBlock focus effect after state updates complete.
 *
 * Key: block ID (string)
 * Value: 'start' | number (character offset from the start of text content)
 */
export const pendingCursors = new Map();
