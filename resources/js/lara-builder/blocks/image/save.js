/**
 * Image Block - Save/Output Generators
 *
 * Generates HTML output for different contexts (page/web and email).
 */

import { buildBlockClasses } from '@lara-builder/utils';

/**
 * Separate layout styles into image-applicable and wrapper-applicable styles
 * Background styles need to go on a wrapper div, not the img element
 */
const separateLayoutStyles = (layoutStyles = {}) => {
    const wrapperStyles = [];
    const imgStyles = [];

    // Background goes on wrapper (can't apply to img element)
    if (layoutStyles.background) {
        const { color, image, size, position, repeat } = layoutStyles.background;
        if (color) wrapperStyles.push(`background-color: ${color}`);
        if (image) {
            wrapperStyles.push(`background-image: url(${image})`);
            wrapperStyles.push(`background-size: ${size || 'cover'}`);
            wrapperStyles.push(`background-position: ${position || 'center'}`);
            wrapperStyles.push(`background-repeat: ${repeat || 'no-repeat'}`);
        }
    }

    // Margin goes on wrapper
    if (layoutStyles.margin) {
        const { top, right, bottom, left } = layoutStyles.margin;
        if (top) wrapperStyles.push(`margin-top: ${top}`);
        if (right) wrapperStyles.push(`margin-right: ${right}`);
        if (bottom) wrapperStyles.push(`margin-bottom: ${bottom}`);
        if (left) wrapperStyles.push(`margin-left: ${left}`);
    }

    // Padding goes on wrapper
    if (layoutStyles.padding) {
        const { top, right, bottom, left } = layoutStyles.padding;
        if (top) wrapperStyles.push(`padding-top: ${top}`);
        if (right) wrapperStyles.push(`padding-right: ${right}`);
        if (bottom) wrapperStyles.push(`padding-bottom: ${bottom}`);
        if (left) wrapperStyles.push(`padding-left: ${left}`);
    }

    // Border goes on img
    if (layoutStyles.border) {
        const { width = {}, style, color, radius = {} } = layoutStyles.border;
        if (width.top) imgStyles.push(`border-top-width: ${width.top}`);
        if (width.right) imgStyles.push(`border-right-width: ${width.right}`);
        if (width.bottom) imgStyles.push(`border-bottom-width: ${width.bottom}`);
        if (width.left) imgStyles.push(`border-left-width: ${width.left}`);
        if (style) imgStyles.push(`border-style: ${style}`);
        if (color) imgStyles.push(`border-color: ${color}`);
        if (radius.topLeft) imgStyles.push(`border-top-left-radius: ${radius.topLeft}`);
        if (radius.topRight) imgStyles.push(`border-top-right-radius: ${radius.topRight}`);
        if (radius.bottomLeft) imgStyles.push(`border-bottom-left-radius: ${radius.bottomLeft}`);
        if (radius.bottomRight) imgStyles.push(`border-bottom-right-radius: ${radius.bottomRight}`);
    }

    // Box shadow goes on img
    if (layoutStyles.boxShadow) {
        const { x, y, blur, spread, color, inset } = layoutStyles.boxShadow;
        if (x || y || blur || spread || color) {
            const shadowValue = `${inset ? 'inset ' : ''}${x || '0px'} ${y || '0px'} ${blur || '0px'} ${spread || '0px'} ${color || 'rgba(0,0,0,0.1)'}`;
            imgStyles.push(`box-shadow: ${shadowValue}`);
        }
    }

    return {
        wrapper: wrapperStyles.join('; '),
        img: imgStyles.join('; '),
    };
};

/**
 * Generate HTML for web/page context
 */
export const page = (props, options = {}) => {
    const type = 'image';
    const isCustomWidth = props.width === 'custom' && props.customWidth;
    const isCustomHeight = props.height === 'custom' && props.customHeight;

    // Separate layout styles for wrapper vs img element
    const separatedStyles = separateLayoutStyles(props?.layoutStyles);
    const hasWrapperStyles = separatedStyles.wrapper || props?.layoutStyles?.background?.image;

    // Image-specific styles
    const imgStyles = [];
    if (isCustomWidth) {
        imgStyles.push(`width: ${props.customWidth}`);
        imgStyles.push(`max-width: ${props.customWidth}`);
    } else {
        imgStyles.push('max-width: 100%');
    }
    if (isCustomHeight) {
        imgStyles.push(`height: ${props.customHeight}`);
        imgStyles.push('object-fit: cover');
    } else {
        imgStyles.push('height: auto');
    }

    // Add layout styles that apply to img (border, shadow)
    const allImgStyles = [imgStyles.join('; '), separatedStyles.img, props?.customCSS].filter(Boolean).join('; ');

    const blockClasses = buildBlockClasses(type, props);
    const img = `<img src="${props.src || ''}" alt="${props.alt || ''}" class="${blockClasses}" style="${allImgStyles}" loading="lazy" />`;

    const align = props.align || 'center';
    const justifyContent = align === 'left' ? 'flex-start' : align === 'right' ? 'flex-end' : 'center';

    // Wrap img with link if needed
    let content = props.link
        ? `<a href="${props.link}" target="_blank" rel="noopener noreferrer" class="lb-image-link">${img}</a>`
        : img;

    // If there are wrapper styles (background, margin, padding), wrap in a div
    if (hasWrapperStyles) {
        content = `<div class="lb-image-bg-wrapper" style="${separatedStyles.wrapper}">${content}</div>`;
    }

    return `<figure class="lb-image-wrapper" style="display: flex; justify-content: ${justifyContent}; margin: 0 0 16px 0;">${content}</figure>`;
};

/**
 * Generate HTML for email context
 */
export const email = (props, options = {}) => {
    const isCustomWidth = props.width === 'custom' && props.customWidth;
    const isCustomHeight = props.height === 'custom' && props.customHeight;
    const imgWidth = isCustomWidth ? props.customWidth : (props.width || '100%');
    const imgHeight = isCustomHeight ? props.customHeight : (props.height || 'auto');
    const imgStyle = `max-width: ${imgWidth};${isCustomWidth ? ` width: ${props.customWidth};` : ''} height: ${imgHeight}; display: block; margin: 0 auto;${imgHeight !== 'auto' ? ' object-fit: cover;' : ''}`;
    const img = `<img src="${props.src || ''}" alt="${props.alt || ''}" style="${imgStyle}" />`;

    return props.link
        ? `<a href="${props.link}" target="_blank" style="display: block; text-align: ${props.align || 'center'};">${img}</a>`
        : `<div style="text-align: ${props.align || 'center'};">${img}</div>`;
};

export default {
    page,
    email,
};
