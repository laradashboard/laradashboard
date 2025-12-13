import { createBlockFromJson } from '@lara-builder/factory';
import { __ } from '@lara-builder/i18n';
import config from './block.json';
import block from './block';
import save from './save';

const fields = [
    {
        name: 'link',
        type: 'url',
        label: __('Link URL'),
        placeholder: 'https://...',
        section: __('Content'),
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
        name: 'fontWeight',
        type: 'select',
        label: __('Font Weight'),
        section: __('Typography'),
        options: [
            { value: 'normal', label: __('Normal') },
            { value: '500', label: __('Medium') },
            { value: '600', label: __('Semi Bold') },
            { value: 'bold', label: __('Bold') },
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
            { value: '9999px', label: __('Pill') },
        ],
    },
    {
        name: 'padding',
        type: 'select',
        label: __('Padding'),
        section: __('Style'),
        options: [
            { value: '8px 16px', label: __('Small') },
            { value: '12px 24px', label: __('Medium') },
            { value: '16px 32px', label: __('Large') },
            { value: '20px 40px', label: __('X-Large') },
        ],
    },
];

export default createBlockFromJson(config, { block, save, fields });
