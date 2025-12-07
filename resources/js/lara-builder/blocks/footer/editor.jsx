/**
 * Footer Block - Property Editor
 *
 * Note: Typography and colors are controlled by Layout Styles.
 * Link color is specific to this block.
 */

import { __ } from '@lara-builder/i18n';

const FooterEditor = ({ props, updateProps }) => {
    const handleChange = (key, value) => {
        updateProps({ [key]: value });
    };

    return (
        <div className="space-y-4">
            {/* Company Information Section */}
            <Section title={__('Company Information')}>
                <Label>{__('Company Name')}</Label>
                <input
                    type="text"
                    value={props.companyName || ''}
                    onChange={(e) => handleChange('companyName', e.target.value)}
                    className="form-control mb-3"
                    placeholder="Your Company Name"
                />

                <Label>{__('Address')}</Label>
                <textarea
                    value={props.address || ''}
                    onChange={(e) => handleChange('address', e.target.value)}
                    className="form-control mb-3"
                    rows="2"
                    placeholder="123 Street Name, City, Country"
                />

                <div className="grid grid-cols-2 gap-4">
                    <div>
                        <Label>{__('Phone')}</Label>
                        <input
                            type="text"
                            value={props.phone || ''}
                            onChange={(e) => handleChange('phone', e.target.value)}
                            className="form-control"
                            placeholder="+1 234 567 890"
                        />
                    </div>
                    <div>
                        <Label>{__('Email')}</Label>
                        <input
                            type="email"
                            value={props.email || ''}
                            onChange={(e) => handleChange('email', e.target.value)}
                            className="form-control"
                            placeholder="contact@company.com"
                        />
                    </div>
                </div>
            </Section>

            {/* Unsubscribe Section */}
            <Section title={__('Unsubscribe Link')}>
                <Label>{__('Unsubscribe Text')}</Label>
                <input
                    type="text"
                    value={props.unsubscribeText || ''}
                    onChange={(e) => handleChange('unsubscribeText', e.target.value)}
                    className="form-control mb-3"
                    placeholder="Unsubscribe from these emails"
                />

                <Label>{__('Unsubscribe URL')}</Label>
                <input
                    type="text"
                    value={props.unsubscribeUrl || ''}
                    onChange={(e) => handleChange('unsubscribeUrl', e.target.value)}
                    className="form-control"
                    placeholder="#unsubscribe"
                />
            </Section>

            {/* Copyright Section */}
            <Section title={__('Copyright')}>
                <Label>{__('Copyright Text')}</Label>
                <input
                    type="text"
                    value={props.copyright || ''}
                    onChange={(e) => handleChange('copyright', e.target.value)}
                    className="form-control"
                    placeholder="© 2024 Your Company. All rights reserved."
                />
            </Section>

            {/* Style Section */}
            <Section title={__('Style')}>
                <Label>{__('Link Color')}</Label>
                <ColorPicker
                    value={props.linkColor || '#635bff'}
                    onChange={(value) => handleChange('linkColor', value)}
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

// Reusable Color Picker Component
const ColorPicker = ({ value, onChange }) => (
    <div className="flex gap-2">
        <input
            type="color"
            value={value}
            onChange={(e) => onChange(e.target.value)}
            className="w-12 h-9 rounded border border-gray-300 cursor-pointer"
        />
        <input
            type="text"
            value={value}
            onChange={(e) => onChange(e.target.value)}
            className="form-control flex-1 font-mono text-sm"
        />
    </div>
);

export default FooterEditor;
