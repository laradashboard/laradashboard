/**
 * Tabs Block
 *
 * Tabbed content with switchable panels and custom styling editor.
 */

import { createBlockFromJson } from '@lara-builder/factory';
import config from './block.json';
import block from './block';
import editor from './editor';
import save from './save';

// Using custom editor for tabs-specific styling options
export default createBlockFromJson(config, { block, editor, save });
