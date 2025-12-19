/**
 * Table Block
 *
 * A data table block with custom editor for row/column management.
 */

import { createBlockFromJson } from '@lara-builder/factory';
import config from './block.json';
import block from './block';
import editor from './editor';
import save from './save';

// Using custom editor for dynamic row/column management
export default createBlockFromJson(config, { block, editor, save });
