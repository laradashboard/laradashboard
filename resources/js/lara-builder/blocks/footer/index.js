import block from './block.jsx';
import editor from './editor.jsx';
import metadata from './block.json';

export default {
    ...metadata,
    component: block,
    editor: editor,
};
