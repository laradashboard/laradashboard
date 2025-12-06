/**
 * Spacer Block - Canvas Component
 *
 * Renders the spacer block in the builder canvas.
 */

import { applyLayoutStyles } from '../../components/layout-styles/styleHelpers';

const SpacerBlock = ({ props, isSelected }) => {
    // Base container styles
    const defaultContainerStyle = {
        outline: isSelected ? '2px solid #635bff' : '1px dashed #d1d5db',
        borderRadius: '4px',
        backgroundColor: isSelected ? 'rgba(99, 91, 255, 0.1)' : 'transparent',
    };

    // Apply layout styles to container
    const containerStyle = applyLayoutStyles(defaultContainerStyle, props.layoutStyles);

    const spacerStyle = {
        height: props.height || '40px',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        color: '#9ca3af',
        fontSize: '12px',
    };

    return (
        <div style={containerStyle}>
            <div style={spacerStyle}>
                Spacer ({props.height || '40px'})
            </div>
        </div>
    );
};

export default SpacerBlock;
