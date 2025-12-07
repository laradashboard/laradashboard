/**
 * Quote Block
 *
 * A quote/testimonial block with author info.
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
        type: 'textarea',
        label: __('Quote Text'),
        placeholder: __('Enter quote text...'),
        section: __('Content'),
    },
    {
        name: 'author',
        type: 'text',
        label: __('Author Name'),
        placeholder: 'John Doe',
        section: __('Author'),
    },
    {
        name: 'authorTitle',
        type: 'text',
        label: __('Author Title'),
        placeholder: 'CEO, Company',
        section: __('Author'),
    },
    {
        name: 'borderColor',
        type: 'color',
        label: __('Border Color'),
        section: __('Style'),
    },
    {
        name: 'backgroundColor',
        type: 'color',
        label: __('Background Color'),
        section: __('Style'),
    },
    {
        name: 'textColor',
        type: 'color',
        label: __('Text Color'),
        section: __('Style'),
    },
    {
        name: 'authorColor',
        type: 'color',
        label: __('Author Color'),
        section: __('Style'),
    },
];

export default createBlockFromJson(config, { block, save, fields });
