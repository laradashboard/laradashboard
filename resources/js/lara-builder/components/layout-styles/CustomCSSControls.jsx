/**
 * CustomCSS Controls Component
 *
 * Provides a textarea for users to add custom CSS to any block.
 * Matches the style of other layout controls.
 */

import { useState, useCallback } from 'react';
import { __ } from '@lara-builder/i18n';

const CustomCSSControls = ({ customCSS = '', customClass = '', onChange, onClassChange }) => {
    const [localCSS, setLocalCSS] = useState(customCSS);
    const [localClass, setLocalClass] = useState(customClass);

    // Handle CSS change
    const handleCSSChange = useCallback((e) => {
        const newValue = e.target.value;
        setLocalCSS(newValue);
        onChange(newValue);
    }, [onChange]);

    // Handle class change
    const handleClassChange = useCallback((e) => {
        const newValue = e.target.value;
        setLocalClass(newValue);
        onClassChange?.(newValue);
    }, [onClassChange]);

    // Example CSS snippets
    const snippets = [
        { label: __('Shadow'), css: 'box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);' },
        { label: __('Rounded'), css: 'border-radius: 12px;' },
        { label: __('Border'), css: 'border: 2px solid #e5e7eb;' },
        { label: __('Opacity'), css: 'opacity: 0.9;' },
    ];

    const insertSnippet = (css) => {
        const newValue = localCSS ? `${localCSS}\n${css}` : css;
        setLocalCSS(newValue);
        onChange(newValue);
    };

    return (
        <div className="space-y-4">
            {/* Quick snippets */}
            <div>
                <label className="block text-xs font-medium text-gray-600 mb-2">
                    {__('Quick Add')}
                </label>
                <div className="flex flex-wrap gap-1.5">
                    {snippets.map((snippet) => (
                        <button
                            key={snippet.label}
                            type="button"
                            onClick={() => insertSnippet(snippet.css)}
                            className="px-2 py-1 text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                        >
                            {snippet.label}
                        </button>
                    ))}
                </div>
            </div>

            {/* CSS textarea */}
            <div>
                <label className="block text-xs font-medium text-gray-600 mb-1.5">
                    {__('CSS Properties')}
                </label>
                <textarea
                    value={localCSS}
                    onChange={handleCSSChange}
                    placeholder="e.g., box-shadow: 0 4px 6px rgba(0,0,0,0.1);"
                    className="w-full h-24 px-3 py-2 text-xs font-mono bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md focus:ring-2 focus:ring-primary/20 focus:border-primary resize-y"
                    spellCheck={false}
                />
                <p className="mt-1 text-xs text-gray-400">
                    {__('CSS properties separated by semicolons')}
                </p>
            </div>

            {/* Custom class input */}
            <div>
                <label className="block text-xs font-medium text-gray-600 mb-1.5">
                    {__('Custom Class')}
                </label>
                <input
                    type="text"
                    value={localClass}
                    onChange={handleClassChange}
                    placeholder="my-custom-class"
                    className="w-full px-3 py-2 text-xs font-mono bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md focus:ring-2 focus:ring-primary/20 focus:border-primary"
                />
                <p className="mt-1 text-xs text-gray-400">
                    {__('Added alongside')} <code className="px-1 py-0.5 bg-gray-100 rounded">lb-{'{type}'}</code>
                </p>
            </div>
        </div>
    );
};

export default CustomCSSControls;
