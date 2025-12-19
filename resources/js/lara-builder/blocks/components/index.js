/**
 * Block Components for LaraBuilder
 *
 * All block components are now loaded from the modular block architecture.
 * Each block is self-contained in its own folder with:
 * - block.json  : Block metadata and configuration
 * - block.jsx   : React component for builder canvas
 * - editor.jsx  : React component for properties panel
 * - index.js    : Main entry point
 *
 * External modules can register their own blocks via blockRegistry.
 */

import { getModularBlockComponent } from '../blockLoader';
import { blockRegistry } from '../../registry/BlockRegistry';

/**
 * Get block component - checks in order:
 * 1. Registry (for external/module blocks like CRM)
 * 2. Modular blocks (core blocks with block.json)
 */
export const getBlockComponent = (type) => {
    // First check the registry for custom blocks (external modules)
    const registryComponent = blockRegistry.getComponent(type);
    if (registryComponent) {
        return registryComponent;
    }

    // Get from modular blocks
    return getModularBlockComponent(type);
};

// Re-export individual block components from modular folders for direct imports
export { default as HeadingBlock } from '../heading/block';
export { default as TextBlock } from '../text/block';
export { default as TextEditorBlock } from '../text-editor/block';
export { default as ImageBlock } from '../image/block';
export { default as ButtonBlock } from '../button/block';
export { default as DividerBlock } from '../divider/block';
export { default as SpacerBlock } from '../spacer/block';
export { default as ColumnsBlock } from '../columns/block';
export { default as SocialBlock } from '../social/block';
export { default as HtmlBlock } from '../html/block';
export { default as QuoteBlock } from '../quote/block';
export { default as ListBlock } from '../list/block';
export { default as VideoBlock } from '../video/block';
export { default as FooterBlock } from '../footer/block';
export { default as CountdownBlock } from '../countdown/block';
export { default as TableBlock } from '../table/block';
export { default as CodeBlock } from '../code/block';
export { default as PreformattedBlock } from '../preformatted/block';
export { default as AccordionBlock } from '../accordion/block';
