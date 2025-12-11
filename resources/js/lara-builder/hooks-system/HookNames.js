/**
 * Hook Names Constants
 *
 * Centralized constants for all LaraBuilder hook names.
 * Use these constants instead of string literals for type safety and autocompletion.
 *
 * @example
 * import { BuilderHooks } from '@/lara-builder/hooks-system/HookNames';
 * LaraHooks.addFilter(BuilderHooks.FILTER_BLOCKS, callback);
 */

export const BuilderHooks = {
    // ========================================
    // FILTER HOOKS - Modify values
    // ========================================

    // Configuration
    FILTER_CONFIG: 'builder.config',
    FILTER_CONFIG_EMAIL: 'builder.config.email',
    FILTER_CONFIG_PAGE: 'builder.config.page',
    FILTER_CONFIG_CAMPAIGN: 'builder.config.campaign',

    // Blocks
    FILTER_BLOCKS: 'builder.blocks',
    FILTER_BLOCKS_EMAIL: 'builder.blocks.email',
    FILTER_BLOCKS_PAGE: 'builder.blocks.page',
    FILTER_BLOCKS_CAMPAIGN: 'builder.blocks.campaign',
    FILTER_BLOCK_CATEGORIES: 'builder.blocks.categories',

    // Block rendering
    FILTER_BLOCK_PROPS: 'builder.block.props',
    FILTER_BLOCK_RENDER: 'builder.block.render',
    FILTER_BLOCK_COMPONENT: 'builder.block.component',

    // HTML generation
    FILTER_HTML_GENERATED: 'builder.html.generated',
    FILTER_HTML_BLOCK: 'builder.html.block',
    FILTER_HTML_WRAPPER: 'builder.html.wrapper',

    // Properties panel
    FILTER_PROPERTY_FIELDS: 'builder.properties.fields',
    FILTER_PROPERTY_FIELD: 'builder.properties.field',

    // Canvas
    FILTER_CANVAS_SETTINGS: 'builder.canvas.settings',
    FILTER_CANVAS_DEFAULT_SETTINGS: 'builder.canvas.defaultSettings',

    // UI Components
    FILTER_HEADER: 'builder.ui.header',
    FILTER_LEFT_PANEL: 'builder.ui.panel.left',
    FILTER_RIGHT_PANEL: 'builder.ui.panel.right',
    FILTER_TOOLBAR: 'builder.ui.toolbar',
    FILTER_BLOCK_TOOLBAR: 'builder.ui.blockToolbar',
    FILTER_MORE_TEXT_CONTROLS: 'builder.ui.moreTextControls',

    // State
    FILTER_INITIAL_STATE: 'builder.state.initial',
    FILTER_STATE_BEFORE_SAVE: 'builder.state.beforeSave',
    FILTER_STATE_AFTER_LOAD: 'builder.state.afterLoad',

    // Data
    FILTER_SAVE_DATA: 'builder.data.save',
    FILTER_LOAD_DATA: 'builder.data.load',

    // ========================================
    // ACTION HOOKS - Side effects
    // ========================================

    // Builder lifecycle
    ACTION_INIT: 'builder.init',
    ACTION_READY: 'builder.ready',
    ACTION_DESTROY: 'builder.destroy',

    // Save operations
    ACTION_BEFORE_SAVE: 'builder.save.before',
    ACTION_AFTER_SAVE: 'builder.save.after',
    ACTION_SAVE_ERROR: 'builder.save.error',

    // Block lifecycle
    ACTION_BLOCK_REGISTERED: 'builder.block.registered',
    ACTION_BLOCK_UNREGISTERED: 'builder.block.unregistered',
    ACTION_BLOCK_ADDED: 'builder.block.added',
    ACTION_BLOCK_REMOVED: 'builder.block.removed',
    ACTION_BLOCK_UPDATED: 'builder.block.updated',
    ACTION_BLOCK_MOVED: 'builder.block.moved',
    ACTION_BLOCK_DUPLICATED: 'builder.block.duplicated',

    // Selection
    ACTION_BLOCK_SELECTED: 'builder.block.selected',
    ACTION_BLOCK_DESELECTED: 'builder.block.deselected',

    // History
    ACTION_UNDO: 'builder.history.undo',
    ACTION_REDO: 'builder.history.redo',
    ACTION_HISTORY_CHANGED: 'builder.history.changed',

    // Canvas
    ACTION_CANVAS_SETTINGS_CHANGED: 'builder.canvas.settingsChanged',

    // HTML generation
    ACTION_HTML_BEFORE_GENERATE: 'builder.html.beforeGenerate',
    ACTION_HTML_AFTER_GENERATE: 'builder.html.afterGenerate',

    // Drag and drop
    ACTION_DRAG_START: 'builder.dnd.dragStart',
    ACTION_DRAG_END: 'builder.dnd.dragEnd',
    ACTION_DROP: 'builder.dnd.drop',

    // Media
    ACTION_IMAGE_UPLOADED: 'builder.media.imageUploaded',
    ACTION_VIDEO_UPLOADED: 'builder.media.videoUploaded',

    // Keyboard
    ACTION_KEYBOARD_SHORTCUT: 'builder.keyboard.shortcut',
};

/**
 * Helper to create context-specific hook names
 * @param {string} baseHook - Base hook name
 * @param {string} context - Context (email, page, campaign)
 * @returns {string}
 */
export function getContextHook(baseHook, context) {
    return `${baseHook}.${context}`;
}

/**
 * Helper to create block-specific hook names
 * @param {string} baseHook - Base hook name
 * @param {string} blockType - Block type
 * @returns {string}
 */
export function getBlockHook(baseHook, blockType) {
    return `${baseHook}.${blockType}`;
}

export default BuilderHooks;
