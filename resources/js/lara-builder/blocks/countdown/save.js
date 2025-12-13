/**
 * Countdown Block - Save/Output Generators
 *
 * Generates HTML output for different contexts (page/web and email).
 */

import { buildBlockClasses, mergeBlockStyles } from '@lara-builder/utils';

/**
 * Generate HTML for web/page context
 */
export const page = (props, options = {}) => {
    const type = 'countdown';
    const blockClasses = buildBlockClasses(type, props);
    const countdownId = `countdown-${Date.now()}-${Math.random().toString(36).substring(2, 9)}`;
    const targetDate = props.targetDate || new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
    const targetTime = props.targetTime || '23:59';
    const targetDateTime = `${targetDate}T${targetTime}:00`;

    // Block-specific styles
    const blockStyles = [
        `padding: 24px`,
        `text-align: ${props.align || 'center'}`,
    ];

    // Only add if not controlled by layoutStyles
    if (!props.layoutStyles?.background?.color) {
        blockStyles.push(`background-color: ${props.backgroundColor || '#1e293b'}`);
    }
    if (!props.layoutStyles?.border) {
        blockStyles.push(`border-radius: 8px`);
    }

    const mergedStyles = mergeBlockStyles(props, blockStyles.join('; '));
    const textColor = props.layoutStyles?.typography?.color || props.textColor || '#ffffff';

    return `
        <div class="${blockClasses}" id="${countdownId}" data-target="${targetDateTime}" data-expired-message="${props.expiredMessage || ''}" style="${mergedStyles}">
            ${props.title ? `<p style="color: ${textColor}; font-size: 18px; font-weight: 600; margin: 0 0 16px 0;">${props.title}</p>` : ''}
            <div style="display: flex; justify-content: center; gap: 16px; flex-wrap: wrap;">
                <div style="background-color: rgba(255,255,255,0.1); border-radius: 8px; padding: 12px 16px; min-width: 60px;">
                    <span class="lb-countdown-days" style="color: ${props.numberColor || '#635bff'}; font-size: 36px; font-weight: 700; display: block;">00</span>
                    <span style="color: ${textColor}; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">Days</span>
                </div>
                <div style="background-color: rgba(255,255,255,0.1); border-radius: 8px; padding: 12px 16px; min-width: 60px;">
                    <span class="lb-countdown-hours" style="color: ${props.numberColor || '#635bff'}; font-size: 36px; font-weight: 700; display: block;">00</span>
                    <span style="color: ${textColor}; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">Hours</span>
                </div>
                <div style="background-color: rgba(255,255,255,0.1); border-radius: 8px; padding: 12px 16px; min-width: 60px;">
                    <span class="lb-countdown-mins" style="color: ${props.numberColor || '#635bff'}; font-size: 36px; font-weight: 700; display: block;">00</span>
                    <span style="color: ${textColor}; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">Mins</span>
                </div>
                <div style="background-color: rgba(255,255,255,0.1); border-radius: 8px; padding: 12px 16px; min-width: 60px;">
                    <span class="lb-countdown-secs" style="color: ${props.numberColor || '#635bff'}; font-size: 36px; font-weight: 700; display: block;">00</span>
                    <span style="color: ${textColor}; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">Secs</span>
                </div>
            </div>
        </div>
        <script>
            (function() {
                const el = document.getElementById('${countdownId}');
                if (!el) return;
                const target = new Date(el.dataset.target);
                const expiredMsg = el.dataset.expiredMessage;
                function update() {
                    const now = new Date();
                    const diff = Math.max(0, target - now);
                    if (diff <= 0 && expiredMsg) {
                        el.innerHTML = '<p style="color: #ffffff; font-size: 18px; font-weight: 600; margin: 0;">' + expiredMsg + '</p>';
                        return;
                    }
                    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((diff / (1000 * 60 * 60)) % 24);
                    const mins = Math.floor((diff / 1000 / 60) % 60);
                    const secs = Math.floor((diff / 1000) % 60);
                    el.querySelector('.lb-countdown-days').textContent = String(days).padStart(2, '0');
                    el.querySelector('.lb-countdown-hours').textContent = String(hours).padStart(2, '0');
                    el.querySelector('.lb-countdown-mins').textContent = String(mins).padStart(2, '0');
                    el.querySelector('.lb-countdown-secs').textContent = String(secs).padStart(2, '0');
                }
                update();
                setInterval(update, 1000);
            })();
        </script>
    `;
};

/**
 * Generate HTML for email context
 */
export const email = (props, options = {}) => {
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
                                        <span style="color: ${props.numberColor || '#635bff'}; font-size: 36px; font-weight: 700; display: block;">${String(days).padStart(2, '0')}</span>
                                        <span style="color: ${props.textColor || '#ffffff'}; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">Days</span>
                                    </div>
                                </td>
                                <td style="color: ${props.numberColor || '#635bff'}; font-size: 28px; font-weight: 700; padding: 0 4px;">:</td>
                                <td style="text-align: center; padding: 0 12px;">
                                    <div style="background-color: rgba(255,255,255,0.1); border-radius: 8px; padding: 12px 16px;">
                                        <span style="color: ${props.numberColor || '#635bff'}; font-size: 36px; font-weight: 700; display: block;">${String(hours).padStart(2, '0')}</span>
                                        <span style="color: ${props.textColor || '#ffffff'}; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">Hours</span>
                                    </div>
                                </td>
                                <td style="color: ${props.numberColor || '#635bff'}; font-size: 28px; font-weight: 700; padding: 0 4px;">:</td>
                                <td style="text-align: center; padding: 0 12px;">
                                    <div style="background-color: rgba(255,255,255,0.1); border-radius: 8px; padding: 12px 16px;">
                                        <span style="color: ${props.numberColor || '#635bff'}; font-size: 36px; font-weight: 700; display: block;">${String(minutes).padStart(2, '0')}</span>
                                        <span style="color: ${props.textColor || '#ffffff'}; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">Mins</span>
                                    </div>
                                </td>
                                <td style="color: ${props.numberColor || '#635bff'}; font-size: 28px; font-weight: 700; padding: 0 4px;">:</td>
                                <td style="text-align: center; padding: 0 12px;">
                                    <div style="background-color: rgba(255,255,255,0.1); border-radius: 8px; padding: 12px 16px;">
                                        <span style="color: ${props.numberColor || '#635bff'}; font-size: 36px; font-weight: 700; display: block;">${String(seconds).padStart(2, '0')}</span>
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
};

export default {
    page,
    email,
};
