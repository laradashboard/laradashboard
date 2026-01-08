/**
 * Block Loader
 *
 * Auto-discovers and loads blocks from the blocks directory.
 * Each block folder should contain:
 * - index.js    : Main entry point exporting the block definition
 * - block.json  : Block metadata and configuration
 * - block.jsx   : React component for builder canvas
 * - editor.jsx  : React component for properties panel
 * - render.php  : Server-side rendering (optional)
 */

// Import all block modules (new modular format)
// Each block exports its complete definition from index.js
import headingBlock from './heading';
import textBlock from './text';
import textEditorBlock from './text-editor';
import imageBlock from './image';
import buttonBlock from './button';
import dividerBlock from './divider';
import spacerBlock from './spacer';
import columnsBlock from './columns';
import socialBlock from './social';
import htmlBlock from './html';
import quoteBlock from './quote';
import listBlock from './list';
import videoBlock from './video';
import footerBlock from './footer';
import countdownBlock from './countdown';
import tableBlock from './table';
import codeBlock from './code';
import preformattedBlock from './preformatted';
import accordionBlock from './accordion';
import tocBlock from './toc';
import timeToReadBlock from './time-to-read';
import markdownBlock from './markdown';

/**
 * All modular blocks (new format with block.json)
 */
const modularBlocks = [
    headingBlock,
    textBlock,
    textEditorBlock,
    imageBlock,
    buttonBlock,
    dividerBlock,
    spacerBlock,
    columnsBlock,
    socialBlock,
    htmlBlock,
    quoteBlock,
    listBlock,
    videoBlock,
    footerBlock,
    countdownBlock,
    tableBlock,
    codeBlock,
    preformattedBlock,
    accordionBlock,
    tocBlock,
    timeToReadBlock,
    markdownBlock,
];

/**
 * Map of block types to their modular definitions
 * Used for quick lookup when checking if a block has been migrated
 */
const modularBlockMap = modularBlocks.reduce((acc, block) => {
    if (block && block.type) {
        acc[block.type] = block;
    }
    return acc;
}, {});

/**
 * Check if a block type has been migrated to modular format
 */
export const isModularBlock = (type) => type in modularBlockMap;

/**
 * Get all modular blocks
 */
export const getAllModularBlocks = () => modularBlocks;

/**
 * Get a modular block by type
 */
export const getModularBlock = (type) => modularBlockMap[type] || null;

/**
 * Get block component by type (from modular blocks only)
 */
export const getModularBlockComponent = (type) => {
    const blockDef = modularBlockMap[type];
    return blockDef?.block || null;
};

/**
 * Get block editor (property panel) by type (from modular blocks only)
 */
export const getModularBlockEditor = (type) => {
    const blockDef = modularBlockMap[type];
    return blockDef?.editor || null;
};

/**
 * Get block config by type (from modular blocks only)
 * Returns config without component references (for serialization)
 */
export const getModularBlockConfig = (type) => {
    const blockDef = modularBlockMap[type];
    if (!blockDef) return null;

    const { block, editor, save, ...config } = blockDef;
    return config;
};

/**
 * Get block supports configuration by type
 * Checks both modular blocks (core) and blockRegistry (external modules)
 * Returns the supports object from block.json or empty object
 */
export const getBlockSupports = (type) => {
    // First check modular blocks (core blocks)
    const blockDef = modularBlockMap[type];
    if (blockDef?.supports) {
        return blockDef.supports;
    }

    // Fall back to blockRegistry for external blocks (e.g., CRM module blocks)
    if (typeof window !== 'undefined' && window.LaraBuilderBlockRegistry) {
        const registryBlock = window.LaraBuilderBlockRegistry.get(type);
        if (registryBlock?.supports) {
            return registryBlock.supports;
        }
    }

    return {};
};

/**
 * Check if a block supports a specific feature
 * @param {string} type - Block type
 * @param {string} feature - Feature name (e.g., 'bold', 'align', 'headingLevel')
 * @returns {boolean}
 */
export const blockSupports = (type, feature) => {
    const supports = getBlockSupports(type);
    return supports[feature] === true;
};

/**
 * Register modular blocks with the block registry
 */
export const registerModularBlocks = (registry) => {
    modularBlocks.forEach((block, index) => {
        // Skip undefined or invalid blocks
        if (!block || !block.type) {
            console.warn(`[BlockLoader] Skipping invalid block at index ${index}:`, block);
            return;
        }
        // Modular blocks include component references
        registry.register(block);
    });
};

export default {
    modularBlocks,
    modularBlockMap,
    isModularBlock,
    getAllModularBlocks,
    getModularBlock,
    getModularBlockComponent,
    getModularBlockEditor,
    getModularBlockConfig,
    getBlockSupports,
    blockSupports,
    registerModularBlocks,
};
