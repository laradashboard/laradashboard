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
 *
 * This module provides a bridge between the old block system and the new
 * modular block architecture. As blocks are migrated, they're added here.
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
];

/**
 * Map of block types to their modular definitions
 * Used for quick lookup when checking if a block has been migrated
 */
const modularBlockMap = modularBlocks.reduce((acc, block) => {
    acc[block.type] = block;
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
    const block = modularBlockMap[type];
    return block?.component || null;
};

/**
 * Get block editor (property panel) by type (from modular blocks only)
 */
export const getModularBlockEditor = (type) => {
    const block = modularBlockMap[type];
    return block?.propertyEditor || null;
};

/**
 * Get block config by type (from modular blocks only)
 * Returns config without component references (for serialization)
 */
export const getModularBlockConfig = (type) => {
    const block = modularBlockMap[type];
    if (!block) return null;

    const { component, propertyEditor, ...config } = block;
    return config;
};

/**
 * Register modular blocks with the block registry
 * This merges modular block definitions with legacy blocks
 */
export const registerModularBlocks = (registry) => {
    modularBlocks.forEach((block) => {
        // Modular blocks include component references
        registry.register(block);
    });
};

/**
 * Enhance legacy block definitions with modular components
 * Call this to add component/propertyEditor from modular blocks to legacy definitions
 */
export const enhanceLegacyBlocks = (legacyBlocks) => {
    return legacyBlocks.map((block) => {
        const modularBlock = modularBlockMap[block.type];
        if (modularBlock) {
            // Merge modular block into legacy block (modular takes priority)
            return {
                ...block,
                ...modularBlock,
            };
        }
        return block;
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
    registerModularBlocks,
    enhanceLegacyBlocks,
};
