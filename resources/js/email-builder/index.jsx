/**
 * Email Builder Entry Point
 *
 * This file provides backward compatibility for email-builder imports.
 * The actual builder is now handled by lara-builder.
 */

// Re-export from lara-builder entry which handles email-builder-root.
export * from '../lara-builder/entry';
export { default } from '../lara-builder/entry';

// Also export EmailBuilder component for direct usage.
export { default as EmailBuilder } from './EmailBuilder';
