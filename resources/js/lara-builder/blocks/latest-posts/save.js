/**
 * Latest Posts Block - Save/Output Generator
 *
 * Always server-side rendered via render.php.
 * This save function generates the placeholder for server processing.
 */

export const page = (props, options = {}) => {
    const serverProps = {
        postsCount: props.postsCount || 6,
        columns: props.columns || 3,
        categorySlug: props.categorySlug || '',
        showExcerpt: props.showExcerpt !== false,
        showImage: props.showImage !== false,
        showDate: props.showDate !== false,
        showAuthor: props.showAuthor || false,
        headingText: props.headingText || '',
        layout: props.layout || 'grid',
        postRoute: props.postRoute || 'starter26.post',
        layoutStyles: props.layoutStyles || {},
        customClass: props.customClass || '',
    };

    const blockId = options.blockId || props._blockId || '';
    const propsJson = JSON.stringify(serverProps).replace(/'/g, '&#39;');

    return `<div data-lara-block="latest-posts" data-block-id="${blockId}" data-props='${propsJson}'></div>`;
};

export default { page };
