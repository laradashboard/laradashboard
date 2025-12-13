/**
 * Divider Block - Canvas Component
 *
 * Renders the divider block in the builder canvas.
 */

import { applyLayoutStyles } from '../../components/layout-styles/styleHelpers';

const DividerBlock = ({ props }) => {
    // Base container styles
    const defaultContainerStyle = {
        padding: '8px',
        borderRadius: '4px',
    };

    // Apply layout styles to container
    const containerStyle = applyLayoutStyles(defaultContainerStyle, props.layoutStyles);

    const dividerStyle = {
        border: 'none',
        borderTop: `${props.thickness || '1px'} ${props.style || 'solid'} ${props.color || '#e5e7eb'}`,
        width: props.width || '100%',
        margin: props.margin || '20px 0',
    };

    return (
        <div style={containerStyle}>
            <hr style={dividerStyle} />
        </div>
    );
};

export default DividerBlock;
