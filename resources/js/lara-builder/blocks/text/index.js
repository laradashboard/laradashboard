/**
 * Text Block
 *
 * A text/paragraph block with inline editing support.
 */

import { createBlockFromJson } from '@lara-builder/factory';
import { __ } from '@lara-builder/i18n';
import config from './block.json';
import block from './block';
import save from './save';

// Fields defined in JS for translation support
const fields = [
    {
        name: 'content',
        type: 'textarea',
        label: __('Text Content'),
        placeholder: __('Enter text content...'),
        section: __('Content'),
    },
];

export default createBlockFromJson(config, { block, save, fields });
