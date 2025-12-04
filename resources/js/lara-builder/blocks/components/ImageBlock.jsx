import { layoutStylesToCSS } from '../../components/layout-styles/styleHelpers';

const ImageBlock = ({ props, isSelected }) => {
    // Get layout styles
    const layoutStyles = layoutStylesToCSS(props.layoutStyles || {});

    const containerStyle = {
        textAlign: layoutStyles.textAlign || props.align || 'center',
        padding: '8px',
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

    // Determine width and height values
    const getWidth = () => {
        if (props.width === 'custom' && props.customWidth) return props.customWidth;
        return props.width || '100%';
    };

    const getHeight = () => {
        if (props.height === 'custom' && props.customHeight) return props.customHeight;
        return props.height || 'auto';
    };

    const isCustomWidth = props.width === 'custom' && props.customWidth;
    const isCustomHeight = props.height === 'custom' && props.customHeight;

    const imageStyle = {
        maxWidth: getWidth(),
        width: isCustomWidth ? props.customWidth : undefined,
        height: getHeight(),
        display: 'inline-block',
        objectFit: isCustomWidth || isCustomHeight ? 'cover' : undefined,
    };

    return (
        <div style={containerStyle}>
            {props.src ? (
                <img
                    src={props.src}
                    alt={props.alt || ''}
                    style={imageStyle}
                />
            ) : (
                <div className="bg-gray-100 border-2 border-dashed border-gray-300 text-gray-400 p-8 rounded text-center">
                    <iconify-icon icon="mdi:image-plus" width="32" height="32" class="mb-2"></iconify-icon>
                    <div className="text-sm">Add image in the right panel</div>
                </div>
            )}
        </div>
    );
};

export default ImageBlock;
