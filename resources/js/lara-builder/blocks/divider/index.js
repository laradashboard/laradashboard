import { createBlockFromJson } from '@lara-builder/factory';
import { __ } from '@lara-builder/i18n';
import config from './block.json';
import block from './block';
import save from './save';

const fields = [
    {
        name: 'style',
        type: 'select',
        label: __('Style'),
        section: __('Appearance'),
        options: [
            { value: 'solid', label: __('Solid') },
            { value: 'dashed', label: __('Dashed') },
            { value: 'dotted', label: __('Dotted') },
        ],
    },
    {
        name: 'color',
        type: 'color',
        label: __('Color'),
        section: __('Appearance'),
    },
    {
        name: 'thickness',
        type: 'select',
        label: __('Thickness'),
        section: __('Size'),
        options: [
            { value: '1px', label: '1px' },
            { value: '2px', label: '2px' },
            { value: '3px', label: '3px' },
            { value: '4px', label: '4px' },
        ],
    },
    {
        name: 'width',
        type: 'select',
        label: __('Width'),
        section: __('Size'),
        options: [
            { value: '100%', label: __('Full Width') },
            { value: '75%', label: '75%' },
            { value: '50%', label: '50%' },
            { value: '25%', label: '25%' },
        ],
    },
];

export default createBlockFromJson(config, { block, save, fields });
