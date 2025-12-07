/**
 * Text Block - Property Editor
 */

import { __ } from '@lara-builder/i18n';

const TextBlockEditor = ({ props, onUpdate }) => {
    const handleChange = (field, value) => {
        onUpdate({ ...props, [field]: value });
    };

    return (
        <div className="space-y-4">
            <Section title={__('Content')}>
                <Label>{__('Text Content')}</Label>
                <textarea
                    value={props.content || ''}
                    onChange={(e) => handleChange('content', e.target.value)}
                    placeholder={__('Enter your text...')}
                    className="form-control"
                    rows={4}
                    style={{ resize: 'vertical' }}
                />
            </Section>
        </div>
    );
};

// Reusable Section Component
const Section = ({ title, children }) => (
    <div className="pb-4 border-b border-gray-200 dark:border-gray-700 last:border-b-0 last:pb-0">
        <h4 className="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3">
            {title}
        </h4>
        {children}
    </div>
);

// Reusable Label Component
const Label = ({ children }) => (
    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
        {children}
    </label>
);

export default TextBlockEditor;
