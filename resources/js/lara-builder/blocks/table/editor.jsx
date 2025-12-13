/**
 * Table Block - Property Editor
 *
 * Renders the property fields for the table block in the properties panel.
 */

import { __ } from '@lara-builder/i18n';

const TableBlockEditor = ({ props, onUpdate }) => {
    const handleChange = (field, value) => {
        onUpdate({ ...props, [field]: value });
    };

    const handleHeaderChange = (index, value) => {
        const newHeaders = [...(props.headers || [])];
        newHeaders[index] = value;
        handleChange('headers', newHeaders);
    };

    const handleCellChange = (rowIndex, cellIndex, value) => {
        const newRows = [...(props.rows || [])];
        newRows[rowIndex] = [...newRows[rowIndex]];
        newRows[rowIndex][cellIndex] = value;
        handleChange('rows', newRows);
    };

    const addColumn = () => {
        const newHeaders = [...(props.headers || []), 'New Column'];
        const newRows = (props.rows || []).map(row => [...row, '']);
        onUpdate({ ...props, headers: newHeaders, rows: newRows });
    };

    const removeColumn = (index) => {
        const newHeaders = [...(props.headers || [])];
        newHeaders.splice(index, 1);
        const newRows = (props.rows || []).map(row => {
            const newRow = [...row];
            newRow.splice(index, 1);
            return newRow;
        });
        onUpdate({ ...props, headers: newHeaders, rows: newRows });
    };

    const addRow = () => {
        const newRows = [...(props.rows || [])];
        const columnCount = (props.headers || []).length;
        newRows.push(Array(columnCount).fill(''));
        handleChange('rows', newRows);
    };

    const removeRow = (index) => {
        const newRows = [...(props.rows || [])];
        newRows.splice(index, 1);
        handleChange('rows', newRows);
    };

    const sectionStyle = {
        marginBottom: '16px',
    };

    const labelStyle = {
        display: 'block',
        fontSize: '13px',
        fontWeight: '500',
        color: '#374151',
        marginBottom: '6px',
    };

    const sectionTitleStyle = {
        fontSize: '12px',
        fontWeight: '600',
        color: '#6b7280',
        textTransform: 'uppercase',
        letterSpacing: '0.5px',
        marginBottom: '12px',
        paddingBottom: '8px',
        borderBottom: '1px solid #e5e7eb',
    };

    const tableEditorStyle = {
        width: '100%',
        borderCollapse: 'collapse',
        fontSize: '12px',
        marginBottom: '12px',
    };

    const tableCellInputStyle = {
        width: '100%',
        padding: '6px',
        fontSize: '12px',
        border: '1px solid #d1d5db',
        borderRadius: '4px',
    };

    const buttonStyle = {
        padding: '6px 12px',
        fontSize: '12px',
        border: '1px solid #d1d5db',
        borderRadius: '4px',
        cursor: 'pointer',
        backgroundColor: '#ffffff',
    };

    const deleteButtonStyle = {
        ...buttonStyle,
        color: '#dc2626',
        borderColor: '#dc2626',
        padding: '4px 8px',
    };

    return (
        <div>
            {/* Table Data Section */}
            <div style={sectionStyle}>
                <div style={sectionTitleStyle}>{__('Table Data')}</div>

                {/* Show Header Toggle */}
                <div style={{ marginBottom: '12px' }}>
                    <label style={{ display: 'flex', alignItems: 'center', cursor: 'pointer' }}>
                        <input
                            type="checkbox"
                            checked={props.showHeader !== false}
                            onChange={(e) => handleChange('showHeader', e.target.checked)}
                            style={{ marginRight: '8px' }}
                        />
                        <span style={labelStyle}>{__('Show Header Row')}</span>
                    </label>
                </div>

                {/* Table Editor */}
                <div style={{ overflowX: 'auto', marginBottom: '12px' }}>
                    <table style={tableEditorStyle}>
                        {props.showHeader !== false && (
                            <thead>
                                <tr>
                                    {(props.headers || []).map((header, index) => (
                                        <th key={index} style={{ padding: '4px', position: 'relative' }}>
                                            <input
                                                type="text"
                                                value={header}
                                                onChange={(e) => handleHeaderChange(index, e.target.value)}
                                                style={tableCellInputStyle}
                                                placeholder={`Column ${index + 1}`}
                                            />
                                            <button
                                                onClick={() => removeColumn(index)}
                                                style={{ ...deleteButtonStyle, marginTop: '4px', width: '100%' }}
                                                title="Remove column"
                                            >
                                                <iconify-icon icon="mdi:delete" width="14" height="14" />
                                            </button>
                                        </th>
                                    ))}
                                </tr>
                            </thead>
                        )}
                        <tbody>
                            {(props.rows || []).map((row, rowIndex) => (
                                <tr key={rowIndex}>
                                    {row.map((cell, cellIndex) => (
                                        <td key={cellIndex} style={{ padding: '4px' }}>
                                            <input
                                                type="text"
                                                value={cell}
                                                onChange={(e) => handleCellChange(rowIndex, cellIndex, e.target.value)}
                                                style={tableCellInputStyle}
                                                placeholder={`Cell ${rowIndex + 1}-${cellIndex + 1}`}
                                            />
                                        </td>
                                    ))}
                                    <td style={{ padding: '4px', width: '40px' }}>
                                        <button
                                            onClick={() => removeRow(rowIndex)}
                                            style={deleteButtonStyle}
                                            title="Remove row"
                                        >
                                            <iconify-icon icon="mdi:delete" width="14" height="14" />
                                        </button>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                {/* Add Row/Column Buttons */}
                <div style={{ display: 'flex', gap: '8px' }}>
                    <button
                        onClick={addRow}
                        className="btn btn-default"
                        style={{ flex: 1, fontSize: '12px', padding: '8px' }}
                    >
                        <iconify-icon icon="mdi:plus" width="16" height="16" /> {__('Add Row')}
                    </button>
                    <button
                        onClick={addColumn}
                        className="btn btn-default"
                        style={{ flex: 1, fontSize: '12px', padding: '8px' }}
                    >
                        <iconify-icon icon="mdi:plus" width="16" height="16" /> {__('Add Column')}
                    </button>
                </div>
            </div>

            {/* Style Section */}
            <div style={sectionStyle}>
                <div style={sectionTitleStyle}>{__('Style')}</div>

                <label style={labelStyle}>{__('Font Size')}</label>
                <select
                    value={props.fontSize || '14px'}
                    onChange={(e) => handleChange('fontSize', e.target.value)}
                    className="form-control mb-3"
                >
                    <option value="12px">{__('Small')} (12px)</option>
                    <option value="14px">{__('Medium')} (14px)</option>
                    <option value="16px">{__('Large')} (16px)</option>
                    <option value="18px">{__('X-Large')} (18px)</option>
                </select>

                <label style={labelStyle}>{__('Cell Padding')}</label>
                <select
                    value={props.cellPadding || '12px'}
                    onChange={(e) => handleChange('cellPadding', e.target.value)}
                    className="form-control"
                >
                    <option value="8px">{__('Small')} (8px)</option>
                    <option value="12px">{__('Medium')} (12px)</option>
                    <option value="16px">{__('Large')} (16px)</option>
                    <option value="20px">{__('X-Large')} (20px)</option>
                </select>
            </div>

            {/* Colors Section */}
            <div style={sectionStyle}>
                <div style={sectionTitleStyle}>{__('Colors')}</div>

                <label style={labelStyle}>{__('Header Background Color')}</label>
                <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '12px' }}>
                    <input
                        type="color"
                        value={props.headerBgColor || '#f1f5f9'}
                        onChange={(e) => handleChange('headerBgColor', e.target.value)}
                        style={{
                            width: '40px',
                            height: '36px',
                            padding: '2px',
                            border: '1px solid #d1d5db',
                            borderRadius: '4px',
                            cursor: 'pointer',
                        }}
                    />
                    <input
                        type="text"
                        value={props.headerBgColor || '#f1f5f9'}
                        onChange={(e) => handleChange('headerBgColor', e.target.value)}
                        className="form-control"
                        style={{ flex: 1 }}
                    />
                </div>

                <label style={labelStyle}>{__('Header Text Color')}</label>
                <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '12px' }}>
                    <input
                        type="color"
                        value={props.headerTextColor || '#1e293b'}
                        onChange={(e) => handleChange('headerTextColor', e.target.value)}
                        style={{
                            width: '40px',
                            height: '36px',
                            padding: '2px',
                            border: '1px solid #d1d5db',
                            borderRadius: '4px',
                            cursor: 'pointer',
                        }}
                    />
                    <input
                        type="text"
                        value={props.headerTextColor || '#1e293b'}
                        onChange={(e) => handleChange('headerTextColor', e.target.value)}
                        className="form-control"
                        style={{ flex: 1 }}
                    />
                </div>

                <label style={labelStyle}>{__('Border Color')}</label>
                <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                    <input
                        type="color"
                        value={props.borderColor || '#e2e8f0'}
                        onChange={(e) => handleChange('borderColor', e.target.value)}
                        style={{
                            width: '40px',
                            height: '36px',
                            padding: '2px',
                            border: '1px solid #d1d5db',
                            borderRadius: '4px',
                            cursor: 'pointer',
                        }}
                    />
                    <input
                        type="text"
                        value={props.borderColor || '#e2e8f0'}
                        onChange={(e) => handleChange('borderColor', e.target.value)}
                        className="form-control"
                        style={{ flex: 1 }}
                    />
                </div>
            </div>
        </div>
    );
};

export default TableBlockEditor;
