/**
 * Card Block
 *
 * Styled card container with border, shadow, and hover effects.
 * Supports nesting - drag blocks inside to build card content.
 */

import { createBlockFromJson } from '@lara-builder/factory';
import config from './block.json';
import block from './block';
import editor from './editor';
import save from './save';

export default createBlockFromJson(config, { block, editor, save });
