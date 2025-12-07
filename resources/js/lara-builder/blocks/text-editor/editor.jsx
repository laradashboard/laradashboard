import { useState } from 'react';
import { __ } from '@lara-builder/i18n';

/**
 * TextEditorPropertyEditor - Property editor for the Text Editor block
 */
const TextEditorPropertyEditor = ({ props, onUpdate }) => {
    const [localProps, setLocalProps] = useState(props);

    const handleChange = (key, value) => {
        const newProps = { ...localProps, [key]: value };
        setLocalProps(newProps);
        onUpdate(newProps);
    };

    const alignmentLabels = {
        left: __('Left'),
        center: __('Center'),
        right: __('Right'),
        justify: __('Justify'),
    };

    return (
        <div className="space-y-4">
            <div>
                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {__('Alignment')}
                </label>
                <div className="flex gap-2">
                    {['left', 'center', 'right', 'justify'].map((align) => (
                        <button
                            key={align}
                            onClick={() => handleChange('align', align)}
                            className={`flex-1 px-3 py-2 text-sm rounded border ${
                                localProps.align === align
                                    ? 'bg-primary/10 border-primary text-primary'
                                    : 'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300'
                            } hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors`}
                        >
                            {alignmentLabels[align]}
                        </button>
                    ))}
                </div>
            </div>

            <div>
                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {__('Text Color')}
                </label>
                <div className="flex gap-2">
                    <input
                        type="color"
                        value={localProps.color || '#333333'}
                        onChange={(e) => handleChange('color', e.target.value)}
                        className="h-10 w-20 rounded border border-gray-300 dark:border-gray-600 cursor-pointer"
                    />
                    <input
                        type="text"
                        value={localProps.color || '#333333'}
                        onChange={(e) => handleChange('color', e.target.value)}
                        className="flex-1 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"
                        placeholder="#333333"
                    />
                </div>
            </div>

            <div>
                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {__('Font Size')}
                </label>
                <div className="flex gap-2">
                    <input
                        type="range"
                        min="10"
                        max="48"
                        value={parseInt(localProps.fontSize) || 16}
                        onChange={(e) => handleChange('fontSize', `${e.target.value}px`)}
                        className="flex-1"
                    />
                    <input
                        type="text"
                        value={localProps.fontSize || '16px'}
                        onChange={(e) => handleChange('fontSize', e.target.value)}
                        className="w-20 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-center"
                        placeholder="16px"
                    />
                </div>
            </div>

            <div>
                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {__('Line Height')}
                </label>
                <div className="flex gap-2">
                    <input
                        type="range"
                        min="1"
                        max="3"
                        step="0.1"
                        value={parseFloat(localProps.lineHeight) || 1.6}
                        onChange={(e) => handleChange('lineHeight', e.target.value)}
                        className="flex-1"
                    />
                    <input
                        type="text"
                        value={localProps.lineHeight || '1.6'}
                        onChange={(e) => handleChange('lineHeight', e.target.value)}
                        className="w-20 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-center"
                        placeholder="1.6"
                    />
                </div>
            </div>

            <div className="pt-4 border-t border-gray-200 dark:border-gray-700">
                <p className="text-xs text-gray-500 dark:text-gray-400">
                    {__('Click on the text editor in the canvas to access rich formatting options like bold, italic, lists, and links.')}
                </p>
            </div>
        </div>
    );
};

export default TextEditorPropertyEditor;
