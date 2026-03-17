/**
 * Steps Block
 *
 * Numbered steps with title, description, and optional code or link.
 */

import { createBlockFromJson } from '@lara-builder/factory';
import config from './block.json';
import block from './block';
import editor from './editor';
import save from './save';

// Using custom editor for steps-specific styling options
export default createBlockFromJson(config, { block, editor, save });
