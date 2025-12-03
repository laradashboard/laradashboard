const HeadingBlock = ({ props, onUpdate, isSelected }) => {
    const handleChange = (e) => {
        onUpdate({ ...props, text: e.target.value });
    };

    const autoResize = (el) => {
        if (el) {
            el.style.height = 'auto';
            el.style.height = el.scrollHeight + 'px';
        }
    };

    const style = {
        textAlign: props.align || 'left',
        color: props.color || '#333333',
        fontSize: props.fontSize || '32px',
        fontWeight: props.fontWeight || 'bold',
        margin: 0,
        padding: '8px',
        outline: isSelected ? '2px solid #635bff' : 'none',
        borderRadius: '4px',
        lineHeight: '1.3',
    };

    if (isSelected) {
        return (
            <div data-text-editing="true">
                <textarea
                    ref={autoResize}
                    value={props.text || ''}
                    onChange={handleChange}
                    placeholder="Enter heading text..."
                    rows={1}
                    style={{
                        ...style,
                        width: '100%',
                        border: '2px solid #635bff',
                        background: 'white',
                        resize: 'none',
                        overflow: 'hidden',
                        minHeight: '1.5em',
                    }}
                    onInput={(e) => autoResize(e.target)}
                />
            </div>
        );
    }

    const Tag = props.level || 'h1';

    // Convert newlines to <br> for display
    const displayText = (props.text || 'Click to edit heading').split('\n').map((line, i, arr) => (
        <span key={i}>
            {line}
            {i < arr.length - 1 && <br />}
        </span>
    ));

    return (
        <Tag style={style}>
            {displayText}
        </Tag>
    );
};

export default HeadingBlock;
