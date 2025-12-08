import { createBlockFromJson } from '@lara-builder/factory';
import { __ } from '@lara-builder/i18n';
import config from './block.json';
import block from './block';
import save from './save';

const fields = [
    {
        name: 'height',
        type: 'select',
        label: __('Height'),
        section: __('Size'),
        options: [
            { value: '10px', label: '10px' },
            { value: '20px', label: '20px' },
            { value: '30px', label: '30px' },
            { value: '40px', label: '40px' },
            { value: '50px', label: '50px' },
            { value: '60px', label: '60px' },
            { value: '80px', label: '80px' },
            { value: '100px', label: '100px' },
        ],
    },
];

export default createBlockFromJson(config, { block, save, fields });
