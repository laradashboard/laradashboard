const TextBlock = ({ props, onUpdate, isSelected }) => {
    const handleChange = (e) => {
        onUpdate({ ...props, content: e.target.value });
    };

    const autoResize = (el) => {
        if (el) {
            el.style.height = 'auto';
            el.style.height = el.scrollHeight + 'px';
        }
    };

    const style = {
        textAlign: props.align || 'left',
        color: props.color || '#666666',
        fontSize: props.fontSize || '16px',
        lineHeight: props.lineHeight || '1.6',
        padding: '8px',
        outline: isSelected ? '2px solid #635bff' : 'none',
        borderRadius: '4px',
        minHeight: '40px',
    };

    if (isSelected) {
        return (
            <div data-text-editing="true">
                <textarea
                    ref={autoResize}
                    value={props.content || ''}
                    onChange={handleChange}
                    placeholder="Enter text..."
                    rows={1}
                    style={{
                        ...style,
                        width: '100%',
                        border: '2px solid #635bff',
                        background: 'white',
                        resize: 'none',
                        overflow: 'hidden',
                    }}
                    onInput={(e) => autoResize(e.target)}
                />
            </div>
        );
    }

    // Convert newlines to <br> for display
    const displayContent = (props.content || 'Click to edit text').split('\n').map((line, i, arr) => (
        <span key={i}>
            {line}
            {i < arr.length - 1 && <br />}
        </span>
    ));

    return (
        <div style={style}>
            {displayContent}
        </div>
    );
};

export default TextBlock;
