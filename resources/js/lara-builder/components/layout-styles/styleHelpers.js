/**
 * Helper functions to convert layout styles to CSS
 */

/**
 * Convert layout styles object to React inline CSS object
 */
export const layoutStylesToCSS = (layoutStyles = {}) => {
    const styles = {};

    // Process background
    if (layoutStyles.background) {
        const { color, image, size, position, repeat } = layoutStyles.background;
        if (color) styles.backgroundColor = color;
        if (image) {
            styles.backgroundImage = `url(${image})`;
            styles.backgroundSize = size || 'cover';
            styles.backgroundPosition = position || 'center';
            styles.backgroundRepeat = repeat || 'no-repeat';
        }
    }

    // Process margin
    if (layoutStyles.margin) {
        const { top, right, bottom, left } = layoutStyles.margin;
        if (top || right || bottom || left) {
            styles.marginTop = top || '0';
            styles.marginRight = right || '0';
            styles.marginBottom = bottom || '0';
            styles.marginLeft = left || '0';
        }
    }

    // Process padding
    if (layoutStyles.padding) {
        const { top, right, bottom, left } = layoutStyles.padding;
        if (top || right || bottom || left) {
            styles.paddingTop = top || '0';
            styles.paddingRight = right || '0';
            styles.paddingBottom = bottom || '0';
            styles.paddingLeft = left || '0';
        }
    }

    // Process sizing
    if (layoutStyles.width) styles.width = layoutStyles.width;
    if (layoutStyles.minWidth) styles.minWidth = layoutStyles.minWidth;
    if (layoutStyles.maxWidth) styles.maxWidth = layoutStyles.maxWidth;
    if (layoutStyles.height) styles.height = layoutStyles.height;
    if (layoutStyles.minHeight) styles.minHeight = layoutStyles.minHeight;
    if (layoutStyles.maxHeight) styles.maxHeight = layoutStyles.maxHeight;

    // Process typography
    if (layoutStyles.typography) {
        const { color, fontSize, textAlign, textTransform, fontFamily, fontWeight, fontStyle, lineHeight, letterSpacing, textDecoration } = layoutStyles.typography;
        if (color) styles.color = color;
        if (fontSize) styles.fontSize = fontSize;
        if (textAlign) styles.textAlign = textAlign;
        if (textTransform) styles.textTransform = textTransform;
        if (fontFamily) styles.fontFamily = fontFamily;
        if (fontWeight) styles.fontWeight = fontWeight;
        if (fontStyle) styles.fontStyle = fontStyle;
        if (lineHeight) styles.lineHeight = lineHeight;
        if (letterSpacing) styles.letterSpacing = letterSpacing;
        if (textDecoration) styles.textDecoration = textDecoration;
    }

    return styles;
};

/**
 * Convert layout styles to inline style string for HTML generation
 */
export const layoutStylesToInlineCSS = (layoutStyles = {}) => {
    const cssProperties = [];

    // Process background
    if (layoutStyles.background) {
        const { color, image, size, position, repeat } = layoutStyles.background;
        if (color) cssProperties.push(`background-color: ${color}`);
        if (image) {
            cssProperties.push(`background-image: url(${image})`);
            cssProperties.push(`background-size: ${size || 'cover'}`);
            cssProperties.push(`background-position: ${position || 'center'}`);
            cssProperties.push(`background-repeat: ${repeat || 'no-repeat'}`);
        }
    }

    // Process margin
    if (layoutStyles.margin) {
        const { top, right, bottom, left } = layoutStyles.margin;
        if (top) cssProperties.push(`margin-top: ${top}`);
        if (right) cssProperties.push(`margin-right: ${right}`);
        if (bottom) cssProperties.push(`margin-bottom: ${bottom}`);
        if (left) cssProperties.push(`margin-left: ${left}`);
    }

    // Process padding
    if (layoutStyles.padding) {
        const { top, right, bottom, left } = layoutStyles.padding;
        if (top) cssProperties.push(`padding-top: ${top}`);
        if (right) cssProperties.push(`padding-right: ${right}`);
        if (bottom) cssProperties.push(`padding-bottom: ${bottom}`);
        if (left) cssProperties.push(`padding-left: ${left}`);
    }

    // Process sizing
    if (layoutStyles.width) cssProperties.push(`width: ${layoutStyles.width}`);
    if (layoutStyles.minWidth) cssProperties.push(`min-width: ${layoutStyles.minWidth}`);
    if (layoutStyles.maxWidth) cssProperties.push(`max-width: ${layoutStyles.maxWidth}`);
    if (layoutStyles.height) cssProperties.push(`height: ${layoutStyles.height}`);
    if (layoutStyles.minHeight) cssProperties.push(`min-height: ${layoutStyles.minHeight}`);
    if (layoutStyles.maxHeight) cssProperties.push(`max-height: ${layoutStyles.maxHeight}`);

    // Process typography
    if (layoutStyles.typography) {
        const { color, fontSize, textAlign, textTransform, fontFamily, fontWeight, fontStyle, lineHeight, letterSpacing, textDecoration } = layoutStyles.typography;
        if (color) cssProperties.push(`color: ${color}`);
        if (fontSize) cssProperties.push(`font-size: ${fontSize}`);
        if (textAlign) cssProperties.push(`text-align: ${textAlign}`);
        if (textTransform) cssProperties.push(`text-transform: ${textTransform}`);
        if (fontFamily) cssProperties.push(`font-family: ${fontFamily}`);
        if (fontWeight) cssProperties.push(`font-weight: ${fontWeight}`);
        if (fontStyle) cssProperties.push(`font-style: ${fontStyle}`);
        if (lineHeight) cssProperties.push(`line-height: ${lineHeight}`);
        if (letterSpacing) cssProperties.push(`letter-spacing: ${letterSpacing}`);
        if (textDecoration) cssProperties.push(`text-decoration: ${textDecoration}`);
    }

    return cssProperties.join('; ');
};
