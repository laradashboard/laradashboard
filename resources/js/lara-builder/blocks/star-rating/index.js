/**
 * Star Rating Block
 *
 * Visual star rating display with configurable size, colors, and label.
 */

import { createBlockFromJson } from '@lara-builder/factory';
import config from './block.json';
import block from './block';
import editor from './editor';
import save from './save';

// Using custom editor for star-rating-specific styling options
export default createBlockFromJson(config, { block, editor, save });
