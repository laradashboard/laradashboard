import { createBlockFromJson } from '@lara-builder/factory';
import { __ } from '@lara-builder/i18n';
import config from './block.json';
import block from './block';
import save from './save';

const fields = [
    {
        name: 'borderRadius',
        type: 'text',
        label: __('Border Radius'),
        section: __('Style'),
        placeholder: '4px',
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
        name: 'borderColor',
        type: 'color',
        label: __('Border Color'),
        section: __('Colors'),
    },
];

export default createBlockFromJson(config, { block, save, fields });
