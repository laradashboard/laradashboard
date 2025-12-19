/**
 * LaraBuilder Factory
 *
 * Simplified APIs for creating blocks with minimal boilerplate.
 *
 * ## Quick Start
 *
 * ### 1. Simple Block (minimal code):
 * ```js
 * import { createBlock } from '@lara-builder/factory';
 *
 * export default createBlock({
 *     type: 'my-block',
 *     label: 'My Block',
 *     icon: 'mdi:star',
 *     defaultProps: { text: 'Hello' },
 *     block: ({ props }) => <div>{props.text}</div>,
 * });
 * ```
 *
 * ### 2. Block with Auto-Generated Editor:
 * ```js
 * import { createBlock } from '@lara-builder/factory';
 *
 * export default createBlock({
 *     type: 'my-block',
 *     label: 'My Block',
 *     icon: 'mdi:star',
 *     defaultProps: { text: 'Hello', color: '#333' },
 *     fields: [
 *         { type: 'text', name: 'text', label: 'Text Content' },
 *         { type: 'color', name: 'color', label: 'Text Color' },
 *     ],
 *     block: ({ props }) => <div style={{ color: props.color }}>{props.text}</div>,
 * });
 * ```
 *
 * ### 3. Block from JSON config:
 * ```js
 * import { createBlockFromJson } from '@lara-builder/factory';
 * import config from './block.json';
 * import block from './block';
 * import editor from './editor';
 * import save from './save';
 *
 * export default createBlockFromJson(config, { block, editor, save });
 * ```
 *
 * ### 4. Save Helpers:
 * ```js
 * import { createSave, emailButton, pageDiv } from '@lara-builder/factory';
 *
 * export default createSave({
 *     type: 'button',
 *     page: (props) => pageDiv('button', props, `<a href="${props.link}">${props.text}</a>`),
 *     email: (props) => emailButton(props),
 * });
 * ```
 */

// Block creation
export { createBlock, createBlockFromJson, createSimpleBlock } from './createBlock.jsx';

// Editor components
export {
    EditorField,
    EditorSection,
    EditorLabel,
    AutoEditor,
    createAutoEditor,
} from './EditorField';

// Save/HTML helpers
export {
    pageDiv,
    emailTable,
    emailTableWithLayout,
    createSave,
    emailTextStyles,
    emailButton,
    emailImage,
    emailDivider,
    emailSpacer,
} from './saveHelpers';

// Default export for convenience
import { createBlock } from './createBlock.jsx';
export default createBlock;
