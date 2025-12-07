/**
 * HTML Block
 *
 * A block for custom HTML content.
 */

import { createBlockFromJson } from '@lara-builder/factory';
import { __ } from '@lara-builder/i18n';
import config from './block.json';
import block from './block';
import save from './save';

// Fields defined in JS for translation support
const fields = [
    {
        name: 'code',
        type: 'textarea',
        label: __('HTML Code'),
        section: __('Content'),
        rows: 10,
    },
];

export default createBlockFromJson(config, { block, save, fields });
