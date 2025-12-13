import { createBlockFromJson } from '@lara-builder/factory';
import { __ } from '@lara-builder/i18n';
import config from './block.json';
import block from './block';
import save from './save';

/**
 * Default font sizes for each heading level
 * These provide sensible typography defaults when switching heading levels
 */
export const HEADING_FONT_SIZES = {
    h1: '32px',
    h2: '28px',
    h3: '24px',
    h4: '20px',
    h5: '18px',
    h6: '16px',
};

const fields = [
    {
        name: 'level',
        type: 'select',
        label: __('Heading Level'),
        section: __('Content'),
        options: [
            { value: 'h1', label: __('H1 - Main Heading') },
            { value: 'h2', label: __('H2 - Section Heading') },
            { value: 'h3', label: __('H3 - Subsection') },
            { value: 'h4', label: __('H4 - Minor Heading') },
            { value: 'h5', label: __('H5 - Small Heading') },
            { value: 'h6', label: __('H6 - Smallest Heading') },
        ],
        // When level changes, also update fontSize to the default for that level
        linkedFields: {
            fontSize: (level) => HEADING_FONT_SIZES[level] || '24px',
        },
    },
];

export default createBlockFromJson(config, { block, save, fields });
