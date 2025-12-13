import { createBlockFromJson } from '@lara-builder/factory';
import { __ } from '@lara-builder/i18n';
import config from './block.json';
import block from './block';
import save from './save';

const fields = [
    {
        name: 'listType',
        type: 'select',
        label: __('List Type'),
        section: __('Content'),
        options: [
            { value: 'bullet', label: __('Bullet List') },
            { value: 'number', label: __('Numbered List') },
            { value: 'check', label: __('Check List') },
        ],
    },
    {
        name: 'color',
        type: 'color',
        label: __('Text Color'),
        section: __('Style'),
    },
    {
        name: 'fontSize',
        type: 'select',
        label: __('Font Size'),
        section: __('Style'),
        options: [
            { value: '12px', label: __('Small') + ' (12px)' },
            { value: '14px', label: __('Normal') + ' (14px)' },
            { value: '16px', label: __('Medium') + ' (16px)' },
            { value: '18px', label: __('Large') + ' (18px)' },
            { value: '20px', label: __('X-Large') + ' (20px)' },
        ],
    },
    {
        name: 'iconColor',
        type: 'color',
        label: __('Icon Color'),
        section: __('Style'),
    },
];

export default createBlockFromJson(config, { block, save, fields });
