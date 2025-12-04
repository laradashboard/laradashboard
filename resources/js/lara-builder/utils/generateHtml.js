/**
 * Legacy generateHtml Export
 *
 * This provides backward compatibility for existing code that imports
 * generateHtml from email-builder. It now uses the EmailAdapter internally.
 */

import { EmailAdapter } from '../adapters/EmailAdapter';

// Create a singleton adapter instance
const emailAdapter = new EmailAdapter();

/**
 * Generate email HTML from blocks and settings
 *
 * @param {Array} blocks - Array of block data
 * @param {Object} settings - Canvas settings
 * @param {Object} options - Additional options
 * @returns {string} - Generated HTML
 *
 * @deprecated Use OutputAdapterRegistry.generateHtml('email', blocks, settings) instead
 */
export function generateEmailHtml(blocks, settings = {}, options = {}) {
    return emailAdapter.generateHtml(blocks, { ...settings, previewMode: options.previewMode });
}

/**
 * Generate HTML for a single block
 *
 * @param {Object} block - Block data
 * @param {Object} options - Generation options
 * @returns {string} - Generated HTML
 *
 * @deprecated Use OutputAdapterRegistry.get('email').generateBlockHtml(block, options) instead
 */
export function generateBlockHtml(block, options = {}) {
    return emailAdapter.generateBlockHtml(block, options);
}

// Default export for backward compatibility
export default generateEmailHtml;
