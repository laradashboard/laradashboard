/**
 * Social Block - Canvas Component
 *
 * Renders the social links block in the builder canvas.
 */

import { applyLayoutStyles, layoutStylesToCSS } from '../../components/layout-styles/styleHelpers';

const socialPlatforms = [
    { key: 'facebook', label: 'Facebook', icon: 'mdi:facebook', color: '#1877f2' },
    { key: 'twitter', label: 'Twitter/X', icon: 'mdi:twitter', color: '#1da1f2' },
    { key: 'instagram', label: 'Instagram', icon: 'mdi:instagram', color: '#e4405f' },
    { key: 'linkedin', label: 'LinkedIn', icon: 'mdi:linkedin', color: '#0a66c2' },
    { key: 'youtube', label: 'YouTube', icon: 'mdi:youtube', color: '#ff0000' },
];

const SocialBlock = ({ props, isSelected }) => {
    // Get layout styles for textAlign
    const layoutStyles = layoutStylesToCSS(props.layoutStyles || {});

    // Base container styles
    const defaultContainerStyle = {
        textAlign: layoutStyles.textAlign || props.align || 'center',
        padding: '10px 8px',
        borderRadius: '4px',
    };

    // Apply layout styles to container
    const containerStyle = applyLayoutStyles(defaultContainerStyle, props.layoutStyles);

    const iconContainerStyle = {
        display: 'inline-flex',
        alignItems: 'center',
        justifyContent: props.align === 'left' ? 'flex-start' : props.align === 'right' ? 'flex-end' : 'center',
        gap: props.gap || '12px',
        flexWrap: 'wrap',
    };

    const iconSize = parseInt(props.iconSize) || 32;

    const activeLinks = Object.entries(props.links || {}).filter(([, url]) => url);

    return (
        <div style={containerStyle}>
            {activeLinks.length > 0 ? (
                <div style={iconContainerStyle}>
                    {activeLinks.map(([platform]) => {
                        const platformInfo = socialPlatforms.find(p => p.key === platform);
                        return (
                            <span
                                key={platform}
                                title={platformInfo?.label}
                                style={{
                                    display: 'inline-flex',
                                    alignItems: 'center',
                                    justifyContent: 'center',
                                    width: `${iconSize}px`,
                                    height: `${iconSize}px`,
                                    color: platformInfo?.color || '#666',
                                }}
                            >
                                <iconify-icon
                                    icon={platformInfo?.icon || 'mdi:link'}
                                    width={iconSize}
                                    height={iconSize}
                                ></iconify-icon>
                            </span>
                        );
                    })}
                </div>
            ) : (
                <div className="text-gray-400 text-sm py-2">
                    <iconify-icon icon="mdi:share-variant-outline" width="24" height="24" class="mb-1"></iconify-icon>
                    <div>Add social links in the right panel</div>
                </div>
            )}
        </div>
    );
};

export default SocialBlock;
