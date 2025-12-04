/**
 * Block Registry - Re-exports from lara-builder.
 *
 * This file provides backward compatibility for email-builder imports.
 * All block registration is now handled by lara-builder/registry/BlockRegistry.
 */

import { blockRegistry } from '../../lara-builder/registry/BlockRegistry';
import { defaultBlocks } from '../../lara-builder/blocks/defaultBlocks';

// Re-export the singleton registry.
export default blockRegistry;

// Provide compatible API functions.
export const registerBlock = (type, config) => {
    blockRegistry.register({ type, ...config });
};

export const getBlock = (type) => blockRegistry.get(type);

export const getAllBlocks = () => blockRegistry.getAll();

export const getBlocksByCategory = (category) => {
    return blockRegistry.getAll().filter(block => block.category === category);
};

export const getCategories = () => blockRegistry.getCategories();

// Re-export default blocks for reference.
export { defaultBlocks };
