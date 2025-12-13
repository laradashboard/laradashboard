/**
 * Time to Read Block - Canvas Component
 *
 * Renders the time to read block in the builder canvas.
 * Shows a preview of how the reading time will be displayed.
 */

import { applyLayoutStyles, layoutStylesToCSS } from '../../components/layout-styles/styleHelpers';

const TimeToReadBlock = ({ props }) => {
    const layoutStyles = layoutStylesToCSS(props.layoutStyles || {});

    // Base container styles
    const defaultContainerStyle = {
        display: 'flex',
        alignItems: 'center',
        gap: '6px',
        textAlign: layoutStyles.textAlign || props.align || 'left',
        justifyContent: props.align === 'center' ? 'center' : props.align === 'right' ? 'flex-end' : 'flex-start',
    };

    // Apply layout styles to container
    const containerStyle = applyLayoutStyles(defaultContainerStyle, props.layoutStyles);

    const textStyle = {
        color: props.layoutStyles?.typography?.color || props.color || '#666666',
        fontSize: props.layoutStyles?.typography?.fontSize || props.fontSize || '14px',
        lineHeight: '1.4',
    };

    const iconStyle = {
        color: props.iconColor || props.color || '#666666',
        width: '16px',
        height: '16px',
        flexShrink: 0,
    };

    // Calculate display text based on displayAsRange
    const getDisplayText = () => {
        const prefix = props.prefix || '';
        const suffix = props.suffix || '';

        if (props.displayAsRange) {
            return `${prefix}1-2 minutes${suffix}`;
        }
        return `${prefix}2 minutes${suffix}`;
    };

    return (
        <div style={containerStyle}>
            {props.showIcon && (
                <svg
                    style={iconStyle}
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    strokeWidth="2"
                    strokeLinecap="round"
                    strokeLinejoin="round"
                >
                    <circle cx="12" cy="12" r="10" />
                    <polyline points="12 6 12 12 16 14" />
                </svg>
            )}
            <span style={textStyle}>{getDisplayText()}</span>
        </div>
    );
};

export default TimeToReadBlock;
