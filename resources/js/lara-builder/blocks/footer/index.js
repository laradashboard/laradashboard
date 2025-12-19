import { createBlockFromJson } from '@lara-builder/factory';
import { __ } from '@lara-builder/i18n';
import config from './block.json';
import block from './block';
import save from './save';

const fields = [
    {
        name: 'companyName',
        type: 'text',
        label: __('Company Name'),
        section: __('Company Information'),
        placeholder: __('Your Company Name'),
    },
    {
        name: 'address',
        type: 'textarea',
        label: __('Address'),
        section: __('Company Information'),
        rows: 2,
        placeholder: '123 Street Name, City, Country',
    },
    {
        name: 'phone',
        type: 'text',
        label: __('Phone'),
        section: __('Company Information'),
        placeholder: '+1 234 567 890',
    },
    {
        name: 'email',
        type: 'email',
        label: __('Email'),
        section: __('Company Information'),
        placeholder: 'contact@company.com',
    },
    {
        name: 'unsubscribeText',
        type: 'text',
        label: __('Unsubscribe Text'),
        section: __('Unsubscribe Link'),
        placeholder: __('Unsubscribe from these emails'),
    },
    {
        name: 'unsubscribeUrl',
        type: 'url',
        label: __('Unsubscribe URL'),
        section: __('Unsubscribe Link'),
        placeholder: '#unsubscribe',
    },
    {
        name: 'copyright',
        type: 'text',
        label: __('Copyright Text'),
        section: __('Copyright'),
        placeholder: '© 2024 Your Company. All rights reserved.',
    },
    {
        name: 'linkColor',
        type: 'color',
        label: __('Link Color'),
        section: __('Style'),
    },
];

export default createBlockFromJson(config, { block, save, fields });
