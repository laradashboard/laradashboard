import { createBlockFromJson } from '@lara-builder/factory';
import { __ } from '@lara-builder/i18n';
import config from './block.json';
import block from './block';
import save from './save';

const fields = [
    {
        name: 'displayAsRange',
        type: 'toggle',
        label: __('Display as range'),
        section: __('Settings'),
        help: __('Show reading time as a range (e.g., "1-2 minutes" instead of "2 minutes")'),
    },
    {
        name: 'wordsPerMinute',
        type: 'number',
        label: __('Words per minute'),
        section: __('Settings'),
        help: __('Average reading speed (default: 200 WPM)'),
        min: 100,
        max: 400,
        step: 10,
    },
    {
        name: 'showIcon',
        type: 'toggle',
        label: __('Show clock icon'),
        section: __('Settings'),
    },
    {
        name: 'prefix',
        type: 'text',
        label: __('Prefix text'),
        section: __('Labels'),
        placeholder: __('e.g., "Reading time: "'),
    },
    {
        name: 'suffix',
        type: 'text',
        label: __('Suffix text'),
        section: __('Labels'),
        placeholder: __('e.g., " read"'),
    },
    {
        name: 'color',
        type: 'color',
        label: __('Text Color'),
        section: __('Colors'),
    },
    {
        name: 'iconColor',
        type: 'color',
        label: __('Icon Color'),
        section: __('Colors'),
    },
    {
        name: 'fontSize',
        type: 'select',
        label: __('Font Size'),
        section: __('Typography'),
        options: [
            { value: '12px', label: '12px' },
            { value: '14px', label: '14px' },
            { value: '16px', label: '16px' },
            { value: '18px', label: '18px' },
        ],
    },
    {
        name: 'align',
        type: 'select',
        label: __('Alignment'),
        section: __('Layout'),
        options: [
            { value: 'left', label: __('Left') },
            { value: 'center', label: __('Center') },
            { value: 'right', label: __('Right') },
        ],
    },
];

export default createBlockFromJson(config, { block, save, fields });
