const ButtonBlock = ({ props, isSelected }) => {
    const containerStyle = {
        textAlign: props.align || 'center',
        padding: '10px 8px',
        outline: isSelected ? '2px solid #635bff' : 'none',
        borderRadius: '4px',
    };

    const buttonStyle = {
        display: 'inline-block',
        backgroundColor: props.backgroundColor || '#635bff',
        color: props.textColor || '#ffffff',
        padding: props.padding || '12px 24px',
        borderRadius: props.borderRadius || '6px',
        textDecoration: 'none',
        fontSize: props.fontSize || '16px',
        fontWeight: props.fontWeight || '600',
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
