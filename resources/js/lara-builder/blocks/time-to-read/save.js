/**
 * Time to Read Block - Save/Output Generators
 *
 * Page context: Returns placeholder for server-side rendering via render.php
 * Email context: Not supported (reading time is calculated from page content)
 *
 * The server-side render.php will:
 * - Count words in the entire page content
 * - Calculate reading time based on words per minute setting
 * - Display the formatted reading time
 */

/**
 * Generate placeholder for server-side rendering (page context)
 */
export const page = (props, options = {}) => {
    const serverProps = {
        wordsPerMinute: props.wordsPerMinute || 200,
        displayAsRange: props.displayAsRange !== false,
        prefix: props.prefix || '',
        suffix: props.suffix || '',
        align: props.align || 'left',
        color: props.color || '#666666',
        fontSize: props.fontSize || '14px',
        iconColor: props.iconColor || '#666666',
        showIcon: props.showIcon !== false,
        layoutStyles: props.layoutStyles || {},
        customCSS: props.customCSS || '',
        customClass: props.customClass || '',
    };

    // Escape for HTML attribute
    const propsJson = JSON.stringify(serverProps).replace(/'/g, '&#39;');

    return `<div data-lara-block="time-to-read" data-props='${propsJson}'></div>`;
};

/**
 * Generate HTML for email context (not supported for time-to-read)
 * Returns empty string as reading time calculation requires page content
 */
export const email = (props, options = {}) => {
    // Time to read is not meaningful in email context
    return '';
};

export default {
    page,
    email,
};
