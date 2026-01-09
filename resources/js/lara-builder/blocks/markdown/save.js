/**
 * Markdown Block - Save/Output Generators
 *
 * Page context: Returns placeholder for server-side rendering via render.php
 * Email context: Not supported (external URLs don't work in email clients)
 *
 * Supports two modes:
 * - content: Direct markdown content written in the editor
 * - url: Fetch markdown from external URLs (GitHub, GitLab, etc.)
 */

/**
 * Generate placeholder for server-side rendering (page context)
 */
export const page = (props, options = {}) => {
    const serverProps = {
        sourceType: props.sourceType || 'content',
        content: props.content || '',
        url: props.url || '',
        showSource: props.showSource !== false,
        cacheEnabled: props.cacheEnabled !== false,
        layoutStyles: props.layoutStyles || {},
    };

    // Escape for HTML attribute (single quotes wrap, JSON uses double quotes)
    const propsJson = JSON.stringify(serverProps).replace(/'/g, '&#39;');

    // Placeholder - will be fully replaced by render.php
    return `<div data-lara-block="markdown" data-props='${propsJson}'></div>`;
};

/**
 * Email context is not supported for markdown blocks
 * since external URLs and dynamic content don't work well in email clients
 */
export const email = (props, options = {}) => {
    return `<div style="padding: 16px; background: #f3f4f6; border-radius: 8px; text-align: center; color: #6b7280; font-size: 14px;">
        <p style="margin: 0;">Markdown content is not supported in emails.</p>
        <p style="margin: 8px 0 0; font-size: 12px;">Please use other block types for email templates.</p>
    </div>`;
};

export default {
    page,
    email,
};
