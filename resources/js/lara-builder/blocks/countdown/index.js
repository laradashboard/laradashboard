/**
 * Countdown Block
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

// Calculate default date (7 days from now) for defaultProps
const getDefaultDate = () => {
    const date = new Date();
    date.setDate(date.getDate() + 7);
    return date.toISOString().split('T')[0];
};

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
const countdownBlock = {
    ...config,
    block,
    editor,
    save,
    defaultProps: {
        ...config.defaultProps,
        targetDate: getDefaultDate(),
        layoutStyles: { ...defaultLayoutStyles },
    },
};

export { block, editor, config, save };
export default countdownBlock;
