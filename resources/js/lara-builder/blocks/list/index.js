import { createBlockFromJson } from '@lara-builder/factory';
import { __ } from '@lara-builder/i18n';
import config from './block.json';
import block from './block';
import save from './save';
import './style.css';

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
];

export default createBlockFromJson(config, { block, save, fields });
