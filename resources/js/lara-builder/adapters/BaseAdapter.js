/**
 * BaseAdapter - Abstract Base Class for Output Adapters
 *
 * Defines the interface that all output adapters must implement.
 * Adapters convert blocks to HTML for specific contexts (email, web, etc.)
 */

import { LaraHooks } from '../hooks-system/LaraHooks';
import { BuilderHooks } from '../hooks-system/HookNames';

export class BaseAdapter {
    /**
     * @param {string} context - The context this adapter serves
     */
    constructor(context) {
        this.context = context;
    }

    /**
     * Get default canvas settings for this context
     * @returns {Object}
     */
    getDefaultSettings() {
        return {};
    }

    /**
     * Generate HTML from blocks
     * @param {Array} blocks - Array of block data
     * @param {Object} settings - Canvas/builder settings
     * @returns {string} - Generated HTML
     */
    generateHtml(blocks, settings = {}) {
        const mergedSettings = { ...this.getDefaultSettings(), ...settings };

        // Fire before action
        LaraHooks.doAction(BuilderHooks.ACTION_HTML_BEFORE_GENERATE, blocks, mergedSettings, this.context);

        // Generate blocks HTML - pass allBlocks in options for blocks that need context (like TOC)
        const blocksHtml = blocks
            .map((block) => this.generateBlockHtml(block, { settings: mergedSettings, allBlocks: blocks }))
            .join('');

        // Wrap the output
        let html = this.wrapOutput(blocksHtml, mergedSettings);

        // Apply filter
        html = LaraHooks.applyFilters(BuilderHooks.FILTER_HTML_GENERATED, html, blocks, mergedSettings, this.context);

        // Fire after action
        LaraHooks.doAction(BuilderHooks.ACTION_HTML_AFTER_GENERATE, html, blocks, mergedSettings, this.context);

        return html;
    }

    /**
     * Generate HTML for a single block
     * @param {Object} block - Block data
     * @param {Object} options - Generation options
     * @returns {string} - Generated HTML
     */
    generateBlockHtml(block, options = {}) {
        throw new Error('generateBlockHtml must be implemented by subclass');
    }

    /**
     * Wrap the final HTML output
     * @param {string} content - Inner HTML content
     * @param {Object} settings - Canvas settings
     * @returns {string} - Wrapped HTML
     */
    wrapOutput(content, settings) {
        return content;
    }

    /**
     * Validate if a block type is supported by this adapter
     * @param {string} blockType
     * @returns {boolean}
     */
    isBlockSupported(blockType) {
        return true;
    }

    /**
     * Get CSS styles for preview mode
     * @returns {string}
     */
    getPreviewStyles() {
        return '';
    }
}

export default BaseAdapter;
