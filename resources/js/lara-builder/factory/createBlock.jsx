/**
 * Block Factory - createBlock
 *
 * Eliminates boilerplate by creating complete block definitions
 * from minimal configuration.
 *
 * @example Simple block (just canvas component):
 * ```js
 * export default createBlock({
 *     type: 'spacer',
 *     label: 'Spacer',
 *     icon: 'mdi:arrow-expand-vertical',
 *     category: 'Layout',
 *     defaultProps: { height: '40px' },
 *     block: ({ props, isSelected }) => (
 *         <div style={{ height: props.height }}>Spacer</div>
 *     ),
 * });
 * ```
 *
 * @example Full block with all parts:
 * ```js
 * export default createBlock({
 *     ...config,           // from block.json
 *     block: CanvasComp,   // Canvas component
 *     editor: EditorComp,  // Properties panel (optional - auto-generated if not provided)
 *     save: { page, email }, // HTML generators
 * });
 * ```
 */

// Default layout styles - every block gets these
const DEFAULT_LAYOUT_STYLES = {
    margin: { top: '', right: '', bottom: '', left: '' },
    padding: { top: '', right: '', bottom: '', left: '' },
    width: '',
    minWidth: '',
    maxWidth: '',
    height: '',
    minHeight: '',
    maxHeight: '',
};

// Default supports - features the block supports
const DEFAULT_SUPPORTS = {
    align: true,
    spacing: true,
    colors: true,
    nesting: false,
    html: true,
    duplicate: true,
    remove: true,
    layout: true,
};

/**
 * Create a complete block definition from minimal configuration
 *
 * @param {Object} config - Block configuration
 * @param {string} config.type - Unique block identifier (required)
 * @param {string} config.label - Display name (required)
 * @param {string} [config.category='Content'] - Category for grouping
 * @param {string} [config.icon='lucide:box'] - Iconify icon name
 * @param {string} [config.description=''] - Block description
 * @param {string[]} [config.keywords=[]] - Search keywords
 * @param {string[]} [config.contexts=['*']] - Allowed contexts (email, page, campaign, *)
 * @param {Object} [config.defaultProps={}] - Default property values
 * @param {Object} [config.supports={}] - Feature support flags
 * @param {Array} [config.fields=[]] - Field definitions for auto-generated editor
 * @param {React.ComponentType} config.block - React component for builder canvas (required)
 * @param {React.ComponentType} [config.editor] - Custom editor component (auto-generated if not provided)
 * @param {Object} [config.save={}] - HTML generators { page, email }
 * @returns {Object} Complete block definition
 */
export function createBlock(config) {
    if (!config.type) {
        throw new Error('[createBlock] Block type is required');
    }
    if (!config.label) {
        throw new Error(`[createBlock] Block label is required for type: ${config.type}`);
    }
    if (!config.block) {
        throw new Error(`[createBlock] Block component is required for type: ${config.type}`);
    }

    // Merge supports with defaults
    const supports = {
        ...DEFAULT_SUPPORTS,
        ...config.supports,
    };

    // Merge defaultProps with layout styles
    const defaultProps = {
        ...config.defaultProps,
        layoutStyles: {
            ...DEFAULT_LAYOUT_STYLES,
            ...config.defaultProps?.layoutStyles,
        },
    };

    return {
        // Metadata
        type: config.type,
        label: config.label,
        category: config.category || 'Content',
        icon: config.icon || 'lucide:box',
        description: config.description || '',
        keywords: config.keywords || [],
        contexts: config.contexts || ['*'],
        supports,

        // Fields for auto-generated editor (translatable at runtime)
        fields: config.fields || [],

        // Props
        defaultProps,

        // Components
        block: config.block,
        editor: config.editor || null, // Will use auto-generated if null
        save: config.save || {},

        // Validation (optional)
        validate: config.validate || (() => true),

        // Transform from other blocks (optional)
        transform: config.transform || null,
    };
}

/**
 * Create a block from a JSON config file
 * This is the recommended way for blocks with block.json
 *
 * @param {Object} jsonConfig - Imported block.json
 * @param {Object} components - Block components { block, editor?, save? }
 * @returns {Object} Complete block definition
 */
export function createBlockFromJson(jsonConfig, components = {}) {
    return createBlock({
        ...jsonConfig,
        ...components,
    });
}

/**
 * Create a simple static block with minimal code
 * For blocks that don't need inline editing or complex logic
 *
 * @param {Object} config - Block configuration
 * @param {Function} config.render - Render function (props) => JSX
 * @returns {Object} Complete block definition
 */
export function createSimpleBlock(config) {
    const { render, ...rest } = config;

    if (!render) {
        throw new Error(`[createSimpleBlock] Render function is required for type: ${config.type}`);
    }

    // Wrap render in a proper React component
    const BlockComponent = ({ props, isSelected }) => {
        const baseStyle = {
            outline: isSelected ? '2px solid var(--color-primary, #635bff)' : 'none',
            borderRadius: '4px',
        };

        return (
            <div style={baseStyle}>
                {render(props)}
            </div>
        );
    };

    return createBlock({
        ...rest,
        block: BlockComponent,
    });
}

export default createBlock;
