/**
 * Generate HTML - Re-exports from lara-builder EmailAdapter.
 *
 * This file provides backward compatibility for email-builder imports.
 * HTML generation is now handled by lara-builder/adapters/EmailAdapter.
 */

import { EmailAdapter } from '../adapters/EmailAdapter';

// Create a singleton adapter instance.
const emailAdapter = new EmailAdapter();

/**
 * Generate HTML for a single block.
 *
 * @param {Object} block - The block to generate HTML for
 * @param {Object} options - Options for HTML generation
 * @returns {string} - HTML string
 */
export const generateBlockHtml = (block, options = {}) => {
    return emailAdapter.generateBlockHtml(block, options);
};

/**
 * Generate complete email HTML from blocks.
 *
 * @param {Array} blocks - Array of blocks
 * @param {Object} canvasSettings - Canvas/email settings
 * @param {Object} options - Options for HTML generation
 * @returns {string} - Complete HTML document
 */
export const generateEmailHtml = (blocks, canvasSettings = {}, options = {}) => {
    const blocksHtml = blocks
        .map((block) => emailAdapter.generateBlockHtml(block, options))
        .join('');

    return emailAdapter.wrapOutput(blocksHtml, canvasSettings);
};

export default generateEmailHtml;
