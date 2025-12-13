import { createBlockFromJson } from '@lara-builder/factory';
import { __ } from '@lara-builder/i18n';
import config from './block.json';
import block from './block';
import save from './save';

// Calculate default date (7 days from now)
const getDefaultDate = () => {
    const date = new Date();
    date.setDate(date.getDate() + 7);
    return date.toISOString().split('T')[0];
};

// Override defaultProps with dynamic default date
const configWithDefaults = {
    ...config,
    defaultProps: {
        ...config.defaultProps,
        targetDate: getDefaultDate(),
    },
};

const fields = [
    {
        name: 'targetDate',
        type: 'date',
        label: __('Target Date'),
        section: __('Countdown Settings'),
        required: true,
        help: __('The date when the countdown ends'),
    },
    {
        name: 'targetTime',
        type: 'time',
        label: __('Target Time'),
        section: __('Countdown Settings'),
        help: __('The time when the countdown ends'),
    },
    {
        name: 'title',
        type: 'text',
        label: __('Title'),
        section: __('Countdown Settings'),
        placeholder: __('Sale Ends In'),
    },
    {
        name: 'expiredMessage',
        type: 'text',
        label: __('Expired Message'),
        section: __('Countdown Settings'),
        placeholder: __('This offer has expired!'),
        help: __('Message to show when countdown expires'),
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
        name: 'numberColor',
        type: 'color',
        label: __('Number Color'),
        section: __('Colors'),
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

export default createBlockFromJson(configWithDefaults, { block, save, fields });
