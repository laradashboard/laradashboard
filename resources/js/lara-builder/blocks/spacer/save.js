/**
 * Spacer Block - Save/Output Generators
 *
 * Uses the new factory helpers for cleaner code.
 */

import { emailSpacer } from '@lara-builder/factory';
import { buildBlockClasses, mergeBlockStyles } from '@lara-builder/utils';

/**
 * Generate HTML for web/page context
 */
export const page = (props, options = {}) => {
    const classes = buildBlockClasses('spacer', props);
    const blockStyles = `height: ${props.height || '20px'}`;
    const mergedStyles = mergeBlockStyles(props, blockStyles);
    return `<div class="${classes}" style="${mergedStyles}"></div>`;
};

/**
 * Generate HTML for email context
 * Uses emailSpacer helper for proper email-safe output
 */
export const email = (props, options = {}) => {
    return emailSpacer(props.height || '20px');
};

export default { page, email };
