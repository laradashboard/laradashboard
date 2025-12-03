/**
 * Generate HTML from blocks for email export
 */

export const generateBlockHtml = (block) => {
    const { type, props } = block;

    switch (type) {
        case 'heading':
            return `<${props.level} style="text-align: ${props.align}; color: ${props.color}; font-size: ${props.fontSize}; font-weight: ${props.fontWeight}; margin: 0 0 16px 0;">${props.text}</${props.level}>`;

        case 'text':
            return `<div style="text-align: ${props.align}; color: ${props.color}; font-size: ${props.fontSize}; line-height: ${props.lineHeight};">${props.content}</div>`;

        case 'image':
            const isCustomWidth = props.width === 'custom' && props.customWidth;
            const isCustomHeight = props.height === 'custom' && props.customHeight;
            const imgWidth = isCustomWidth ? props.customWidth : (props.width || '100%');
            const imgHeight = isCustomHeight ? props.customHeight : (props.height || 'auto');
            const imgStyle = `max-width: ${imgWidth};${isCustomWidth ? ` width: ${props.customWidth};` : ''} height: ${imgHeight}; display: block; margin: 0 auto;${imgHeight !== 'auto' ? ' object-fit: cover;' : ''}`;
            const img = `<img src="${props.src}" alt="${props.alt}" style="${imgStyle}" />`;
            return props.link
                ? `<a href="${props.link}" target="_blank" style="display: block; text-align: ${props.align};">${img}</a>`
                : `<div style="text-align: ${props.align};">${img}</div>`;

        case 'button':
            return `
                <div style="text-align: ${props.align}; padding: 10px 0;">
                    <a href="${props.link}" target="_blank" style="display: inline-block; background-color: ${props.backgroundColor}; color: ${props.textColor}; padding: ${props.padding}; border-radius: ${props.borderRadius}; text-decoration: none; font-size: ${props.fontSize}; font-weight: ${props.fontWeight};">${props.text}</a>
                </div>
            `;

        case 'divider':
            return `<hr style="border: none; border-top: ${props.thickness} ${props.style} ${props.color}; width: ${props.width}; margin: ${props.margin};" />`;

        case 'spacer':
            return `<div style="height: ${props.height};"></div>`;

        case 'columns':
            const columnWidth = `${100 / props.columns}%`;
            const columnsHtml = props.children.map((columnBlocks, index) => {
                const columnContent = columnBlocks.map(b => generateBlockHtml(b)).join('');
                return `<td style="width: ${columnWidth}; vertical-align: top; padding: 0 ${index < props.columns - 1 ? props.gap : '0'} 0 0;">${columnContent || '&nbsp;'}</td>`;
            }).join('');
            return `
                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>${columnsHtml}</tr>
                </table>
            `;

        case 'social':
            const socialIcons = {
                facebook: 'https://cdn-icons-png.flaticon.com/32/733/733547.png',
                twitter: 'https://cdn-icons-png.flaticon.com/32/733/733579.png',
                instagram: 'https://cdn-icons-png.flaticon.com/32/2111/2111463.png',
                linkedin: 'https://cdn-icons-png.flaticon.com/32/733/733561.png',
                youtube: 'https://cdn-icons-png.flaticon.com/32/733/733646.png',
            };
            const socialIconSize = parseInt(props.iconSize) || 32;
            const socialGap = parseInt(props.gap) / 2 || 6;
            const socialLinksHtml = Object.entries(props.links || {})
                .filter(([, url]) => url)
                .map(([platform, url]) => `<a href="${url}" target="_blank" style="display: inline-block; margin: 0 ${socialGap}px;"><img src="${socialIcons[platform]}" alt="${platform}" width="${socialIconSize}" height="${socialIconSize}" style="border: 0;" /></a>`)
                .join('');
            return socialLinksHtml ? `<div style="text-align: ${props.align || 'center'}; padding: 10px 0;">${socialLinksHtml}</div>` : '';

        case 'html':
            return props.code;

        case 'quote':
            return `
                <div style="padding: 20px; padding-left: 24px; background-color: ${props.backgroundColor || '#f8fafc'}; border-left: 4px solid ${props.borderColor || '#3b82f6'}; text-align: ${props.align || 'left'}; border-radius: 4px; margin: 10px 0;">
                    <p style="color: ${props.textColor || '#475569'}; font-size: 16px; font-style: italic; line-height: 1.6; margin: 0 0 12px 0;">"${props.text}"</p>
                    ${props.author ? `<p style="color: ${props.authorColor || '#1e293b'}; font-size: 14px; font-weight: 600; margin: 0;">${props.author}</p>` : ''}
                    ${props.authorTitle ? `<p style="color: ${props.textColor || '#475569'}; font-size: 12px; margin: 0;">${props.authorTitle}</p>` : ''}
                </div>
            `;

        case 'list':
            const listItems = (props.items || []).map(item => {
                if (props.listType === 'check') {
                    return `<tr><td style="vertical-align: top; padding-right: 8px; color: ${props.iconColor || '#3b82f6'};">&#10003;</td><td style="color: ${props.color || '#333333'}; font-size: ${props.fontSize || '16px'}; padding-bottom: 8px;">${item}</td></tr>`;
                }
                return `<li style="margin-bottom: 8px;">${item}</li>`;
            }).join('');

            if (props.listType === 'check') {
                return `<table style="color: ${props.color || '#333333'}; font-size: ${props.fontSize || '16px'}; line-height: 1.6;">${listItems}</table>`;
            }
            const listTag = props.listType === 'number' ? 'ol' : 'ul';
            return `<${listTag} style="color: ${props.color || '#333333'}; font-size: ${props.fontSize || '16px'}; line-height: 1.8; margin: 0; padding-left: 24px;">${listItems}</${listTag}>`;

        case 'video':
            // Parse video URL to get platform-specific info
            const parseVideoUrlForHtml = (url) => {
                if (!url) return null;
                // YouTube
                const ytMatch = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/|youtube\.com\/shorts\/)([a-zA-Z0-9_-]{11})/);
                if (ytMatch) return { platform: 'youtube', id: ytMatch[1], thumbnail: `https://img.youtube.com/vi/${ytMatch[1]}/maxresdefault.jpg`, color: '#FF0000' };
                // Vimeo
                const vimeoMatch = url.match(/vimeo\.com\/(\d+)/);
                if (vimeoMatch) return { platform: 'vimeo', id: vimeoMatch[1], thumbnail: null, color: '#1AB7EA' };
                // Dailymotion
                const dmMatch = url.match(/(?:dailymotion\.com\/video\/|dai\.ly\/)([a-zA-Z0-9]+)/);
                if (dmMatch) return { platform: 'dailymotion', id: dmMatch[1], thumbnail: `https://www.dailymotion.com/thumbnail/video/${dmMatch[1]}`, color: '#00AAFF' };
                return null;
            };
            const vidInfo = parseVideoUrlForHtml(props.videoUrl);
            const vidThumbnail = props.thumbnailUrl || vidInfo?.thumbnail || 'https://via.placeholder.com/600x340/1a1a2e/ffffff?text=Video';
            const vidPlayColor = props.playButtonColor || vidInfo?.color || '#ff0000';
            const videoThumbnail = `
                <div style="position: relative; display: inline-block; max-width: ${props.width || '100%'}; width: 100%;">
                    <a href="${props.videoUrl}" target="_blank" style="display: block; text-decoration: none;">
                        <img src="${vidThumbnail}" alt="${props.alt || 'Video thumbnail'}" style="width: 100%; height: auto; display: block; border-radius: 8px;" />
                        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 68px; height: 68px; background-color: ${vidPlayColor}; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <span style="width: 0; height: 0; border-top: 12px solid transparent; border-bottom: 12px solid transparent; border-left: 20px solid white; margin-left: 4px;"></span>
                        </div>
                    </a>
                </div>
            `;
            return `<div style="text-align: ${props.align || 'center'};">${videoThumbnail}</div>`;

        case 'footer':
            return `
                <div style="padding: 24px 16px; text-align: ${props.align || 'center'}; border-top: 1px solid #e5e7eb;">
                    ${props.companyName ? `<p style="color: ${props.textColor || '#6b7280'}; font-size: 14px; font-weight: 600; margin: 0 0 12px 0;">${props.companyName}</p>` : ''}
                    ${props.address ? `<p style="color: ${props.textColor || '#6b7280'}; font-size: ${props.fontSize || '12px'}; margin: 0 0 8px 0;">${props.address}</p>` : ''}
                    ${(props.phone || props.email) ? `<p style="color: ${props.textColor || '#6b7280'}; font-size: ${props.fontSize || '12px'}; margin: 0 0 8px 0;">${props.phone || ''}${props.phone && props.email ? ' | ' : ''}${props.email ? `<a href="mailto:${props.email}" style="color: ${props.linkColor || '#3b82f6'}; text-decoration: underline;">${props.email}</a>` : ''}</p>` : ''}
                    ${props.unsubscribeText ? `<p style="color: ${props.textColor || '#6b7280'}; font-size: ${props.fontSize || '12px'}; margin: 16px 0 0 0;"><a href="${props.unsubscribeUrl || '#'}" style="color: ${props.linkColor || '#3b82f6'}; text-decoration: underline;">${props.unsubscribeText}</a></p>` : ''}
                    ${props.copyright ? `<p style="color: ${props.textColor || '#6b7280'}; font-size: 11px; margin: 12px 0 0 0;">${props.copyright}</p>` : ''}
                </div>
            `;

        case 'countdown':
            // Countdown timer in emails - generates static HTML at send time
            // Note: True live countdowns require external image services or AMP email
            const countdownTargetDate = props.targetDate || new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
            const countdownTargetTime = props.targetTime || '23:59';
            const targetDateTime = `${countdownTargetDate}T${countdownTargetTime}:00`;
            const targetDate = new Date(targetDateTime);
            const now = new Date();
            const diff = Math.max(0, targetDate - now);
            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
            const hours = Math.floor((diff / (1000 * 60 * 60)) % 24);
            const minutes = Math.floor((diff / 1000 / 60) % 60);
            const seconds = Math.floor((diff / 1000) % 60);

            // If already expired, show expired message
            if (diff <= 0 && props.expiredMessage) {
                return `
                    <div style="padding: 24px; background-color: ${props.backgroundColor || '#1e293b'}; border-radius: 8px; text-align: ${props.align || 'center'};">
                        <p style="color: ${props.textColor || '#ffffff'}; font-size: 18px; font-weight: 600; margin: 0;">${props.expiredMessage}</p>
                    </div>
                `;
            }

            return `
                <div style="padding: 24px; background-color: ${props.backgroundColor || '#1e293b'}; border-radius: 8px; text-align: ${props.align || 'center'};">
                    ${props.title ? `<p style="color: ${props.textColor || '#ffffff'}; font-size: 18px; font-weight: 600; margin: 0 0 16px 0;">${props.title}</p>` : ''}
                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td align="center">
                                <table cellpadding="0" cellspacing="0" border="0">
                                    <tr>
                                        <td style="text-align: center; padding: 0 12px;">
                                            <div style="background-color: rgba(255,255,255,0.1); border-radius: 8px; padding: 12px 16px;">
                                                <span style="color: ${props.numberColor || '#3b82f6'}; font-size: 36px; font-weight: 700; display: block;">${String(days).padStart(2, '0')}</span>
                                                <span style="color: ${props.textColor || '#ffffff'}; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">Days</span>
                                            </div>
                                        </td>
                                        <td style="color: ${props.numberColor || '#3b82f6'}; font-size: 28px; font-weight: 700; padding: 0 4px;">:</td>
                                        <td style="text-align: center; padding: 0 12px;">
                                            <div style="background-color: rgba(255,255,255,0.1); border-radius: 8px; padding: 12px 16px;">
                                                <span style="color: ${props.numberColor || '#3b82f6'}; font-size: 36px; font-weight: 700; display: block;">${String(hours).padStart(2, '0')}</span>
                                                <span style="color: ${props.textColor || '#ffffff'}; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">Hours</span>
                                            </div>
                                        </td>
                                        <td style="color: ${props.numberColor || '#3b82f6'}; font-size: 28px; font-weight: 700; padding: 0 4px;">:</td>
                                        <td style="text-align: center; padding: 0 12px;">
                                            <div style="background-color: rgba(255,255,255,0.1); border-radius: 8px; padding: 12px 16px;">
                                                <span style="color: ${props.numberColor || '#3b82f6'}; font-size: 36px; font-weight: 700; display: block;">${String(minutes).padStart(2, '0')}</span>
                                                <span style="color: ${props.textColor || '#ffffff'}; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">Mins</span>
                                            </div>
                                        </td>
                                        <td style="color: ${props.numberColor || '#3b82f6'}; font-size: 28px; font-weight: 700; padding: 0 4px;">:</td>
                                        <td style="text-align: center; padding: 0 12px;">
                                            <div style="background-color: rgba(255,255,255,0.1); border-radius: 8px; padding: 12px 16px;">
                                                <span style="color: ${props.numberColor || '#3b82f6'}; font-size: 36px; font-weight: 700; display: block;">${String(seconds).padStart(2, '0')}</span>
                                                <span style="color: ${props.textColor || '#ffffff'}; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">Secs</span>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </div>
            `;

        case 'table':
            const tableHeaders = (props.headers || []).map(header =>
                `<th style="background-color: ${props.headerBgColor || '#f1f5f9'}; color: ${props.headerTextColor || '#1e293b'}; padding: ${props.cellPadding || '12px'}; text-align: left; font-weight: 600; border-bottom: 2px solid ${props.borderColor || '#e2e8f0'};">${header}</th>`
            ).join('');
            const tableRows = (props.rows || []).map(row =>
                `<tr>${row.map(cell => `<td style="padding: ${props.cellPadding || '12px'}; border-bottom: 1px solid ${props.borderColor || '#e2e8f0'}; color: #374151;">${cell}</td>`).join('')}</tr>`
            ).join('');

            return `
                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="font-size: ${props.fontSize || '14px'}; border-collapse: collapse;">
                    ${props.showHeader && tableHeaders ? `<thead><tr>${tableHeaders}</tr></thead>` : ''}
                    <tbody>${tableRows}</tbody>
                </table>
            `;

        default:
            return '';
    }
};

export const generateEmailHtml = (blocks, canvasSettings = {}) => {
    const backgroundColor = canvasSettings.backgroundColor || '#f4f4f4';
    const backgroundImage = canvasSettings.backgroundImage || '';
    const backgroundSize = canvasSettings.backgroundSize || 'cover';
    const backgroundPosition = canvasSettings.backgroundPosition || 'center';
    const backgroundRepeat = canvasSettings.backgroundRepeat || 'no-repeat';
    const contentBackgroundColor = canvasSettings.contentBackgroundColor || '#ffffff';
    const contentBackgroundImage = canvasSettings.contentBackgroundImage || '';
    const contentBackgroundSize = canvasSettings.contentBackgroundSize || 'cover';
    const contentBackgroundPosition = canvasSettings.contentBackgroundPosition || 'center';
    const contentBackgroundRepeat = canvasSettings.contentBackgroundRepeat || 'no-repeat';
    const maxWidth = canvasSettings.width || '600px';
    const fontFamily = canvasSettings.fontFamily || 'Arial, sans-serif';
    const contentPadding = canvasSettings.contentPadding || '40px';
    const contentMargin = canvasSettings.contentMargin || '40px';
    const contentBorderWidth = canvasSettings.contentBorderWidth || '0px';
    const contentBorderColor = canvasSettings.contentBorderColor || '#e5e7eb';
    const contentBorderRadius = canvasSettings.contentBorderRadius || '8px';

    const borderStyle = contentBorderWidth !== '0px'
        ? `border: ${contentBorderWidth} solid ${contentBorderColor};`
        : '';

    // Build outer background style
    let outerBgStyle = `background-color: ${backgroundColor};`;
    if (backgroundImage) {
        outerBgStyle += ` background-image: url('${backgroundImage}'); background-size: ${backgroundSize}; background-position: ${backgroundPosition}; background-repeat: ${backgroundRepeat};`;
    }

    // Build content background style
    let contentBgStyle = `background-color: ${contentBackgroundColor};`;
    if (contentBackgroundImage) {
        contentBgStyle += ` background-image: url('${contentBackgroundImage}'); background-size: ${contentBackgroundSize}; background-position: ${contentBackgroundPosition}; background-repeat: ${contentBackgroundRepeat};`;
    }

    const blocksHtml = blocks.map(block => generateBlockHtml(block)).join('');

    return `
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Email</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
    <style>
        body { margin: 0; padding: 0; font-family: ${fontFamily}; }
        table { border-collapse: collapse; }
        img { border: 0; line-height: 100%; outline: none; text-decoration: none; }
        a { color: inherit; }
    </style>
</head>
<body style="margin: 0; padding: 0; ${outerBgStyle}">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="${outerBgStyle}">
        <tr>
            <td align="center" style="padding: ${contentMargin} 20px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: ${maxWidth}; ${contentBgStyle} border-radius: ${contentBorderRadius}; ${borderStyle}">
                    <tr>
                        <td style="padding: ${contentPadding};">
                            ${blocksHtml}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
    `.trim();
};

export default generateEmailHtml;
