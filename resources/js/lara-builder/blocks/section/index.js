/**
 * Section Block
 *
 * Full-width section container with gradient background support.
 * Ideal for hero sections, feature sections, and page divisions.
 */

import { createBlockFromJson } from '@lara-builder/factory';
import config from './block.json';
import block from './block';
import editor from './editor';
import save from './save';

export default createBlockFromJson(config, { block, editor, save });
