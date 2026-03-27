/**
 * Alert Banner Block
 *
 * Announcement banner with optional badge, text, and link.
 */

import { createBlockFromJson } from '@lara-builder/factory';
import config from './block.json';
import block from './block';
import editor from './editor';
import save from './save';

export default createBlockFromJson(config, { block, editor, save });
