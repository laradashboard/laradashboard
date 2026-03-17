import { createBlockFromJson } from '@lara-builder/factory';
import { __ } from '@lara-builder/i18n';
import config from './block.json';
import block from './block';
import save from './save';

const fields = [
    {
        name: 'headingText',
        type: 'text',
        label: __('Heading'),
        section: __('Content'),
        placeholder: __('e.g. Latest Posts'),
    },
    {
        name: 'postsCount',
        type: 'number',
        label: __('Number of Posts'),
        section: __('Content'),
        min: 1,
        max: 24,
    },
    {
        name: 'columns',
        type: 'select',
        label: __('Columns'),
        section: __('Layout'),
        options: [
            { value: 1, label: __('1 Column') },
            { value: 2, label: __('2 Columns') },
            { value: 3, label: __('3 Columns') },
            { value: 4, label: __('4 Columns') },
        ],
    },
    {
        name: 'layout',
        type: 'select',
        label: __('Layout Style'),
        section: __('Layout'),
        options: [
            { value: 'grid', label: __('Grid') },
            { value: 'list', label: __('List') },
        ],
    },
    {
        name: 'categorySlug',
        type: 'text',
        label: __('Category Slug'),
        section: __('Content'),
        placeholder: __('Leave empty for all categories'),
    },
    {
        name: 'showImage',
        type: 'toggle',
        label: __('Show Featured Image'),
        section: __('Display'),
    },
    {
        name: 'showExcerpt',
        type: 'toggle',
        label: __('Show Excerpt'),
        section: __('Display'),
    },
    {
        name: 'showDate',
        type: 'toggle',
        label: __('Show Date'),
        section: __('Display'),
    },
    {
        name: 'showAuthor',
        type: 'toggle',
        label: __('Show Author'),
        section: __('Display'),
    },
    {
        name: 'interactive',
        type: 'toggle',
        label: __('Interactive Mode'),
        section: __('Interactive Features'),
        help: __('Enable search, filter, sort, and pagination'),
    },
    {
        name: 'showSearch',
        type: 'toggle',
        label: __('Show Search'),
        section: __('Interactive Features'),
        condition: (props) => props.interactive,
    },
    {
        name: 'showCategoryFilter',
        type: 'toggle',
        label: __('Show Category Filter'),
        section: __('Interactive Features'),
        condition: (props) => props.interactive,
    },
    {
        name: 'showSort',
        type: 'toggle',
        label: __('Show Sort'),
        section: __('Interactive Features'),
        condition: (props) => props.interactive,
    },
];

export default createBlockFromJson(config, { block, save, fields });
