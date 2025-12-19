/**
 * Table of Contents Block
 *
 * Auto-generates a table of contents from page headings.
 */

import { createBlockFromJson } from '@lara-builder/factory';
import { __ } from '@lara-builder/i18n';
import config from './block.json';
import block from './block';
import save from './save';

const fields = [
    {
        name: 'title',
        type: 'text',
        label: __('Title'),
        section: __('Content'),
        placeholder: __('Table of Contents'),
    },
    {
        name: 'showTitle',
        type: 'toggle',
        label: __('Show Title'),
        section: __('Content'),
    },
    {
        name: 'minLevel',
        type: 'select',
        label: __('Minimum Heading Level'),
        section: __('Content'),
        options: [
            { value: 'h1', label: 'H1' },
            { value: 'h2', label: 'H2' },
            { value: 'h3', label: 'H3' },
            { value: 'h4', label: 'H4' },
            { value: 'h5', label: 'H5' },
            { value: 'h6', label: 'H6' },
        ],
    },
    {
        name: 'maxLevel',
        type: 'select',
        label: __('Maximum Heading Level'),
        section: __('Content'),
        options: [
            { value: 'h1', label: 'H1' },
            { value: 'h2', label: 'H2' },
            { value: 'h3', label: 'H3' },
            { value: 'h4', label: 'H4' },
            { value: 'h5', label: 'H5' },
            { value: 'h6', label: 'H6' },
        ],
    },
    {
        name: 'listStyle',
        type: 'select',
        label: __('List Style'),
        section: __('Style'),
        options: [
            { value: 'bullet', label: __('Bullet') },
            { value: 'number', label: __('Numbered') },
            { value: 'none', label: __('None') },
        ],
    },
    {
        name: 'backgroundColor',
        type: 'color',
        label: __('Background Color'),
        section: __('Style'),
    },
    {
        name: 'borderColor',
        type: 'color',
        label: __('Border Color'),
        section: __('Style'),
    },
    {
        name: 'titleColor',
        type: 'color',
        label: __('Title Color'),
        section: __('Style'),
    },
    {
        name: 'linkColor',
        type: 'color',
        label: __('Link Color'),
        section: __('Style'),
    },
];

export default createBlockFromJson(config, { block, save, fields });
