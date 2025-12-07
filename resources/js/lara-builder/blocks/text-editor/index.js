/**
 * Text Editor Block
 *
 * Rich text editor with WYSIWYG formatting.
 */

import { createBlockFromJson } from '@lara-builder/factory';
import config from './block.json';
import block from './block';
import save from './save';

// Editor is embedded in block.jsx for text-editor
export default createBlockFromJson(config, { block, save });
