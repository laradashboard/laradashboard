/**
 * Table Block - Canvas Component
 *
 * Renders the table block in the builder canvas.
 */

import { applyLayoutStyles } from '../../components/layout-styles/styleHelpers';

const TableBlock = ({ props }) => {
    // Base container styles
    const defaultContainerStyle = {
        padding: '8px',
        borderRadius: '4px',
    };

    // Apply layout styles to container
    const containerStyle = applyLayoutStyles(defaultContainerStyle, props.layoutStyles);

    // Base table styles
    const defaultTableStyle = {
        width: '100%',
        borderCollapse: 'collapse',
        fontSize: props.fontSize || '14px',
    };

    // Apply typography from layout styles to table
    const tableStyle = applyLayoutStyles(defaultTableStyle, props.layoutStyles);

    const headerCellStyle = {
        backgroundColor: props.headerBgColor || '#f1f5f9',
        color: props.headerTextColor || '#1e293b',
        padding: props.cellPadding || '12px',
        textAlign: 'left',
        fontWeight: '600',
        borderBottom: `2px solid ${props.borderColor || '#e2e8f0'}`,
    };

    const cellStyle = {
        padding: props.cellPadding || '12px',
        borderBottom: `1px solid ${props.borderColor || '#e2e8f0'}`,
        color: '#374151',
    };

    const headers = props.headers || [];
    const rows = props.rows || [];

    return (
        <div style={containerStyle}>
            <table style={tableStyle}>
                {props.showHeader && headers.length > 0 && (
                    <thead>
                        <tr>
                            {headers.map((header, index) => (
                                <th key={index} style={headerCellStyle}>
                                    {header}
                                </th>
                            ))}
                        </tr>
                    </thead>
                )}
                <tbody>
                    {rows.map((row, rowIndex) => (
                        <tr key={rowIndex}>
                            {row.map((cell, cellIndex) => (
                                <td key={cellIndex} style={cellStyle}>
                                    {cell}
                                </td>
                            ))}
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
};

export default TableBlock;
