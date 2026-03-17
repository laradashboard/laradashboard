/**
 * Testimonial Block
 *
 * Testimonial card with avatar, name, role, star rating, and quote.
 */

import { createBlockFromJson } from '@lara-builder/factory';
import config from './block.json';
import block from './block';
import editor from './editor';
import save from './save';

export default createBlockFromJson(config, { block, editor, save });
