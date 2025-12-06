/**
 * Preformatted Block
 */

import block from './block';
import editor from './editor';
import config from './block.json';
import save from './save';

const defaultLayoutStyles = {
    margin: { top: '', right: '', bottom: '', left: '' },
    padding: { top: '', right: '', bottom: '', left: '' },
    width: '', minWidth: '', maxWidth: '',
    height: '', minHeight: '', maxHeight: '',
};

const preformattedBlock = {
    ...config,
    block,
    editor,
    save,
    defaultProps: { ...config.defaultProps, layoutStyles: { ...defaultLayoutStyles } },
};

export { block, editor, config, save };
export default preformattedBlock;
