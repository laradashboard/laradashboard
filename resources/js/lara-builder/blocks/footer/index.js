/**
 * Footer Block
 *
 * Block file structure:
 * - index.js    : Main entry point, exports block definition
 * - block.json  : Block metadata and configuration
 * - block.jsx   : React component for builder canvas
 * - editor.jsx  : React component for properties panel
 * - save.js     : HTML generators for page/email output
 */

import block from './block';
import editor from './editor';
import config from './block.json';
import save from './save';

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
const footerBlock = {
    ...config,
    block,
    editor,
    save,
    defaultProps: {
        ...config.defaultProps,
        layoutStyles: { ...defaultLayoutStyles },
    },
};

export { block, editor, config, save };
export default footerBlock;
