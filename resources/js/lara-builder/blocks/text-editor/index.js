import metadata from './block.json';
import TextEditorBlock from './block';
import TextEditorPropertyEditor from './editor';

export default {
    ...metadata,
    component: TextEditorBlock,
    editor: TextEditorPropertyEditor,
};
