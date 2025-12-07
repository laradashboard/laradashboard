import block from './block';
import editor from './editor';
import config from './block.json';
import save from './save';

// Default layout styles.
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

// Block definition combining config and components.
const textBlock = {
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
export default textBlock;
