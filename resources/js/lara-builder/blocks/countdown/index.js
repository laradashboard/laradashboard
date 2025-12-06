import metadata from './block.json';
import Block from './block.jsx';
import Editor from './editor.jsx';

// Calculate default date (7 days from now) for defaultProps
const getDefaultDate = () => {
    const date = new Date();
    date.setDate(date.getDate() + 7);
    return date.toISOString().split('T')[0];
};

// Merge computed default date with metadata defaultProps
const defaultProps = {
    ...metadata.defaultProps,
    targetDate: getDefaultDate(),
};

export default {
    ...metadata,
    defaultProps,
    Block,
    Editor,
};
