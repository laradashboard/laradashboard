/**
 * Image Block - Canvas Component
 *
 * Renders the image block in the builder canvas.
 * Supports inline image selection when clicked.
 */

import { useState, useCallback } from 'react';
import { applyLayoutStyles, layoutStylesToCSS } from '../../components/layout-styles/styleHelpers';
import { mediaLibrary } from '../../services/MediaLibraryService';
import { __ } from '@lara-builder/i18n';

const ImageBlock = ({ props, onUpdate, isSelected }) => {
    const [isHovered, setIsHovered] = useState(false);

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

    // Handle image selection from media library
    const handleSelectImage = useCallback(async (e) => {
        e.stopPropagation();
        try {
            const file = await mediaLibrary.selectImage();
            if (file && onUpdate) {
                onUpdate({
                    ...props,
                    src: file.url,
                    alt: props.alt || file.name || '',
                });
            }
        } catch (error) {
            // Selection cancelled - ignore
        }
    }, [props, onUpdate]);

    // Handle remove image
    const handleRemoveImage = useCallback((e) => {
        e.stopPropagation();
        if (onUpdate) {
            onUpdate({ ...props, src: '' });
        }
    }, [props, onUpdate]);

    // Show overlay when selected or hovered
    const showOverlay = isSelected || isHovered;

    return (
        <div style={containerStyle}>
            {props.src ? (
                <div
                    style={{ position: 'relative', display: 'inline-block' }}
                    onMouseEnter={() => setIsHovered(true)}
                    onMouseLeave={() => setIsHovered(false)}
                >
                    <img
                        src={props.src}
                        alt={props.alt || ''}
                        style={imageStyle}
                    />
                    {/* Edit overlay */}
                    {showOverlay && (
                        <div
                            style={{
                                position: 'absolute',
                                top: 0,
                                left: 0,
                                right: 0,
                                bottom: 0,
                                backgroundColor: 'rgba(0, 0, 0, 0.5)',
                                display: 'flex',
                                alignItems: 'center',
                                justifyContent: 'center',
                                gap: '8px',
                                borderRadius: '4px',
                                transition: 'opacity 0.2s ease',
                            }}
                        >
                            <button
                                type="button"
                                onClick={handleSelectImage}
                                style={{
                                    display: 'flex',
                                    alignItems: 'center',
                                    gap: '6px',
                                    padding: '8px 16px',
                                    backgroundColor: 'var(--color-primary, #635bff)',
                                    color: 'white',
                                    border: 'none',
                                    borderRadius: '6px',
                                    cursor: 'pointer',
                                    fontSize: '14px',
                                    fontWeight: '500',
                                }}
                            >
                                <iconify-icon icon="lucide:image" width="16" height="16"></iconify-icon>
                                {__('Change')}
                            </button>
                            <button
                                type="button"
                                onClick={handleRemoveImage}
                                style={{
                                    display: 'flex',
                                    alignItems: 'center',
                                    justifyContent: 'center',
                                    padding: '8px',
                                    backgroundColor: '#ef4444',
                                    color: 'white',
                                    border: 'none',
                                    borderRadius: '6px',
                                    cursor: 'pointer',
                                }}
                                title={__('Remove image')}
                            >
                                <iconify-icon icon="lucide:trash-2" width="16" height="16"></iconify-icon>
                            </button>
                        </div>
                    )}
                </div>
            ) : (
                <div
                    onClick={handleSelectImage}
                    onMouseEnter={() => setIsHovered(true)}
                    onMouseLeave={() => setIsHovered(false)}
                    style={{ cursor: 'pointer' }}
                    className={`bg-gray-100 dark:bg-gray-800 border-2 border-dashed ${
                        isHovered || isSelected
                            ? 'border-primary bg-primary/10 dark:bg-primary/20'
                            : 'border-gray-300 dark:border-gray-600'
                    } text-gray-500 dark:text-gray-400 p-8 rounded-lg text-center transition-colors`}
                >
                    <iconify-icon
                        icon={isHovered || isSelected ? 'lucide:upload' : 'mdi:image-plus'}
                        width="40"
                        height="40"
                        class="mb-2"
                        style={{ color: isHovered || isSelected ? 'var(--color-primary, #635bff)' : undefined }}
                    ></iconify-icon>
                    <div className="text-sm font-medium">
                        {isHovered || isSelected ? __('Click to select image') : __('Click to add image')}
                    </div>
                    <div className="text-xs mt-1 opacity-70">
                        {__('or use the properties panel')}
                    </div>
                </div>
            )}
        </div>
    );
};

export default ImageBlock;
