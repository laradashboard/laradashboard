import { createBlockFromJson } from '@lara-builder/factory';
import { __ } from '@lara-builder/i18n';
import config from './block.json';
import block from './block';
import save from './save';

const fields = [
    {
        name: 'iconSize',
        type: 'select',
        label: __('Icon Size'),
        section: __('Appearance'),
        options: [
            { value: '24px', label: __('Small') + ' (24px)' },
            { value: '32px', label: __('Medium') + ' (32px)' },
            { value: '40px', label: __('Large') + ' (40px)' },
            { value: '48px', label: __('X-Large') + ' (48px)' },
        ],
    },
    {
        name: 'gap',
        type: 'select',
        label: __('Spacing'),
        section: __('Appearance'),
        options: [
            { value: '8px', label: __('Small') },
            { value: '12px', label: __('Medium') },
            { value: '16px', label: __('Large') },
            { value: '24px', label: __('X-Large') },
        ],
    },
    {
        name: 'links.facebook',
        type: 'url',
        label: __('Facebook URL'),
        placeholder: 'https://facebook.com/...',
        section: __('Links'),
    },
    {
        name: 'links.twitter',
        type: 'url',
        label: __('Twitter/X URL'),
        placeholder: 'https://twitter.com/...',
        section: __('Links'),
    },
    {
        name: 'links.instagram',
        type: 'url',
        label: __('Instagram URL'),
        placeholder: 'https://instagram.com/...',
        section: __('Links'),
    },
    {
        name: 'links.linkedin',
        type: 'url',
        label: __('LinkedIn URL'),
        placeholder: 'https://linkedin.com/...',
        section: __('Links'),
    },
    {
        name: 'links.youtube',
        type: 'url',
        label: __('YouTube URL'),
        placeholder: 'https://youtube.com/...',
        section: __('Links'),
    },
];

export default createBlockFromJson(config, { block, save, fields });
