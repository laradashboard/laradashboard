/**
 * Code Block - Save/Output Generators
 *
 * Page context: Returns placeholder for server-side rendering via render.php
 * Email context: Generates inline HTML (email clients can't call server)
 *
 * This approach ensures:
 * - Single source of truth (render.php) for page display
 * - Proper XSS protection (server-side escaping)
 * - Future syntax highlighting support (server-side)
 * - Email still works without server calls
 */

/**
 * Escape HTML entities for email context
 */
const escapeHtml = (text) => {
    if (!text) return '';
    return text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
};

/**
 * Generate placeholder for server-side rendering (page context)
 */
export const page = (props, options = {}) => {
    const serverProps = {
        code: props.code || '',
        language: props.language || 'plaintext',
        fontSize: props.fontSize || '14px',
        backgroundColor: props.backgroundColor || '#1e1e1e',
        textColor: props.textColor || '#d4d4d4',
        borderRadius: props.borderRadius || '8px',
        layoutStyles: props.layoutStyles || {},
        customCSS: props.customCSS || '',
        customClass: props.customClass || '',
    };

    // Escape for HTML attribute
    const propsJson = JSON.stringify(serverProps).replace(/'/g, '&#39;');

    return `<div data-lara-block="code" data-props='${propsJson}'></div>`;
};

/**
 * Generate HTML for email context (no server rendering available)
 */
export const email = (props, options = {}) => {
    const code = escapeHtml(props.code || '');
    const backgroundColor = props.backgroundColor || '#1e1e1e';
    const textColor = props.textColor || '#d4d4d4';
    const fontSize = props.fontSize || '14px';
    const borderRadius = props.borderRadius || '8px';

    return `
        <div style="background-color: ${backgroundColor}; border-radius: ${borderRadius}; padding: 16px; overflow-x: auto; font-family: monospace; font-size: ${fontSize}; line-height: 1.5; color: ${textColor};">
            <pre style="margin: 0; white-space: pre-wrap; word-wrap: break-word;">${code}</pre>
        </div>
    `;
};

export default {
    page,
    email,
};
