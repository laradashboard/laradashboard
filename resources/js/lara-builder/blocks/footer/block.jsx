import { applyLayoutStyles } from '../../components/layout-styles/styleHelpers';

const FooterBlock = ({ props }) => {
    // Base container styles
    const defaultContainerStyle = {
        padding: '24px 16px',
        textAlign: props.align || 'center',
        borderTop: '1px solid #e5e7eb',
        borderRadius: '4px',
    };

    // Apply layout styles to container
    const containerStyle = applyLayoutStyles(defaultContainerStyle, props.layoutStyles);

    // Base text styles
    const defaultTextStyle = {
        color: props.textColor || '#6b7280',
        fontSize: props.fontSize || '12px',
        lineHeight: '1.6',
        margin: '0 0 8px 0',
    };

    // Apply typography from layout styles
    const textStyle = applyLayoutStyles(defaultTextStyle, props.layoutStyles);

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
