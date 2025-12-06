/**
 * Button Block
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
const buttonBlock = {
    ...config,
    component: Block,
    propertyEditor: Editor,
    defaultProps: {
        ...config.defaultProps,
        layoutStyles: { ...defaultLayoutStyles },
    },
};

export { Block, Editor, config };
export default buttonBlock;
