import { createBlockFromJson } from '@lara-builder/factory';
import { __ } from '@lara-builder/i18n';
import config from './block.json';
import block from './block';
import save from './save';

const fields = [
    {
        name: 'src',
        type: 'image',
        label: __('Image'),
        section: __('Content'),
    },
    {
        name: 'alt',
        type: 'text',
        label: __('Alt Text'),
        placeholder: __('Describe the image...'),
        section: __('Content'),
    },
    {
        name: 'link',
        type: 'url',
        label: __('Link URL'),
        placeholder: 'https://...',
        section: __('Link'),
    },
    {
        name: 'width',
        type: 'select',
        label: __('Width'),
        section: __('Size'),
        options: [
            { value: '100%', label: __('Full Width') + ' (100%)' },
            { value: '75%', label: __('Three Quarters') + ' (75%)' },
            { value: '50%', label: __('Half') + ' (50%)' },
            { value: '25%', label: __('Quarter') + ' (25%)' },
            { value: 'custom', label: __('Custom') },
        ],
    },
    {
        name: 'customWidth',
        type: 'text',
        label: __('Custom Width'),
        placeholder: __('e.g., 300px'),
        section: __('Size'),
    },
    {
        name: 'height',
        type: 'select',
        label: __('Height'),
        section: __('Size'),
        options: [
            { value: 'auto', label: __('Auto') },
            { value: 'custom', label: __('Custom') },
        ],
    },
    {
        name: 'customHeight',
        type: 'text',
        label: __('Custom Height'),
        placeholder: __('e.g., 200px'),
        section: __('Size'),
    },
];

export default createBlockFromJson(config, { block, save, fields });
