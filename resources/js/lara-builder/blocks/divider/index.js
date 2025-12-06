/**
 * Divider Block
 */

import Block from './block';
import Editor from './editor';
import config from './block.json';

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

const dividerBlock = {
    ...config,
    component: Block,
    propertyEditor: Editor,
    defaultProps: {
        ...config.defaultProps,
        layoutStyles: { ...defaultLayoutStyles },
    },
};

export { Block, Editor, config };
export default dividerBlock;
