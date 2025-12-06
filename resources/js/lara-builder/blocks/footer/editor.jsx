import React from 'react';

const FooterEditor = ({ props, updateProps }) => {
    const handleChange = (key, value) => {
        updateProps({ [key]: value });
    };

    return (
        <div className="space-y-6">
            {/* Company Information Section */}
            <div className="space-y-4">
                <h3 className="text-sm font-semibold text-gray-900 dark:text-gray-100">
                    Company Information
                </h3>

                <div>
                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Company Name
                    </label>
                    <input
                        type="text"
                        value={props.companyName || ''}
                        onChange={(e) => handleChange('companyName', e.target.value)}
                        className="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"
                        placeholder="Your Company Name"
                    />
                </div>

                <div>
                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Address
                    </label>
                    <textarea
                        value={props.address || ''}
                        onChange={(e) => handleChange('address', e.target.value)}
                        className="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"
                        rows="2"
                        placeholder="123 Street Name, City, Country"
                    />
                </div>

                <div className="grid grid-cols-2 gap-4">
                    <div>
                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Phone
                        </label>
                        <input
                            type="text"
                            value={props.phone || ''}
                            onChange={(e) => handleChange('phone', e.target.value)}
                            className="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"
                            placeholder="+1 234 567 890"
                        />
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Email
                        </label>
                        <input
                            type="email"
                            value={props.email || ''}
                            onChange={(e) => handleChange('email', e.target.value)}
                            className="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"
                            placeholder="contact@company.com"
                        />
                    </div>
                </div>
            </div>

            {/* Unsubscribe Section */}
            <div className="space-y-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <h3 className="text-sm font-semibold text-gray-900 dark:text-gray-100">
                    Unsubscribe Link
                </h3>

                <div>
                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Unsubscribe Text
                    </label>
                    <input
                        type="text"
                        value={props.unsubscribeText || ''}
                        onChange={(e) => handleChange('unsubscribeText', e.target.value)}
                        className="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"
                        placeholder="Unsubscribe from these emails"
                    />
                </div>

                <div>
                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Unsubscribe URL
                    </label>
                    <input
                        type="text"
                        value={props.unsubscribeUrl || ''}
                        onChange={(e) => handleChange('unsubscribeUrl', e.target.value)}
                        className="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"
                        placeholder="#unsubscribe"
                    />
                </div>
            </div>

            {/* Copyright Section */}
            <div className="space-y-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <h3 className="text-sm font-semibold text-gray-900 dark:text-gray-100">
                    Copyright
                </h3>

                <div>
                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Copyright Text
                    </label>
                    <input
                        type="text"
                        value={props.copyright || ''}
                        onChange={(e) => handleChange('copyright', e.target.value)}
                        className="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"
                        placeholder="© 2024 Your Company. All rights reserved."
                    />
                </div>
            </div>

            {/* Style Settings Section */}
            <div className="space-y-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <h3 className="text-sm font-semibold text-gray-900 dark:text-gray-100">
                    Style Settings
                </h3>

                <div>
                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Alignment
                    </label>
                    <select
                        value={props.align || 'center'}
                        onChange={(e) => handleChange('align', e.target.value)}
                        className="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"
                    >
                        <option value="left">Left</option>
                        <option value="center">Center</option>
                        <option value="right">Right</option>
                    </select>
                </div>

                <div className="grid grid-cols-2 gap-4">
                    <div>
                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Text Color
                        </label>
                        <div className="flex gap-2">
                            <input
                                type="color"
                                value={props.textColor || '#6b7280'}
                                onChange={(e) => handleChange('textColor', e.target.value)}
                                className="h-10 w-12 border border-gray-300 dark:border-gray-600 rounded cursor-pointer"
                            />
                            <input
                                type="text"
                                value={props.textColor || '#6b7280'}
                                onChange={(e) => handleChange('textColor', e.target.value)}
                                className="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 font-mono text-sm"
                            />
                        </div>
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Link Color
                        </label>
                        <div className="flex gap-2">
                            <input
                                type="color"
                                value={props.linkColor || '#635bff'}
                                onChange={(e) => handleChange('linkColor', e.target.value)}
                                className="h-10 w-12 border border-gray-300 dark:border-gray-600 rounded cursor-pointer"
                            />
                            <input
                                type="text"
                                value={props.linkColor || '#635bff'}
                                onChange={(e) => handleChange('linkColor', e.target.value)}
                                className="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 font-mono text-sm"
                            />
                        </div>
                    </div>
                </div>

                <div>
                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Font Size
                    </label>
                    <input
                        type="text"
                        value={props.fontSize || '12px'}
                        onChange={(e) => handleChange('fontSize', e.target.value)}
                        className="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"
                        placeholder="12px"
                    />
                    <p className="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Examples: 12px, 1rem, 0.875em
                    </p>
                </div>
            </div>
        </div>
    );
};

export default FooterEditor;
