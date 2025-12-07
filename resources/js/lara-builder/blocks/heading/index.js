/**
 * Heading Block
 *
 * A heading block with inline editing support.
 */

import { createBlockFromJson } from '@lara-builder/factory';
import { __ } from '@lara-builder/i18n';
import config from './block.json';
import block from './block';
import save from './save';

// Fields defined in JS for translation support
const fields = [
    {
        name: 'text',
        type: 'text',
        label: __('Heading Text'),
        placeholder: __('Enter heading text...'),
        section: __('Content'),
    },
    {
        name: 'level',
        type: 'select',
        label: __('Heading Level'),
        section: __('Content'),
        options: [
            { value: 'h1', label: __('H1 - Main Heading') },
            { value: 'h2', label: __('H2 - Section Heading') },
            { value: 'h3', label: __('H3 - Subsection') },
            { value: 'h4', label: __('H4 - Minor Heading') },
            { value: 'h5', label: __('H5 - Small Heading') },
            { value: 'h6', label: __('H6 - Smallest Heading') },
        ],
    },
];

export default createBlockFromJson(config, { block, save, fields });
