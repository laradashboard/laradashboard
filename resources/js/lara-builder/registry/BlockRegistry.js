/**
 * BlockRegistry - Enhanced Block Registration System
 *
 * Provides block registration with context filtering, categories,
 * and extensibility through hooks.
 *
 * @example
 * // Register a block
 * blockRegistry.register({
 *   type: 'my-block',
 *   label: 'My Block',
 *   category: 'Content',
 *   icon: 'lucide:box',
 *   contexts: ['email', 'page'],
 *   defaultProps: { text: 'Hello' },
 *   component: MyBlockComponent,
 * });
 *
 * // Get blocks for a context
 * const emailBlocks = blockRegistry.getBlocksForContext('email');
 */

import { LaraHooks } from '../hooks-system/LaraHooks';
import { BuilderHooks } from '../hooks-system/HookNames';

/**
 * @typedef {Object} BlockSupports
 * @property {boolean} [align=true] - Supports text alignment
 * @property {boolean} [spacing=true] - Supports margin/padding
 * @property {boolean} [colors=true] - Supports color customization
 * @property {boolean} [nesting=false] - Can contain nested blocks
 * @property {boolean} [html=true] - Can generate HTML
 * @property {boolean} [duplicate=true] - Can be duplicated
 * @property {boolean} [remove=true] - Can be removed
 */

/**
 * @typedef {Object} BlockDefinition
 * @property {string} type - Unique block identifier
 * @property {string} label - Display name
 * @property {string} category - Category for grouping
 * @property {string} icon - Iconify icon name
 * @property {string[]} [contexts=['*']] - Allowed contexts
 * @property {Object} defaultProps - Default property values
 * @property {React.ComponentType} block - React component for rendering in builder canvas
 * @property {React.ComponentType} [editor] - Custom property editor component
 * @property {Array} [fields] - Field definitions for auto-generated editor
 * @property {Object} [save] - Per-context HTML generators { page, email, ... }
 * @property {Function} [validate] - Validation function
 * @property {Object} [transform] - Transform from other blocks
 * @property {BlockSupports} [supports] - Feature support flags
 * @property {string} [description] - Block description
 * @property {string[]} [keywords] - Search keywords
 */

class BlockRegistryClass {
    constructor() {
        this.blocks = new Map();
        this.categories = new Map();
        this.components = new Map();
        this.initialized = false;
    }

    /**
     * Register a new block type
     * @param {BlockDefinition} definition
     * @returns {BlockRegistryClass} - Returns this for chaining
     */
    register(definition) {
        if (!definition.type) {
            console.error('[BlockRegistry] Block type is required');
            return this;
        }

        if (!definition.label) {
            console.error(`[BlockRegistry] Block label is required for type: ${definition.type}`);
            return this;
        }

        // Normalize the block definition
        const block = {
            type: definition.type,
            label: definition.label,
            category: definition.category || 'Content',
            icon: definition.icon || 'lucide:box',
            contexts: definition.contexts || ['*'],
            defaultProps: definition.defaultProps || {},
            block: definition.block || null,
            editor: definition.editor || null,
            fields: definition.fields || [], // Field definitions for auto-generated editor
            save: definition.save || {},
            validate: definition.validate || (() => true),
            transform: definition.transform || null,
            description: definition.description || '',
            keywords: definition.keywords || [],
            supports: {
                align: true,
                spacing: true,
                colors: true,
                nesting: false,
                html: true,
                duplicate: true,
                remove: true,
                ...definition.supports,
            },
        };

        // Store the block
        this.blocks.set(definition.type, block);

        // Index by category
        if (!this.categories.has(block.category)) {
            this.categories.set(block.category, []);
        }
        const categoryBlocks = this.categories.get(block.category);
        const existingIndex = categoryBlocks.findIndex((b) => b.type === block.type);
        if (existingIndex > -1) {
            categoryBlocks[existingIndex] = block;
        } else {
            categoryBlocks.push(block);
        }

        // Store component mapping
        if (block.block) {
            this.components.set(definition.type, block.block);
        }

        // Fire action hook
        LaraHooks.doAction(BuilderHooks.ACTION_BLOCK_REGISTERED, block);

        return this;
    }

    /**
     * Register multiple blocks at once
     * @param {BlockDefinition[]} definitions
     * @returns {BlockRegistryClass}
     */
    registerMany(definitions) {
        definitions.forEach((def) => this.register(def));
        return this;
    }

    /**
     * Unregister a block type
     * @param {string} type - Block type to remove
     * @returns {BlockRegistryClass}
     */
    unregister(type) {
        const block = this.blocks.get(type);
        if (block) {
            this.blocks.delete(type);
            this.components.delete(type);

            // Remove from category
            const categoryBlocks = this.categories.get(block.category);
            if (categoryBlocks) {
                const index = categoryBlocks.findIndex((b) => b.type === type);
                if (index > -1) {
                    categoryBlocks.splice(index, 1);
                }
            }

            LaraHooks.doAction(BuilderHooks.ACTION_BLOCK_UNREGISTERED, type);
        }
        return this;
    }

    /**
     * Get a block definition by type
     * @param {string} type
     * @returns {BlockDefinition|undefined}
     */
    get(type) {
        return this.blocks.get(type);
    }

    /**
     * Check if a block type is registered
     * @param {string} type
     * @returns {boolean}
     */
    has(type) {
        return this.blocks.has(type);
    }

    /**
     * Get all registered blocks
     * @returns {BlockDefinition[]}
     */
    getAll() {
        return Array.from(this.blocks.values());
    }

    /**
     * Get blocks available for a specific context
     * @param {string} context - The context (email, page, campaign, etc.)
     * @returns {BlockDefinition[]}
     */
    getBlocksForContext(context) {
        let blocks = Array.from(this.blocks.values()).filter((block) => {
            return block.contexts.includes('*') || block.contexts.includes(context);
        });

        // Apply context-specific filter
        blocks = LaraHooks.applyFilters(`${BuilderHooks.FILTER_BLOCKS}.${context}`, blocks, context);

        // Apply general filter
        blocks = LaraHooks.applyFilters(BuilderHooks.FILTER_BLOCKS, blocks, context);

        return blocks;
    }

    /**
     * Get blocks grouped by category
     * @param {string} [context] - Optional context filter
     * @returns {Object}
     */
    getByCategory(context = null) {
        const blocks = context ? this.getBlocksForContext(context) : this.getAll();

        const grouped = {};
        blocks.forEach((block) => {
            if (!grouped[block.category]) {
                grouped[block.category] = [];
            }
            grouped[block.category].push(block);
        });

        // Apply filter for category order and grouping
        return LaraHooks.applyFilters(BuilderHooks.FILTER_BLOCK_CATEGORIES, grouped, context);
    }

    /**
     * Get all category names
     * @returns {string[]}
     */
    getCategories() {
        return Array.from(this.categories.keys());
    }

    /**
     * Get the React component for a block type
     * @param {string} type
     * @returns {React.ComponentType|null}
     */
    getComponent(type) {
        return this.components.get(type) || null;
    }

    /**
     * Set the component for a block type (useful for lazy loading)
     * @param {string} type
     * @param {React.ComponentType} component
     */
    setComponent(type, component) {
        this.components.set(type, component);
        const block = this.blocks.get(type);
        if (block) {
            block.block = component;
        }
    }

    /**
     * Search blocks by keyword
     * @param {string} query
     * @param {string} [context] - Optional context filter
     * @returns {BlockDefinition[]}
     */
    search(query, context = null) {
        const searchLower = query.toLowerCase();
        const blocks = context ? this.getBlocksForContext(context) : this.getAll();

        return blocks.filter((block) => {
            // Search in label
            if (block.label.toLowerCase().includes(searchLower)) return true;
            // Search in type
            if (block.type.toLowerCase().includes(searchLower)) return true;
            // Search in category
            if (block.category.toLowerCase().includes(searchLower)) return true;
            // Search in keywords
            if (block.keywords.some((kw) => kw.toLowerCase().includes(searchLower))) return true;
            // Search in description
            if (block.description.toLowerCase().includes(searchLower)) return true;
            return false;
        });
    }

    /**
     * Create a new block instance with default props
     * @param {string} type
     * @param {Object} [overrides] - Property overrides
     * @returns {Object|null}
     */
    createInstance(type, overrides = {}) {
        const block = this.blocks.get(type);
        if (!block) {
            console.error(`[BlockRegistry] Block type not found: ${type}`);
            return null;
        }

        return {
            id: this._generateId(),
            type: block.type,
            props: {
                ...JSON.parse(JSON.stringify(block.defaultProps)),
                ...overrides,
            },
        };
    }

    /**
     * Validate a block's props
     * @param {string} type
     * @param {Object} props
     * @returns {boolean}
     */
    validate(type, props) {
        const block = this.blocks.get(type);
        if (!block) return false;

        try {
            return block.validate(props);
        } catch (error) {
            console.error(`[BlockRegistry] Validation error for ${type}:`, error);
            return false;
        }
    }

    /**
     * Get default props for a block type
     * @param {string} type
     * @returns {Object}
     */
    getDefaultProps(type) {
        const block = this.blocks.get(type);
        return block ? { ...block.defaultProps } : {};
    }

    /**
     * Check if a block supports a feature
     * @param {string} type
     * @param {string} feature
     * @returns {boolean}
     */
    supports(type, feature) {
        const block = this.blocks.get(type);
        return block?.supports?.[feature] ?? false;
    }

    /**
     * Get the HTML generator for a block and context
     * @param {string} type
     * @param {string} context
     * @returns {Function|null}
     */
    getHtmlGenerator(type, context) {
        const block = this.blocks.get(type);
        if (!block) return null;

        // Check for context-specific generator (using new 'save' name)
        if (block.save[context]) {
            return block.save[context];
        }

        // Check for wildcard generator
        if (block.save['*']) {
            return block.save['*'];
        }

        return null;
    }

    /**
     * Generate a unique ID
     * @private
     */
    _generateId() {
        return `block-${Date.now()}-${Math.random().toString(36).substring(2, 11)}`;
    }

    /**
     * Reset the registry (useful for testing)
     */
    reset() {
        this.blocks.clear();
        this.categories.clear();
        this.components.clear();
        this.initialized = false;
    }

    /**
     * Export registry state (for debugging)
     */
    toJSON() {
        return {
            blocks: Array.from(this.blocks.entries()),
            categories: Array.from(this.categories.entries()),
        };
    }
}

// Create singleton instance
const blockRegistry = new BlockRegistryClass();

// Expose globally for module access
if (typeof window !== 'undefined') {
    window.LaraBuilderBlockRegistry = blockRegistry;
}

export { blockRegistry, BlockRegistryClass };
export default blockRegistry;
