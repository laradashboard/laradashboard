const ListBlock = ({ props, isSelected }) => {
    const containerStyle = {
        padding: '8px',
        outline: isSelected ? '2px solid #3b82f6' : 'none',
        borderRadius: '4px',
    };

    const listStyle = {
        color: props.color || '#333333',
        fontSize: props.fontSize || '16px',
        lineHeight: '1.8',
        margin: 0,
        paddingLeft: props.listType === 'none' ? '0' : '24px',
        listStyleType: props.listType === 'bullet' ? 'disc' : props.listType === 'number' ? 'decimal' : 'none',
    };

    const itemStyle = {
        marginBottom: '8px',
    };

    const checkIconStyle = {
        color: props.iconColor || '#3b82f6',
        marginRight: '8px',
    };

    const items = props.items || [];

    if (props.listType === 'check') {
        return (
            <div style={containerStyle}>
                <div style={{ color: props.color, fontSize: props.fontSize }}>
                    {items.map((item, index) => (
                        <div key={index} style={{ ...itemStyle, display: 'flex', alignItems: 'flex-start' }}>
                            <span style={checkIconStyle}>
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </span>
                            <span>{item}</span>
                        </div>
                    ))}
                </div>
            </div>
        );
    }

    const ListTag = props.listType === 'number' ? 'ol' : 'ul';

    return (
        <div style={containerStyle}>
            <ListTag style={listStyle}>
                {items.map((item, index) => (
                    <li key={index} style={itemStyle}>{item}</li>
                ))}
            </ListTag>
        </div>
    );
};

export default ListBlock;
