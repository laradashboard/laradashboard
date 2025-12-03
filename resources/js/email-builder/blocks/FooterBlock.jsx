const FooterBlock = ({ props, isSelected }) => {
    const containerStyle = {
        padding: '24px 16px',
        textAlign: props.align || 'center',
        borderTop: '1px solid #e5e7eb',
        outline: isSelected ? '2px solid #3b82f6' : 'none',
        borderRadius: '4px',
    };

    const textStyle = {
        color: props.textColor || '#6b7280',
        fontSize: props.fontSize || '12px',
        lineHeight: '1.6',
        margin: '0 0 8px 0',
    };

    const linkStyle = {
        color: props.linkColor || '#3b82f6',
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
