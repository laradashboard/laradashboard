/**
 * LaraHooks - WordPress-style JavaScript Hook System
 *
 * Provides a filter and action hook system for extensibility.
 * Mirrors the PHP Hook system in the Laravel backend.
 *
 * @example
 * // Add a filter
 * LaraHooks.addFilter('builder.blocks', (blocks) => {
 *   return [...blocks, myCustomBlock];
 * }, 10);
 *
 * // Apply filters
 * const filteredBlocks = LaraHooks.applyFilters('builder.blocks', blocks);
 *
 * // Add an action
 * LaraHooks.addAction('builder.block.added', (block) => {
 *   console.log('Block added:', block);
 * });
 *
 * // Execute action
 * LaraHooks.doAction('builder.block.added', newBlock);
 */

class LaraHooksSystem {
    constructor() {
        this.filters = new Map();
        this.actions = new Map();
        this.debug = false;
    }

    /**
     * Enable or disable debug mode
     * @param {boolean} enabled
     */
    setDebug(enabled) {
        this.debug = enabled;
    }

    /**
     * Log debug messages if debug mode is enabled
     * @private
     */
    _log(type, hookName, ...args) {
        if (this.debug) {
            console.log(`[LaraHooks] ${type}: ${hookName}`, ...args);
        }
    }

    /**
     * Add a filter callback
     * @param {string} hookName - The hook name
     * @param {Function} callback - The callback function
     * @param {number} priority - Priority (lower runs first, default 10)
     * @returns {LaraHooksSystem} - Returns this for chaining
     */
    addFilter(hookName, callback, priority = 10) {
        if (typeof callback !== 'function') {
            console.error(`[LaraHooks] addFilter: callback must be a function for hook "${hookName}"`);
            return this;
        }

        if (!this.filters.has(hookName)) {
            this.filters.set(hookName, []);
        }

        this.filters.get(hookName).push({ callback, priority });
        this.filters.get(hookName).sort((a, b) => a.priority - b.priority);

        this._log('Filter added', hookName, { priority });

        return this;
    }

    /**
     * Apply filters to a value
     * @param {string} hookName - The hook name
     * @param {*} value - The value to filter
     * @param {...*} args - Additional arguments passed to callbacks
     * @returns {*} - The filtered value
     */
    applyFilters(hookName, value, ...args) {
        const hooks = this.filters.get(hookName);

        if (!hooks || hooks.length === 0) {
            return value;
        }

        this._log('Applying filters', hookName, { hookCount: hooks.length });

        return hooks.reduce((currentValue, { callback }) => {
            try {
                const result = callback(currentValue, ...args);
                return result !== undefined ? result : currentValue;
            } catch (error) {
                console.error(`[LaraHooks] Error in filter "${hookName}":`, error);
                return currentValue;
            }
        }, value);
    }

    /**
     * Add an action callback
     * @param {string} hookName - The hook name
     * @param {Function} callback - The callback function
     * @param {number} priority - Priority (lower runs first, default 10)
     * @returns {LaraHooksSystem} - Returns this for chaining
     */
    addAction(hookName, callback, priority = 10) {
        if (typeof callback !== 'function') {
            console.error(`[LaraHooks] addAction: callback must be a function for hook "${hookName}"`);
            return this;
        }

        if (!this.actions.has(hookName)) {
            this.actions.set(hookName, []);
        }

        this.actions.get(hookName).push({ callback, priority });
        this.actions.get(hookName).sort((a, b) => a.priority - b.priority);

        this._log('Action added', hookName, { priority });

        return this;
    }

    /**
     * Execute action callbacks
     * @param {string} hookName - The hook name
     * @param {...*} args - Arguments to pass to callbacks
     */
    doAction(hookName, ...args) {
        const hooks = this.actions.get(hookName);

        if (!hooks || hooks.length === 0) {
            return;
        }

        this._log('Executing action', hookName, { hookCount: hooks.length });

        hooks.forEach(({ callback }) => {
            try {
                callback(...args);
            } catch (error) {
                console.error(`[LaraHooks] Error in action "${hookName}":`, error);
            }
        });
    }

    /**
     * Remove a specific filter callback
     * @param {string} hookName - The hook name
     * @param {Function} callback - The callback to remove
     * @returns {LaraHooksSystem} - Returns this for chaining
     */
    removeFilter(hookName, callback) {
        const hooks = this.filters.get(hookName);
        if (hooks) {
            const index = hooks.findIndex((h) => h.callback === callback);
            if (index > -1) {
                hooks.splice(index, 1);
                this._log('Filter removed', hookName);
            }
        }
        return this;
    }

    /**
     * Remove a specific action callback
     * @param {string} hookName - The hook name
     * @param {Function} callback - The callback to remove
     * @returns {LaraHooksSystem} - Returns this for chaining
     */
    removeAction(hookName, callback) {
        const hooks = this.actions.get(hookName);
        if (hooks) {
            const index = hooks.findIndex((h) => h.callback === callback);
            if (index > -1) {
                hooks.splice(index, 1);
                this._log('Action removed', hookName);
            }
        }
        return this;
    }

    /**
     * Remove all filters for a hook
     * @param {string} hookName - The hook name
     * @returns {LaraHooksSystem} - Returns this for chaining
     */
    removeAllFilters(hookName) {
        this.filters.delete(hookName);
        this._log('All filters removed', hookName);
        return this;
    }

    /**
     * Remove all actions for a hook
     * @param {string} hookName - The hook name
     * @returns {LaraHooksSystem} - Returns this for chaining
     */
    removeAllActions(hookName) {
        this.actions.delete(hookName);
        this._log('All actions removed', hookName);
        return this;
    }

    /**
     * Check if a hook has any callbacks registered
     * @param {string} hookName - The hook name
     * @param {string} type - 'filter' or 'action'
     * @returns {boolean}
     */
    hasHook(hookName, type = 'filter') {
        const map = type === 'filter' ? this.filters : this.actions;
        const hooks = map.get(hookName);
        return hooks && hooks.length > 0;
    }

    /**
     * Get the number of callbacks registered for a hook
     * @param {string} hookName - The hook name
     * @param {string} type - 'filter' or 'action'
     * @returns {number}
     */
    getHookCount(hookName, type = 'filter') {
        const map = type === 'filter' ? this.filters : this.actions;
        const hooks = map.get(hookName);
        return hooks ? hooks.length : 0;
    }

    /**
     * Get all registered hook names
     * @param {string} type - 'filter', 'action', or 'all'
     * @returns {string[]}
     */
    getRegisteredHooks(type = 'all') {
        const hooks = [];

        if (type === 'all' || type === 'filter') {
            hooks.push(...Array.from(this.filters.keys()).map((name) => `filter:${name}`));
        }

        if (type === 'all' || type === 'action') {
            hooks.push(...Array.from(this.actions.keys()).map((name) => `action:${name}`));
        }

        return hooks;
    }

    /**
     * Clear all hooks (useful for testing)
     */
    reset() {
        this.filters.clear();
        this.actions.clear();
        this._log('System reset', 'all hooks cleared');
    }
}

// Create singleton instance
const LaraHooks = new LaraHooksSystem();

// Expose globally for module access
if (typeof window !== 'undefined') {
    window.LaraHooks = LaraHooks;
}

export { LaraHooks, LaraHooksSystem };
export default LaraHooks;
