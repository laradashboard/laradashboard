/**
 * Email Builder Entry Point
 *
 * DEPRECATED: This folder is kept for backward compatibility only.
 * All functionality has been merged into lara-builder.
 *
 * New projects should import directly from lara-builder:
 *   import { EmailBuilder } from '@lara-builder';
 */

// Re-export from lara-builder entry which handles email-builder-root.
export * from '../lara-builder/entry';
export { default } from '../lara-builder/entry';

// Also export EmailBuilder component for direct usage.
export { default as EmailBuilder } from '../lara-builder/EmailBuilder';
