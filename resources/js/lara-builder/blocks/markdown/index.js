import { createBlockFromJson } from '@lara-builder/factory';
import { __ } from '@lara-builder/i18n';
import config from './block.json';
import block from './block';
import save from './save';

const fields = [
    {
        name: 'sourceType',
        type: 'select',
        label: __('Source Type'),
        section: __('Content'),
        options: [
            { value: 'content', label: __('Write Content') },
            { value: 'url', label: __('From URL') },
        ],
    },
    {
        name: 'url',
        type: 'text',
        label: __('Markdown URL'),
        section: __('Content'),
        placeholder: 'https://github.com/user/repo/blob/main/README.md',
        description: __('Enter a URL to a markdown file (GitHub, GitLab, Bitbucket, or direct .md URL)'),
        condition: (props) => props.sourceType === 'url',
    },
    {
        name: 'showSource',
        type: 'toggle',
        label: __('Show Source URL'),
        section: __('Settings'),
        defaultValue: true,
        condition: (props) => props.sourceType === 'url',
    },
    {
        name: 'cacheEnabled',
        type: 'toggle',
        label: __('Enable Caching'),
        section: __('Settings'),
        defaultValue: true,
        description: __('Cache the markdown content to improve performance'),
        condition: (props) => props.sourceType === 'url',
    },
];

export default createBlockFromJson(config, { block, save, fields });
