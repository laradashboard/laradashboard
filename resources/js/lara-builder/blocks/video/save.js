/**
 * Video Block - Save/Output Generators
 *
 * Generates HTML output for different contexts (page/web and email).
 */

import { buildBlockClasses, mergeBlockStyles } from '@lara-builder/utils';

/**
 * Check if URL is a direct video file
 */
const isDirectVideoFile = (url) => {
    if (!url) return false;
    const videoExtensions = /\.(mp4|webm|ogg|mov|avi|m4v)(\?.*)?$/i;
    return videoExtensions.test(url);
};

/**
 * Parse video URL to extract platform and embed info
 */
const parseVideoUrl = (url) => {
    if (!url) return null;
    const ytMatch = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/|youtube\.com\/shorts\/)([a-zA-Z0-9_-]{11})/);
    if (ytMatch) return { platform: 'youtube', id: ytMatch[1], thumbnail: `https://img.youtube.com/vi/${ytMatch[1]}/maxresdefault.jpg`, color: '#FF0000', embedUrl: `https://www.youtube.com/embed/${ytMatch[1]}?rel=0` };
    const vimeoMatch = url.match(/vimeo\.com\/(\d+)/);
    if (vimeoMatch) return { platform: 'vimeo', id: vimeoMatch[1], thumbnail: null, color: '#1AB7EA', embedUrl: `https://player.vimeo.com/video/${vimeoMatch[1]}` };
    const dmMatch = url.match(/(?:dailymotion\.com\/video\/|dai\.ly\/)([a-zA-Z0-9]+)/);
    if (dmMatch) return { platform: 'dailymotion', id: dmMatch[1], thumbnail: `https://www.dailymotion.com/thumbnail/video/${dmMatch[1]}`, color: '#00AAFF', embedUrl: `https://www.dailymotion.com/embed/video/${dmMatch[1]}` };
    return null;
};

/**
 * Generate HTML for web/page context
 */
export const page = (props, options = {}) => {
    const type = 'video';
    const isDirectVideo = isDirectVideoFile(props.videoUrl);
    const vidInfo = parseVideoUrl(props.videoUrl);
    const width = props.width || '100%';
    const thumbnail = props.thumbnailUrl || props.thumbnail || '';
    const align = props.align || 'center';
    const justifyContent = align === 'left' ? 'flex-start' : align === 'right' ? 'flex-end' : 'center';

    // Use buildBlockClasses for consistent naming
    const blockClasses = buildBlockClasses(type, props);
    const platformClass = vidInfo ? ` lb-video-${vidInfo.platform}` : '';

    // Base block styles
    const blockStyles = `display: flex; justify-content: ${justifyContent}`;
    const mergedStyles = mergeBlockStyles(props, blockStyles);

    // Direct video file
    if (isDirectVideo) {
        // If thumbnail is provided, show thumbnail with play button that loads video on click
        if (thumbnail) {
            const videoId = `lb-video-${Date.now()}-${Math.random().toString(36).substring(2, 9)}`;
            return `
                <div class="${blockClasses}" style="${mergedStyles}">
                    <div id="${videoId}" class="lb-video-container" style="position: relative; max-width: ${width}; width: 100%; cursor: pointer;">
                        <div class="lb-video-thumbnail" style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 8px; background: #000;">
                            <img src="${thumbnail}" alt="${props.alt || 'Video thumbnail'}" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover;" />
                            <div class="lb-video-play-btn" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 68px; height: 48px; background: rgba(0,0,0,0.8); border-radius: 12px; display: flex; align-items: center; justify-content: center; transition: background 0.2s;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="white">
                                    <path d="M8 5v14l11-7z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
                <script>
                    (function() {
                        var container = document.getElementById('${videoId}');
                        if (!container) return;
                        container.addEventListener('click', function() {
                            var wrapper = container.querySelector('.lb-video-thumbnail');
                            wrapper.innerHTML = '<video src="${props.videoUrl}" controls autoplay style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0; border-radius: 8px;"></video>';
                        });
                    })();
                </script>
            `;
        }
        // No thumbnail - use native video element
        return `
            <div class="${blockClasses}" style="${mergedStyles}">
                <video src="${props.videoUrl}" controls ${props.autoplay ? 'autoplay muted' : ''} ${props.loop ? 'loop' : ''} style="max-width: ${width}; width: 100%; height: auto; border-radius: 8px;" preload="metadata">
                    Your browser does not support the video tag.
                </video>
            </div>
        `;
    }

    // Embedded video - use responsive iframe with optional custom thumbnail overlay
    if (vidInfo?.embedUrl) {
        const videoId = `lb-video-${Date.now()}-${Math.random().toString(36).substring(2, 9)}`;

        // If custom thumbnail is provided, show thumbnail with play button overlay
        if (thumbnail) {
            return `
                <div class="${blockClasses}${platformClass}" style="${mergedStyles}">
                    <div id="${videoId}" class="lb-video-container" style="position: relative; max-width: ${width}; width: 100%; cursor: pointer;">
                        <div class="lb-video-thumbnail" style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 8px; background: #000;">
                            <img src="${thumbnail}" alt="Video thumbnail" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover;" />
                            <div class="lb-video-play-btn" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 68px; height: 48px; background: rgba(0,0,0,0.8); border-radius: 12px; display: flex; align-items: center; justify-content: center; transition: background 0.2s;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="white">
                                    <path d="M8 5v14l11-7z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
                <script>
                    (function() {
                        var container = document.getElementById('${videoId}');
                        if (!container) return;
                        container.addEventListener('click', function() {
                            var wrapper = container.querySelector('.lb-video-thumbnail');
                            wrapper.innerHTML = '<iframe src="${vidInfo.embedUrl}&autoplay=1" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0; border-radius: 8px;" allowfullscreen allow="autoplay; encrypted-media; picture-in-picture"></iframe>';
                        });
                    })();
                </script>
            `;
        }

        // No custom thumbnail - show iframe directly
        return `
            <div class="${blockClasses}${platformClass}" style="${mergedStyles}">
                <div style="position: relative; max-width: ${width}; width: 100%;">
                    <div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 8px;">
                        <iframe src="${vidInfo.embedUrl}" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;" allowfullscreen allow="autoplay; encrypted-media; picture-in-picture"></iframe>
                    </div>
                </div>
            </div>
        `;
    }

    // Fallback for unknown video URLs
    return `
        <div class="${blockClasses}" style="${mergedStyles}">
            <a href="${props.videoUrl}" target="_blank" rel="noopener noreferrer" class="lb-video-link">Watch Video</a>
        </div>
    `;
};

/**
 * Generate placeholder for server-side rendering (email context)
 */
export const email = (props, options = {}) => {
    const serverProps = {
        videoUrl: props.videoUrl || '',
        thumbnailUrl: props.thumbnailUrl || props.thumbnail || '',
        alt: props.alt || 'Video',
        width: props.width || '100%',
        align: props.align || 'center',
        playButtonColor: props.playButtonColor || '#635bff',
        layoutStyles: props.layoutStyles || {},
    };

    const propsJson = JSON.stringify(serverProps).replace(/'/g, '&#39;');

    return `<div data-lara-block="video" data-props='${propsJson}'></div>`;
};

export default {
    page,
    email,
};
