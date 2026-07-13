/**
 * Text Editor Block
 *
 * Rich text editor with WYSIWYG formatting.
 */

import { createBlockFromJson } from '@lara-builder/factory';
import { __ } from '@lara-builder/i18n';
import config from './block.json';
import block from './block';
import save from './save';
import './style.css';

const fields = [
    {
        name: 'fontSize',
        type: 'select',
        label: __('Font Size'),
        section: __('Typography'),
        options: [
            { value: '14px', label: __('Small') + ' (14px)' },
            { value: '16px', label: __('Medium') + ' (16px)' },
            { value: '18px', label: __('Large') + ' (18px)' },
            { value: '20px', label: __('X-Large') + ' (20px)' },
        ],
    },
    {
        name: 'lineHeight',
        type: 'select',
        label: __('Line Height'),
        section: __('Typography'),
        options: [
            { value: '1.4', label: __('Tight') },
            { value: '1.6', label: __('Normal') },
            { value: '1.8', label: __('Relaxed') },
            { value: '2', label: __('Loose') },
        ],
    },
    {
        name: 'color',
        type: 'color',
        label: __('Text Color'),
        section: __('Typography'),
    },
];

export default createBlockFromJson(config, { block, save, fields });
