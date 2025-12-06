/**
 * LaraBuilder Blocks
 *
 * This module exports all block-related functionality:
 * - Block components (React components for rendering blocks)
 * - Block loader for modular block architecture
 */

// Export block components and getBlockComponent
export * from './components';
export { getBlockComponent } from './components';

// Export block loader utilities
export * from './blockLoader';
