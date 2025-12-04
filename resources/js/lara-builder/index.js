/**
 * LaraBuilder - Extensible Visual Content Builder
 *
 * A reusable, hook-based builder for emails, pages, and custom content.
 *
 * @example
 * import LaraBuilder, { blockRegistry, LaraHooks } from '@/lara-builder';
 *
 * // Register custom blocks
 * blockRegistry.register({
 *   type: 'my-block',
 *   label: 'My Block',
 *   category: 'Custom',
 *   contexts: ['email', 'page'],
 *   component: MyBlockComponent,
 *   defaultProps: { text: 'Hello' },
 * });
 *
 * // Add hooks
 * LaraHooks.addFilter('builder.blocks.email', (blocks) => {
 *   return [...blocks, myBlock];
 * });
 *
 * // Use the builder
 * <LaraBuilder
 *   context="email"
 *   initialData={data}
 *   onSave={handleSave}
 * />
 */

// Core
export {
    LaraBuilder,
    LaraBuilderInner,
    BuilderProvider,
    useBuilder,
    useBuilderState,
    useBuilderActions,
    useSelectedBlock,
    useBuilderHistory,
    BuilderContext,
    builderReducer,
    initialState,
    ActionTypes,
    actions,
    useHistory,
    useBlocks,
    useSelection,
} from './core';

// Hooks System
export {
    LaraHooks,
    LaraHooksSystem,
    BuilderHooks,
    getContextHook,
    getBlockHook,
} from './hooks-system';

// Registry
export {
    blockRegistry,
    BlockRegistryClass,
    OutputAdapterRegistry,
    OutputAdapterRegistryClass,
} from './registry';

// Adapters
export {
    BaseAdapter,
    EmailAdapter,
    WebAdapter,
} from './adapters';

// Utilities (backward compatibility)
export {
    generateEmailHtml,
    generateBlockHtml,
} from './utils/generateHtml';

// Default export
export { default } from './core/LaraBuilder';
