/**
 * Table of Contents Block - Save/Output Generators
 *
 * This block is fully server-rendered via render.php.
 * save.js only outputs a placeholder with data-lara-block attribute
 * that BlockRenderer will replace with server-rendered content.
 */

/**
 * Extract minimal block data needed for TOC (only type, id, and heading-relevant props)
 * This avoids including rendered HTML which could break the data-props attribute parsing.
 */
const extractMinimalBlocks = (blocks) => {
    if (!Array.isArray(blocks)) return [];

    return blocks.map((block) => {
        const minimal = {
            type: block.type,
            id: block.id,
        };

        // Only include props needed for heading scanning
        if (block.type === 'heading' && block.props) {
            minimal.props = {
                text: block.props.text,
                level: block.props.level,
            };
        }

        // Handle nested blocks in columns
        if (block.props?.children && Array.isArray(block.props.children)) {
            minimal.props = minimal.props || {};
            minimal.props.children = block.props.children.map((column) =>
                Array.isArray(column) ? extractMinimalBlocks(column) : column
            );
        }

        return minimal;
    });
};

/**
 * Generate placeholder for server-side rendering
 */
export const page = (props, options = {}) => {
    const {
        title = 'Table of Contents',
        showTitle = true,
        minLevel = 'h1',
        maxLevel = 'h4',
        listStyle = 'bullet',
        backgroundColor = '#f8fafc',
        borderColor = '#e2e8f0',
        titleColor = '#1e293b',
        linkColor = '#635bff',
    } = props;

    // Extract minimal block data for heading scanning (avoids HTML in JSON)
    const allBlocks = extractMinimalBlocks(options.allBlocks || []);

    // Props for server-side rendering
    const serverProps = {
        title,
        showTitle,
        minLevel,
        maxLevel,
        listStyle,
        backgroundColor,
        borderColor,
        titleColor,
        linkColor,
        layoutStyles: props.layoutStyles || {},
        customCSS: props.customCSS || '',
        customClass: props.customClass || '',
        _allBlocks: allBlocks,
    };

    // Escape for HTML attribute (single quotes wrap, JSON uses double quotes)
    const propsJson = JSON.stringify(serverProps).replace(/'/g, '&#39;');

    // Minimal placeholder - will be fully replaced by render.php
    return `<div data-lara-block="toc" data-props='${propsJson}'></div>`;
};

/**
 * Email context - TOC not supported
 */
export const email = () => null;

export default {
    page,
    email,
};
