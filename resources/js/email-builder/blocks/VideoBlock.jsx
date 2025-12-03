import { useMemo, useState, useEffect } from 'react';

// Helper to extract video ID and platform from URL
export const parseVideoUrl = (url) => {
    if (!url) return null;

    // YouTube patterns
    const youtubePatterns = [
        /(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/,
        /youtube\.com\/shorts\/([a-zA-Z0-9_-]{11})/,
    ];
    for (const pattern of youtubePatterns) {
        const match = url.match(pattern);
        if (match) {
            return {
                platform: 'youtube',
                videoId: match[1],
                embedUrl: `https://www.youtube.com/embed/${match[1]}`,
                thumbnailUrl: `https://img.youtube.com/vi/${match[1]}/maxresdefault.jpg`,
            };
        }
    }

    // Vimeo patterns
    const vimeoPattern = /(?:vimeo\.com\/)(\d+)/;
    const vimeoMatch = url.match(vimeoPattern);
    if (vimeoMatch) {
        return {
            platform: 'vimeo',
            videoId: vimeoMatch[1],
            embedUrl: `https://player.vimeo.com/video/${vimeoMatch[1]}`,
            thumbnailUrl: null, // Vimeo requires API call for thumbnail
        };
    }

    // Dailymotion patterns
    const dailymotionPattern = /(?:dailymotion\.com\/video\/|dai\.ly\/)([a-zA-Z0-9]+)/;
    const dailymotionMatch = url.match(dailymotionPattern);
    if (dailymotionMatch) {
        return {
            platform: 'dailymotion',
            videoId: dailymotionMatch[1],
            embedUrl: `https://www.dailymotion.com/embed/video/${dailymotionMatch[1]}`,
            thumbnailUrl: `https://www.dailymotion.com/thumbnail/video/${dailymotionMatch[1]}`,
        };
    }

    // Wistia patterns
    const wistiaPattern = /(?:wistia\.com\/medias\/|wi\.st\/)([a-zA-Z0-9]+)/;
    const wistiaMatch = url.match(wistiaPattern);
    if (wistiaMatch) {
        return {
            platform: 'wistia',
            videoId: wistiaMatch[1],
            embedUrl: `https://fast.wistia.net/embed/iframe/${wistiaMatch[1]}`,
            thumbnailUrl: null,
        };
    }

    // Loom patterns
    const loomPattern = /(?:loom\.com\/share\/)([a-zA-Z0-9]+)/;
    const loomMatch = url.match(loomPattern);
    if (loomMatch) {
        return {
            platform: 'loom',
            videoId: loomMatch[1],
            embedUrl: `https://www.loom.com/embed/${loomMatch[1]}`,
            thumbnailUrl: null,
        };
    }

    return null;
};

// Get platform icon
const getPlatformIcon = (platform) => {
    const icons = {
        youtube: 'mdi:youtube',
        vimeo: 'mdi:vimeo',
        dailymotion: 'simple-icons:dailymotion',
        wistia: 'mdi:video-box',
        loom: 'mdi:video-outline',
    };
    return icons[platform] || 'mdi:play-circle';
};

// Get platform color
const getPlatformColor = (platform) => {
    const colors = {
        youtube: '#FF0000',
        vimeo: '#1AB7EA',
        dailymotion: '#00AAFF',
        wistia: '#54BBFF',
        loom: '#625DF5',
    };
    return colors[platform] || '#FF0000';
};

const PLACEHOLDER_IMAGE = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="600" height="340" viewBox="0 0 600 340"%3E%3Crect fill="%231a1a2e" width="600" height="340"/%3E%3Ctext fill="%23ffffff" font-family="Arial" font-size="24" x="50%25" y="50%25" text-anchor="middle" dy=".3em"%3EVideo Thumbnail%3C/text%3E%3C/svg%3E';

const VideoBlock = ({ props, isSelected }) => {
    const [imageError, setImageError] = useState(false);
    const videoInfo = useMemo(() => parseVideoUrl(props.videoUrl), [props.videoUrl]);

    // Use auto-detected thumbnail if no custom one provided
    const thumbnailSrc = props.thumbnailUrl || videoInfo?.thumbnailUrl;
    const displayThumbnail = imageError || !thumbnailSrc ? PLACEHOLDER_IMAGE : thumbnailSrc;
    const playButtonColor = props.playButtonColor || (videoInfo ? getPlatformColor(videoInfo.platform) : '#FF0000');

    const containerStyle = {
        textAlign: props.align || 'center',
        padding: '8px',
        outline: isSelected ? '2px solid #3b82f6' : 'none',
        borderRadius: '4px',
    };

    const wrapperStyle = {
        position: 'relative',
        display: 'inline-block',
        maxWidth: props.width || '100%',
        width: '100%',
    };

    const imageStyle = {
        width: '100%',
        height: 'auto',
        display: 'block',
        borderRadius: '8px',
        backgroundColor: '#1a1a2e',
    };

    const playButtonStyle = {
        position: 'absolute',
        top: '50%',
        left: '50%',
        transform: 'translate(-50%, -50%)',
        width: '68px',
        height: '68px',
        backgroundColor: playButtonColor,
        borderRadius: '50%',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        boxShadow: '0 4px 12px rgba(0,0,0,0.3)',
        cursor: 'pointer',
    };

    const playIconStyle = {
        width: 0,
        height: 0,
        borderTop: '12px solid transparent',
        borderBottom: '12px solid transparent',
        borderLeft: '20px solid white',
        marginLeft: '4px',
    };

    const platformBadgeStyle = {
        position: 'absolute',
        top: '12px',
        left: '12px',
        backgroundColor: 'rgba(0,0,0,0.7)',
        color: 'white',
        padding: '4px 8px',
        borderRadius: '4px',
        fontSize: '12px',
        display: 'flex',
        alignItems: 'center',
        gap: '4px',
    };

    // Reset error state when thumbnail URL changes
    const handleImageError = () => {
        if (!imageError) {
            setImageError(true);
        }
    };

    // Reset error when thumbnailSrc changes
    useEffect(() => {
        setImageError(false);
    }, [thumbnailSrc]);

    return (
        <div style={containerStyle}>
            <div style={wrapperStyle}>
                <img
                    src={displayThumbnail}
                    alt={props.alt || 'Video thumbnail'}
                    style={imageStyle}
                    onError={handleImageError}
                />
                <div style={playButtonStyle}>
                    <div style={playIconStyle}></div>
                </div>
                {videoInfo && (
                    <div style={platformBadgeStyle}>
                        <iconify-icon icon={getPlatformIcon(videoInfo.platform)} width="16" height="16"></iconify-icon>
                        <span style={{ textTransform: 'capitalize' }}>{videoInfo.platform}</span>
                    </div>
                )}
            </div>
            {props.videoUrl && !videoInfo && (
                <p style={{ fontSize: '11px', color: '#f59e0b', marginTop: '8px' }}>
                    Custom video URL (not a recognized platform)
                </p>
            )}
        </div>
    );
};

export default VideoBlock;
