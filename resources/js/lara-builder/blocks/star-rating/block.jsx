/**
 * Star Rating Block - Canvas Component
 *
 * Renders the star rating block in the builder canvas.
 * Displays filled and empty stars with optional label.
 */

import { applyLayoutStyles } from '../../components/layout-styles/styleHelpers';

const sizeMap = {
    sm: '16px',
    md: '24px',
    lg: '32px',
};

const StarRatingBlock = ({ props, isSelected }) => {
    const rating = props.rating ?? 5;
    const maxStars = props.maxStars || 5;
    const size = sizeMap[props.size] || sizeMap.md;
    const filledColor = props.filledColor || '#fbbf24';
    const emptyColor = props.emptyColor || '#d1d5db';
    const showLabel = props.showLabel || false;
    const labelText = props.labelText || '';
    const align = props.align || 'center';

    const alignMap = {
        left: 'flex-start',
        center: 'center',
        right: 'flex-end',
    };

    const defaultContainerStyle = {
        display: 'flex',
        alignItems: 'center',
        justifyContent: alignMap[align] || 'center',
        gap: '4px',
        padding: '8px',
    };
    const containerStyle = applyLayoutStyles(defaultContainerStyle, props.layoutStyles);

    const stars = [];
    for (let i = 1; i <= maxStars; i++) {
        const isFull = i <= Math.floor(rating);
        const isHalf = !isFull && i === Math.ceil(rating) && rating % 1 >= 0.5;
        const icon = isFull || isHalf ? 'mdi:star' : 'mdi:star-outline';
        const color = isFull || isHalf ? filledColor : emptyColor;

        stars.push(
            <iconify-icon
                key={i}
                icon={isHalf ? 'mdi:star-half-full' : icon}
                width={size}
                height={size}
                style={{ color, display: 'inline-block' }}
            />
        );
    }

    return (
        <div style={containerStyle} role="img" aria-label={`Rating: ${rating} out of ${maxStars} stars`}>
            <div style={{ display: 'flex', alignItems: 'center', gap: '2px' }}>
                {stars}
            </div>
            {showLabel && labelText && (
                <span style={{
                    marginLeft: '8px',
                    fontSize: size === '16px' ? '12px' : size === '32px' ? '18px' : '14px',
                    color: '#374151',
                    fontWeight: '500',
                }}>
                    {labelText}
                </span>
            )}
        </div>
    );
};

export default StarRatingBlock;
