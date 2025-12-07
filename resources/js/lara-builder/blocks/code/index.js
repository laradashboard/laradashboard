/**
 * Code Block
 *
 * A code snippet block.
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
        label: __('Code'),
        section: __('Content'),
        rows: 10,
        placeholder: __('Enter your code here...'),
    },
    {
        name: 'backgroundColor',
        type: 'color',
        label: __('Background Color'),
        section: __('Colors'),
    },
    {
        name: 'textColor',
        type: 'color',
        label: __('Text Color'),
        section: __('Colors'),
    },
    {
        name: 'fontSize',
        type: 'select',
        label: __('Font Size'),
        section: __('Typography'),
        options: [
            { value: '12px', label: __('X-Small') + ' (12px)' },
            { value: '14px', label: __('Small') + ' (14px)' },
            { value: '16px', label: __('Medium') + ' (16px)' },
            { value: '18px', label: __('Large') + ' (18px)' },
        ],
    },
    {
        name: 'borderRadius',
        type: 'select',
        label: __('Border Radius'),
        section: __('Style'),
        options: [
            { value: '0', label: __('None') },
            { value: '4px', label: __('Small') + ' (4px)' },
            { value: '6px', label: __('Medium') + ' (6px)' },
            { value: '8px', label: __('Large') + ' (8px)' },
            { value: '12px', label: __('X-Large') + ' (12px)' },
        ],
    },
];

export default createBlockFromJson(config, { block, save, fields });
