import { createBlockFromJson } from '@lara-builder/factory';
import { __ } from '@lara-builder/i18n';
import config from './block.json';
import block from './block';
import save from './save';

const fields = [
    {
        name: 'language',
        type: 'select',
        label: __('Language'),
        section: __('Settings'),
        options: [
            { value: 'markup', label: 'HTML' },
            { value: 'css', label: 'CSS' },
            { value: 'javascript', label: 'JavaScript' },
            { value: 'typescript', label: 'TypeScript' },
            { value: 'jsx', label: 'JSX (React)' },
            { value: 'tsx', label: 'TSX (React)' },
            { value: 'php', label: 'PHP' },
            { value: 'python', label: 'Python' },
            { value: 'bash', label: 'Bash / Shell' },
            { value: 'sql', label: 'SQL' },
            { value: 'json', label: 'JSON' },
            { value: 'yaml', label: 'YAML' },
            { value: 'scss', label: 'SCSS' },
            { value: 'plaintext', label: 'Plain Text' },
        ],
    },
    {
        name: 'fontSize',
        type: 'select',
        label: __('Font Size'),
        section: __('Typography'),
        options: [
            { value: '12px', label: __('X-Small') + ' (12px)' },
            { value: '14px', label: __('Small') + ' (14px)' },
            { value: '16px', label: __('Medium') + ' (16px)' },
            { value: '18px', label: __('Large') + ' (18px)' },
        ],
    },
    {
        name: 'borderRadius',
        type: 'select',
        label: __('Border Radius'),
        section: __('Style'),
        options: [
            { value: '0', label: __('None') },
            { value: '4px', label: __('Small') + ' (4px)' },
            { value: '6px', label: __('Medium') + ' (6px)' },
            { value: '8px', label: __('Large') + ' (8px)' },
            { value: '12px', label: __('X-Large') + ' (12px)' },
        ],
    },
];

export default createBlockFromJson(config, { block, save, fields });
