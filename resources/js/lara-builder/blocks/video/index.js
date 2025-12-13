/**
 * Video Block
 *
 * A video block with thumbnail and play button.
 * Uses custom editor for media library integration.
 */

import { createBlockFromJson } from '@lara-builder/factory';
import config from './block.json';
import block from './block';
import editor from './editor';
import save from './save';

// Using custom editor for media library integration
export default createBlockFromJson(config, { block, editor, save });
