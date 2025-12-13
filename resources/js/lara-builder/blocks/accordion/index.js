/**
 * Accordion Block
 *
 * Collapsible accordion sections with custom styling editor.
 */

import { createBlockFromJson } from '@lara-builder/factory';
import config from './block.json';
import block from './block';
import editor from './editor';
import save from './save';

// Using custom editor for accordion-specific styling options
export default createBlockFromJson(config, { block, editor, save });
