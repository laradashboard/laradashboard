import { createBlockFromJson } from '@lara-builder/factory';
import { __ } from '@lara-builder/i18n';
import config from './block.json';
import block from './block';
import save from './save';

const fields = [
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
