import { layoutStylesToCSS } from '../../components/layout-styles/styleHelpers';

const ButtonBlock = ({ props, isSelected }) => {
    // Get layout styles (typography, background, spacing, etc.)
    const layoutStyles = layoutStylesToCSS(props.layoutStyles || {});

    const containerStyle = {
        textAlign: layoutStyles.textAlign || props.align || 'center',
        padding: '10px 8px',
        outline: isSelected ? '2px solid #635bff' : 'none',
        borderRadius: '4px',
        // Apply margin/padding from layout styles for container
        ...( layoutStyles.marginTop && { marginTop: layoutStyles.marginTop }),
        ...( layoutStyles.marginRight && { marginRight: layoutStyles.marginRight }),
        ...( layoutStyles.marginBottom && { marginBottom: layoutStyles.marginBottom }),
        ...( layoutStyles.marginLeft && { marginLeft: layoutStyles.marginLeft }),
    };

    const buttonStyle = {
        display: 'inline-block',
        backgroundColor: props.backgroundColor || '#635bff',
        color: props.textColor || '#ffffff',
        padding: props.padding || '12px 24px',
        borderRadius: props.borderRadius || '6px',
        textDecoration: 'none',
        fontSize: layoutStyles.fontSize || props.fontSize || '16px',
        fontWeight: layoutStyles.fontWeight || props.fontWeight || '600',
        fontFamily: layoutStyles.fontFamily,
        fontStyle: layoutStyles.fontStyle,
        letterSpacing: layoutStyles.letterSpacing,
        textTransform: layoutStyles.textTransform,
        cursor: 'default',
        border: 'none',
    };

    return (
        <div style={containerStyle}>
            <button type="button" style={buttonStyle}>
                {props.text || 'Button Text'}
            </button>
        </div>
    );
};

export default ButtonBlock;
