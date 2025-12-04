import { layoutStylesToCSS } from '../../components/layout-styles/styleHelpers';

const FooterBlock = ({ props, isSelected }) => {
    // Get layout styles (typography, background, spacing, etc.)
    const layoutStyles = layoutStylesToCSS(props.layoutStyles || {});

    const containerStyle = {
        padding: '24px 16px',
        textAlign: layoutStyles.textAlign || props.align || 'center',
        borderTop: '1px solid #e5e7eb',
        outline: isSelected ? '2px solid #635bff' : 'none',
        borderRadius: '4px',
        backgroundColor: layoutStyles.backgroundColor,
        backgroundImage: layoutStyles.backgroundImage,
        backgroundSize: layoutStyles.backgroundSize,
        backgroundPosition: layoutStyles.backgroundPosition,
        backgroundRepeat: layoutStyles.backgroundRepeat,
        // Apply margin/padding from layout styles
        ...( layoutStyles.marginTop && { marginTop: layoutStyles.marginTop }),
        ...( layoutStyles.marginRight && { marginRight: layoutStyles.marginRight }),
        ...( layoutStyles.marginBottom && { marginBottom: layoutStyles.marginBottom }),
        ...( layoutStyles.marginLeft && { marginLeft: layoutStyles.marginLeft }),
        ...( layoutStyles.paddingTop && { paddingTop: layoutStyles.paddingTop }),
        ...( layoutStyles.paddingRight && { paddingRight: layoutStyles.paddingRight }),
        ...( layoutStyles.paddingBottom && { paddingBottom: layoutStyles.paddingBottom }),
        ...( layoutStyles.paddingLeft && { paddingLeft: layoutStyles.paddingLeft }),
    };

    const textStyle = {
        color: layoutStyles.color || props.textColor || '#6b7280',
        fontSize: layoutStyles.fontSize || props.fontSize || '12px',
        lineHeight: layoutStyles.lineHeight || '1.6',
        fontFamily: layoutStyles.fontFamily,
        fontWeight: layoutStyles.fontWeight,
        fontStyle: layoutStyles.fontStyle,
        letterSpacing: layoutStyles.letterSpacing,
        textTransform: layoutStyles.textTransform,
        textDecoration: layoutStyles.textDecoration,
        margin: '0 0 8px 0',
    };

    const linkStyle = {
        color: props.linkColor || '#635bff',
        textDecoration: 'underline',
    };

    const companyStyle = {
        ...textStyle,
        fontWeight: '600',
        fontSize: '14px',
        marginBottom: '12px',
    };

    return (
        <div style={containerStyle}>
            {props.companyName && (
                <p style={companyStyle}>{props.companyName}</p>
            )}
            {props.address && (
                <p style={textStyle}>{props.address}</p>
            )}
            {(props.phone || props.email) && (
                <p style={textStyle}>
                    {props.phone && <span>{props.phone}</span>}
                    {props.phone && props.email && <span> | </span>}
                    {props.email && (
                        <a href={`mailto:${props.email}`} style={linkStyle}>{props.email}</a>
                    )}
                </p>
            )}
            {props.unsubscribeText && (
                <p style={{ ...textStyle, marginTop: '16px' }}>
                    <a href={props.unsubscribeUrl || '#'} style={linkStyle}>
                        {props.unsubscribeText}
                    </a>
                </p>
            )}
            {props.copyright && (
                <p style={{ ...textStyle, marginTop: '12px', fontSize: '11px' }}>
                    {props.copyright}
                </p>
            )}
        </div>
    );
};

export default FooterBlock;
