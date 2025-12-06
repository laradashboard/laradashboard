/**
 * List Block
 *
 * Block file structure:
 * - index.js    : Main entry point, exports block definition
 * - block.json  : Block metadata and configuration
 * - block.jsx   : React component for builder canvas
 * - editor.jsx  : React component for properties panel
 */

import Block from './block';
import Editor from './editor';
import config from './block.json';

// Default layout styles
const defaultLayoutStyles = {
    margin: { top: '', right: '', bottom: '', left: '' },
    padding: { top: '', right: '', bottom: '', left: '' },
    width: '',
    minWidth: '',
    maxWidth: '',
    height: '',
    minHeight: '',
    maxHeight: '',
};

// Block definition combining config and components
const listBlock = {
    // Spread config from block.json
    ...config,

    // React component for rendering in the builder canvas
    component: Block,

    // React component for the properties panel editor
    propertyEditor: Editor,

    // Merge defaultProps with layoutStyles
    defaultProps: {
        ...config.defaultProps,
        layoutStyles: { ...defaultLayoutStyles },
    },
};

export { Block, Editor, config };
export default listBlock;
