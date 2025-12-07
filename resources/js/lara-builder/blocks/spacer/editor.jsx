/**
 * Spacer Block - Property Editor
 */

import { __ } from '@lara-builder/i18n';

const SpacerBlockEditor = ({ props, onUpdate }) => {
    const handleChange = (value) => {
        onUpdate({ ...props, height: value });
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

    return (
        <div>
            <div style={sectionStyle}>
                <div style={sectionTitleStyle}>{__('Size')}</div>

                <label style={labelStyle}>{__('Height')}</label>
                <select
                    value={props.height || '40px'}
                    onChange={(e) => handleChange(e.target.value)}
                    className="form-control"
                >
                    <option value="10px">10px</option>
                    <option value="20px">20px</option>
                    <option value="30px">30px</option>
                    <option value="40px">40px</option>
                    <option value="50px">50px</option>
                    <option value="60px">60px</option>
                    <option value="80px">80px</option>
                    <option value="100px">100px</option>
                </select>
            </div>
        </div>
    );
};

export default SpacerBlockEditor;
