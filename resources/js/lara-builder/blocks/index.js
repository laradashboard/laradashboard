/**
 * LaraBuilder Blocks
 *
 * This module exports all block-related functionality:
 * - Block components (React components for rendering blocks)
 * - Default block definitions (block configurations)
 */

// Export block components
export * from './components';
export { blockComponents, getBlockComponent } from './components';

// Export default block definitions
export { defaultBlocks, defaultLayoutStyles } from './defaultBlocks';
