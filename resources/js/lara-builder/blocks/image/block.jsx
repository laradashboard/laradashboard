/**
 * Image Block - Canvas Component
 *
 * Renders the image block in the builder canvas.
 */

import { applyLayoutStyles, layoutStylesToCSS } from '../../components/layout-styles/styleHelpers';

const ImageBlock = ({ props }) => {
    // Get layout styles for textAlign
    const layoutStyles = layoutStylesToCSS(props.layoutStyles || {});

    // Base container styles
    const defaultContainerStyle = {
        textAlign: layoutStyles.textAlign || props.align || 'center',
        padding: '8px',
        borderRadius: '4px',
    };

    // Apply layout styles to container
    const containerStyle = applyLayoutStyles(defaultContainerStyle, props.layoutStyles);

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
