import { createBlockFromJson } from '@lara-builder/factory';
import { __ } from '@lara-builder/i18n';
import config from './block.json';
import block from './block';
import save from './save';

const fields = [
    {
        name: 'url',
        type: 'text',
        label: __('Markdown URL'),
        section: __('Content'),
        placeholder: 'https://github.com/user/repo/blob/main/README.md',
        description: __('Enter a URL to a markdown file (GitHub, GitLab, Bitbucket, or direct .md URL)'),
    },
    {
        name: 'showSource',
        type: 'toggle',
        label: __('Show Source URL'),
        section: __('Settings'),
        defaultValue: true,
    },
    {
        name: 'cacheEnabled',
        type: 'toggle',
        label: __('Enable Caching'),
        section: __('Settings'),
        defaultValue: true,
        description: __('Cache the markdown content to improve performance'),
    },
];

export default createBlockFromJson(config, { block, save, fields });
