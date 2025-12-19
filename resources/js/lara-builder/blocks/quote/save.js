/**
 * Quote Block - Save/Output Generators
 *
 * Page context: Returns placeholder for server-side rendering via render.php
 * Email context: Generates inline HTML (email clients can't call server)
 *
 * This approach ensures:
 * - Single source of truth (render.php) for page display
 * - Semantic HTML with proper blockquote/cite elements
 * - Email still works without server calls
 */

/**
 * Generate placeholder for server-side rendering (page context)
 */
export const page = (props, options = {}) => {
    const serverProps = {
        text: props.text || '',
        author: props.author || '',
        authorTitle: props.authorTitle || '',
        align: props.align || 'left',
        borderColor: props.borderColor || '#635bff',
        backgroundColor: props.backgroundColor || '#f8fafc',
        textColor: props.textColor || '#475569',
        authorColor: props.authorColor || '#1e293b',
        layoutStyles: props.layoutStyles || {},
        customCSS: props.customCSS || '',
        customClass: props.customClass || '',
    };

    // Escape for HTML attribute
    const propsJson = JSON.stringify(serverProps).replace(/'/g, '&#39;');

    return `<div data-lara-block="quote" data-props='${propsJson}'></div>`;
};

/**
 * Generate HTML for email context (no server rendering available)
 */
export const email = (props, options = {}) => {
    const text = props.text || '';
    const author = props.author || '';
    const authorTitle = props.authorTitle || '';
    const align = props.align || 'left';
    const borderColor = props.borderColor || '#635bff';
    const backgroundColor = props.backgroundColor || '#f8fafc';
    const textColor = props.textColor || '#475569';
    const authorColor = props.authorColor || '#1e293b';

    let authorHtml = '';
    if (author) {
        authorHtml = `<p style="color: ${authorColor}; font-size: 14px; font-weight: 600; margin: 0;">${author}</p>`;
    }

    let authorTitleHtml = '';
    if (authorTitle) {
        authorTitleHtml = `<p style="color: ${textColor}; font-size: 12px; margin: 0;">${authorTitle}</p>`;
    }

    return `
        <div style="padding: 20px; padding-left: 24px; background-color: ${backgroundColor}; border-left: 4px solid ${borderColor}; text-align: ${align}; border-radius: 4px; margin: 10px 0;">
            <p style="color: ${textColor}; font-size: 16px; font-style: italic; line-height: 1.6; margin: 0 0 12px 0;">"${text}"</p>
            ${authorHtml}
            ${authorTitleHtml}
        </div>
    `;
};

export default {
    page,
    email,
};
