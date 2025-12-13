<?php

declare(strict_types=1);

namespace App\Enums\Builder;

/**
 * Builder Action Hooks
 *
 * These hooks trigger at various points during builder operations.
 * They allow extending builder functionality without modifying core code.
 */
enum BuilderActionHook: string
{
    // Lifecycle actions
    case BUILDER_INIT = 'action.builder.init';
    case BUILDER_READY = 'action.builder.ready';
    case BUILDER_DESTROY = 'action.builder.destroy';

    // Block actions
    case BUILDER_BLOCK_ADDED = 'action.builder.block.added';
    case BUILDER_BLOCK_UPDATED = 'action.builder.block.updated';
    case BUILDER_BLOCK_DELETED = 'action.builder.block.deleted';
    case BUILDER_BLOCK_MOVED = 'action.builder.block.moved';
    case BUILDER_BLOCK_DUPLICATED = 'action.builder.block.duplicated';
    case BUILDER_BLOCK_SELECTED = 'action.builder.block.selected';

    // Drag and drop actions
    case BUILDER_DRAG_START = 'action.builder.drag.start';
    case BUILDER_DRAG_END = 'action.builder.drag.end';
    case BUILDER_DROP = 'action.builder.drop';

    // HTML generation actions
    case BUILDER_HTML_BEFORE_GENERATE = 'action.builder.html.before_generate';
    case BUILDER_HTML_AFTER_GENERATE = 'action.builder.html.after_generate';

    // Save actions
    case BUILDER_BEFORE_SAVE = 'action.builder.before_save';
    case BUILDER_AFTER_SAVE = 'action.builder.after_save';
    case BUILDER_SAVE_ERROR = 'action.builder.save_error';

    // History actions
    case BUILDER_UNDO = 'action.builder.undo';
    case BUILDER_REDO = 'action.builder.redo';

    // Selection actions
    case BUILDER_SELECTION_CHANGED = 'action.builder.selection.changed';
    case BUILDER_SELECTION_CLEARED = 'action.builder.selection.cleared';

    // Canvas actions
    case BUILDER_CANVAS_UPDATED = 'action.builder.canvas.updated';
}
