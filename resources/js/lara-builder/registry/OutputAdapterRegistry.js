/**
 * OutputAdapterRegistry - Registry for HTML Output Adapters
 *
 * Manages different output adapters for various contexts (email, web, etc.)
 * Each adapter knows how to convert blocks to HTML for its specific context.
 *
 * @example
 * // Get adapter for a context
 * const adapter = OutputAdapterRegistry.get('email');
 * const html = adapter.generateHtml(blocks, settings);
 *
 * // Register a custom adapter
 * OutputAdapterRegistry.register('custom', new CustomAdapter());
 */

import { LaraHooks } from '../hooks-system/LaraHooks';
import { BuilderHooks } from '../hooks-system/HookNames';

class OutputAdapterRegistryClass {
    constructor() {
        this.adapters = new Map();
        this.defaultAdapter = null;
    }

    /**
     * Register an output adapter
     * @param {string} context - The context name
     * @param {BaseAdapter} adapter - The adapter instance
     * @returns {OutputAdapterRegistryClass}
     */
    register(context, adapter) {
        if (!adapter) {
            console.error(`[OutputAdapterRegistry] Adapter is required for context: ${context}`);
            return this;
        }

        this.adapters.set(context, adapter);

        // Set first registered as default
        if (!this.defaultAdapter) {
            this.defaultAdapter = context;
        }

        return this;
    }

    /**
     * Get an adapter for a context
     * @param {string} context - The context name
     * @returns {BaseAdapter|null}
     */
    get(context) {
        // Try exact match first
        if (this.adapters.has(context)) {
            return this.adapters.get(context);
        }

        // Fall back to default adapter
        if (this.defaultAdapter) {
            return this.adapters.get(this.defaultAdapter);
        }

        return null;
    }

    /**
     * Check if a context has an adapter
     * @param {string} context
     * @returns {boolean}
     */
    has(context) {
        return this.adapters.has(context);
    }

    /**
     * Get all registered context names
     * @returns {string[]}
     */
    getContexts() {
        return Array.from(this.adapters.keys());
    }

    /**
     * Set the default adapter context
     * @param {string} context
     * @returns {OutputAdapterRegistryClass}
     */
    setDefault(context) {
        if (this.adapters.has(context)) {
            this.defaultAdapter = context;
        } else {
            console.warn(`[OutputAdapterRegistry] Context not found: ${context}`);
        }
        return this;
    }

    /**
     * Generate HTML for a context
     * @param {string} context - The context name
     * @param {Array} blocks - The blocks to convert
     * @param {Object} settings - Canvas settings
     * @returns {string}
     */
    generateHtml(context, blocks, settings = {}) {
        const adapter = this.get(context);
        if (!adapter) {
            console.error(`[OutputAdapterRegistry] No adapter found for context: ${context}`);
            return '';
        }

        // Fire before action
        LaraHooks.doAction(BuilderHooks.ACTION_HTML_BEFORE_GENERATE, blocks, settings, context);

        // Generate HTML
        let html = adapter.generateHtml(blocks, settings);

        // Apply filter
        html = LaraHooks.applyFilters(BuilderHooks.FILTER_HTML_GENERATED, html, blocks, settings, context);

        // Fire after action
        LaraHooks.doAction(BuilderHooks.ACTION_HTML_AFTER_GENERATE, html, blocks, settings, context);

        return html;
    }

    /**
     * Get default settings for a context
     * @param {string} context
     * @returns {Object}
     */
    getDefaultSettings(context) {
        const adapter = this.get(context);
        if (!adapter) return {};

        let settings = adapter.getDefaultSettings();

        // Apply filter for customization
        settings = LaraHooks.applyFilters(
            BuilderHooks.FILTER_CANVAS_DEFAULT_SETTINGS,
            settings,
            context
        );

        return settings;
    }

    /**
     * Unregister an adapter
     * @param {string} context
     * @returns {OutputAdapterRegistryClass}
     */
    unregister(context) {
        this.adapters.delete(context);
        if (this.defaultAdapter === context) {
            this.defaultAdapter = this.adapters.keys().next().value || null;
        }
        return this;
    }

    /**
     * Reset the registry
     */
    reset() {
        this.adapters.clear();
        this.defaultAdapter = null;
    }
}

// Create singleton instance
const OutputAdapterRegistry = new OutputAdapterRegistryClass();

// Expose globally
if (typeof window !== 'undefined') {
    window.LaraBuilderOutputAdapterRegistry = OutputAdapterRegistry;
}

export { OutputAdapterRegistry, OutputAdapterRegistryClass };
export default OutputAdapterRegistry;
