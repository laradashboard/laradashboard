/**
 * EditorField - Auto-generate form fields from field definitions
 *
 * Supports all common field types with consistent styling.
 * Handles translation automatically via the __ function.
 *
 * @example Using in editor.jsx:
 * ```jsx
 * import { EditorField, EditorSection } from '@lara-builder/factory';
 *
 * export default function MyBlockEditor({ props, onUpdate }) {
 *     return (
 *         <EditorSection title="Content">
 *             <EditorField
 *                 type="text"
 *                 name="title"
 *                 label="Title"
 *                 value={props.title}
 *                 onChange={(value) => onUpdate({ ...props, title: value })}
 *             />
 *         </EditorSection>
 *     );
 * }
 * ```
 *
 * @example Auto-generate entire editor from fields array:
 * ```jsx
 * import { AutoEditor } from '@lara-builder/factory';
 *
 * // In block definition:
 * fields: [
 *     { type: 'text', name: 'title', label: 'Title' },
 *     { type: 'color', name: 'color', label: 'Text Color' },
 * ]
 *
 * // AutoEditor will render all fields automatically
 * ```
 */

import { __ } from '@lara-builder/i18n';
import { mediaLibrary } from '../services/MediaLibraryService';

/**
 * Reusable Section Component for grouping fields
 */
export const EditorSection = ({ title, children }) => {
    return (
        <div className="pb-4 border-b border-gray-200 dark:border-gray-700 last:border-b-0 last:pb-0">
            <h4 className="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3">
                {__(title)}
            </h4>
            {children}
        </div>
    );
};

/**
 * Reusable Label Component
 */
export const EditorLabel = ({ children, htmlFor }) => (
    <label
        htmlFor={htmlFor}
        className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5"
    >
        {typeof children === 'string' ? __(children) : children}
    </label>
);

/**
 * Color Picker with hex input
 */
const ColorField = ({ value, onChange, id }) => (
    <div className="flex gap-2">
        <input
            type="color"
            id={id}
            value={value || '#000000'}
            onChange={(e) => onChange(e.target.value)}
            className="w-12 h-9 rounded border border-gray-300 dark:border-gray-600 cursor-pointer"
        />
        <input
            type="text"
            value={value || ''}
            onChange={(e) => onChange(e.target.value)}
            placeholder="#000000"
            className="form-control flex-1 font-mono text-sm"
        />
    </div>
);

/**
 * Range/Slider field
 */
const RangeField = ({ value, onChange, min = 0, max = 100, step = 1, id, showValue = true }) => (
    <div className="flex items-center gap-3">
        <input
            type="range"
            id={id}
            value={value || min}
            onChange={(e) => onChange(parseFloat(e.target.value))}
            min={min}
            max={max}
            step={step}
            className="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700"
        />
        {showValue && (
            <span className="text-sm text-gray-600 dark:text-gray-400 min-w-[3rem] text-right">
                {value || min}
            </span>
        )}
    </div>
);

/**
 * Main EditorField component
 *
 * @param {Object} props
 * @param {string} props.type - Field type (text, textarea, select, color, checkbox, range, url, number, image)
 * @param {string} props.name - Field name (used for id)
 * @param {string} props.label - Display label (will be translated)
 * @param {any} props.value - Current value
 * @param {Function} props.onChange - Change handler (value) => void
 * @param {Array} [props.options] - Options for select fields [{ value, label }]
 * @param {string} [props.placeholder] - Placeholder text
 * @param {number} [props.min] - Min value for range/number
 * @param {number} [props.max] - Max value for range/number
 * @param {number} [props.step] - Step value for range/number
 * @param {number} [props.rows] - Rows for textarea
 * @param {string} [props.help] - Help text shown below field
 * @param {boolean} [props.required] - Whether field is required
 * @param {Function} [props.onImageUpload] - Image upload handler
 * @param {boolean} [props.showLabel=true] - Whether to show label
 * @param {string} [props.className] - Additional CSS classes
 */
export const EditorField = ({
    type,
    name,
    label,
    value,
    onChange,
    options = [],
    placeholder,
    min,
    max,
    step,
    rows = 3,
    help,
    required = false,
    showLabel = true,
    className = '',
}) => {
    const id = `field-${name}`;
    const translatedPlaceholder = placeholder ? __(placeholder) : undefined;

    const renderField = () => {
        switch (type) {
            case 'text':
                return (
                    <input
                        type="text"
                        id={id}
                        value={value || ''}
                        onChange={(e) => onChange(e.target.value)}
                        placeholder={translatedPlaceholder}
                        required={required}
                        className="form-control"
                    />
                );

            case 'textarea':
                return (
                    <textarea
                        id={id}
                        value={value || ''}
                        onChange={(e) => onChange(e.target.value)}
                        placeholder={translatedPlaceholder}
                        rows={rows}
                        required={required}
                        className="form-control"
                    />
                );

            case 'number':
                return (
                    <input
                        type="number"
                        id={id}
                        value={value ?? ''}
                        onChange={(e) => onChange(e.target.value ? parseFloat(e.target.value) : '')}
                        placeholder={translatedPlaceholder}
                        min={min}
                        max={max}
                        step={step}
                        required={required}
                        className="form-control"
                    />
                );

            case 'url':
                return (
                    <input
                        type="url"
                        id={id}
                        value={value || ''}
                        onChange={(e) => onChange(e.target.value)}
                        placeholder={translatedPlaceholder || 'https://...'}
                        required={required}
                        className="form-control"
                    />
                );

            case 'email':
                return (
                    <input
                        type="email"
                        id={id}
                        value={value || ''}
                        onChange={(e) => onChange(e.target.value)}
                        placeholder={translatedPlaceholder}
                        required={required}
                        className="form-control"
                    />
                );

            case 'select':
                return (
                    <select
                        id={id}
                        value={value || ''}
                        onChange={(e) => onChange(e.target.value)}
                        required={required}
                        className="form-control"
                    >
                        {options.map((opt) => (
                            <option key={opt.value} value={opt.value}>
                                {__(opt.label)}
                            </option>
                        ))}
                    </select>
                );

            case 'color':
                return <ColorField value={value} onChange={onChange} id={id} />;

            case 'checkbox':
                return (
                    <label className="flex items-center gap-2 cursor-pointer">
                        <input
                            type="checkbox"
                            id={id}
                            checked={value || false}
                            onChange={(e) => onChange(e.target.checked)}
                            className="rounded border-gray-300 dark:border-gray-600 text-primary focus:ring-primary"
                        />
                        <span className="text-sm text-gray-700 dark:text-gray-300">
                            {__(label)}
                        </span>
                    </label>
                );

            case 'toggle':
                return (
                    <label className="relative inline-flex items-center cursor-pointer">
                        <input
                            type="checkbox"
                            id={id}
                            checked={value || false}
                            onChange={(e) => onChange(e.target.checked)}
                            className="sr-only peer"
                        />
                        <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/20 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
                        <span className="ml-3 text-sm text-gray-700 dark:text-gray-300">
                            {__(label)}
                        </span>
                    </label>
                );

            case 'range':
                return (
                    <RangeField
                        value={value}
                        onChange={onChange}
                        min={min}
                        max={max}
                        step={step}
                        id={id}
                    />
                );

            case 'image':
                const handleSelectImage = async () => {
                    try {
                        const file = await mediaLibrary.selectImage();
                        if (file) {
                            onChange(file.url);
                        }
                    } catch (error) {
                        // Selection cancelled
                    }
                };

                return (
                    <div className="space-y-2">
                        {value ? (
                            <div className="relative group">
                                <img
                                    src={value}
                                    alt=""
                                    className="w-full h-32 object-contain rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800"
                                />
                                <button
                                    type="button"
                                    onClick={() => onChange('')}
                                    className="absolute top-2 right-2 p-1 bg-red-500 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity"
                                    title={__('Remove')}
                                >
                                    <iconify-icon icon="mdi:close" width="14" height="14"></iconify-icon>
                                </button>
                            </div>
                        ) : (
                            <div
                                onClick={handleSelectImage}
                                className="flex flex-col items-center justify-center p-6 bg-gray-50 dark:bg-gray-800 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:border-primary hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                            >
                                <iconify-icon icon="mdi:image-plus" className="text-3xl text-gray-400 mb-2"></iconify-icon>
                                <p className="text-sm text-gray-500 dark:text-gray-400">{__('Click to select image')}</p>
                            </div>
                        )}

                        {value && (
                            <button
                                type="button"
                                onClick={handleSelectImage}
                                className="btn btn-default w-full flex items-center justify-center gap-2"
                            >
                                <iconify-icon icon="mdi:image-edit" width="16" height="16"></iconify-icon>
                                {__('Change Image')}
                            </button>
                        )}

                        <details className="mt-2">
                            <summary className="text-xs text-gray-500 dark:text-gray-400 cursor-pointer hover:text-gray-700 dark:hover:text-gray-300">
                                {__('Or enter URL manually')}
                            </summary>
                            <input
                                type="url"
                                value={value || ''}
                                onChange={(e) => onChange(e.target.value)}
                                placeholder="https://..."
                                className="form-control mt-2"
                            />
                        </details>
                    </div>
                );

            case 'align':
                return (
                    <div className="flex gap-1 bg-gray-100 dark:bg-gray-800 rounded-lg p-1">
                        {['left', 'center', 'right', 'justify'].map((align) => (
                            <button
                                key={align}
                                type="button"
                                onClick={() => onChange(align)}
                                className={`flex-1 p-2 rounded-md transition-colors ${
                                    value === align
                                        ? 'bg-white dark:bg-gray-700 shadow-sm text-primary'
                                        : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'
                                }`}
                                title={__(align.charAt(0).toUpperCase() + align.slice(1))}
                            >
                                <iconify-icon
                                    icon={`mdi:format-align-${align}`}
                                    width="18"
                                    height="18"
                                    className="mx-auto"
                                ></iconify-icon>
                            </button>
                        ))}
                    </div>
                );

            case 'date':
                return (
                    <input
                        type="date"
                        id={id}
                        value={value || ''}
                        onChange={(e) => onChange(e.target.value)}
                        required={required}
                        className="form-control"
                    />
                );

            case 'time':
                return (
                    <input
                        type="time"
                        id={id}
                        value={value || '23:59'}
                        onChange={(e) => onChange(e.target.value)}
                        required={required}
                        className="form-control"
                    />
                );

            case 'info':
                return (
                    <div className="flex items-start gap-2 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-md">
                        <iconify-icon
                            icon="mdi:information"
                            width="18"
                            height="18"
                            class="text-blue-500 flex-shrink-0 mt-0.5"
                        ></iconify-icon>
                        <p className="text-sm text-blue-700 dark:text-blue-300">
                            {__(value || '')}
                        </p>
                    </div>
                );

            default:
                console.warn(`[EditorField] Unknown field type: ${type}`);
                return (
                    <input
                        type="text"
                        id={id}
                        value={value || ''}
                        onChange={(e) => onChange(e.target.value)}
                        className="form-control"
                    />
                );
        }
    };

    // For checkbox/toggle, label is rendered inline
    const inlineLabel = type === 'checkbox' || type === 'toggle';

    return (
        <div className={`mb-3 last:mb-0 ${className}`}>
            {showLabel && label && !inlineLabel && (
                <EditorLabel htmlFor={id}>{label}</EditorLabel>
            )}
            {renderField()}
            {help && (
                <p className="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {__(help)}
                </p>
            )}
        </div>
    );
};

/**
 * Get nested value from object using dot notation
 * @param {Object} obj - The object to get value from
 * @param {string} path - Dot notation path (e.g., "links.facebook")
 * @returns {any} The value at the path
 */
const getNestedValue = (obj, path) => {
    if (!path.includes('.')) {
        return obj?.[path];
    }
    return path.split('.').reduce((acc, key) => acc?.[key], obj);
};

/**
 * Set nested value in object using dot notation (immutable)
 * @param {Object} obj - The object to update
 * @param {string} path - Dot notation path (e.g., "links.facebook")
 * @param {any} value - The value to set
 * @returns {Object} New object with updated value
 */
const setNestedValue = (obj, path, value) => {
    if (!path.includes('.')) {
        return { ...obj, [path]: value };
    }

    const keys = path.split('.');
    const result = { ...obj };
    let current = result;

    for (let i = 0; i < keys.length - 1; i++) {
        const key = keys[i];
        current[key] = { ...current[key] };
        current = current[key];
    }

    current[keys[keys.length - 1]] = value;
    return result;
};

/**
 * Auto-generate an editor from a fields array
 *
 * This component reads the `fields` from block definition
 * and automatically renders all the form fields.
 *
 * Supports nested field names using dot notation (e.g., "links.facebook")
 * Supports linkedFields to update multiple props when a field changes
 *
 * @param {Object} props
 * @param {Array} props.fields - Field definitions array
 * @param {Object} props.blockProps - Current block props
 * @param {Function} props.onUpdate - Update handler
 */
export const AutoEditor = ({ fields = [], blockProps, onUpdate }) => {
    const handleChange = (name, value, field) => {
        let newProps = setNestedValue(blockProps, name, value);

        // Handle linkedFields - update related props when this field changes
        if (field?.linkedFields) {
            Object.entries(field.linkedFields).forEach(([linkedName, transformer]) => {
                const linkedValue = typeof transformer === 'function'
                    ? transformer(value)
                    : transformer;
                newProps = setNestedValue(newProps, linkedName, linkedValue);
            });
        }

        onUpdate(newProps);
    };

    // Group fields by section
    const groupedFields = fields.reduce((acc, field) => {
        const section = field.section || 'Content';
        if (!acc[section]) {
            acc[section] = [];
        }
        acc[section].push(field);
        return acc;
    }, {});

    return (
        <div className="space-y-4">
            {Object.entries(groupedFields).map(([section, sectionFields]) => (
                <EditorSection key={section} title={section}>
                    {sectionFields.map((field) => (
                        <EditorField
                            key={field.name}
                            type={field.type}
                            name={field.name}
                            label={field.label}
                            value={getNestedValue(blockProps, field.name)}
                            onChange={(value) => handleChange(field.name, value, field)}
                            options={field.options}
                            placeholder={field.placeholder}
                            min={field.min}
                            max={field.max}
                            step={field.step}
                            rows={field.rows}
                            help={field.help}
                            required={field.required}
                        />
                    ))}
                </EditorSection>
            ))}
        </div>
    );
};

/**
 * Create an auto-generated editor component from fields
 *
 * @param {Array} fields - Field definitions
 * @returns {React.ComponentType} Editor component
 */
export const createAutoEditor = (fields) => {
    return function GeneratedEditor({ props, onUpdate }) {
        return (
            <AutoEditor
                fields={fields}
                blockProps={props}
                onUpdate={onUpdate}
            />
        );
    };
};

export default EditorField;
