<?php

declare(strict_types=1);

namespace App\Enums\Hooks;

/**
 * Term/Taxonomy Action Hooks
 *
 * Provides action hooks for term/taxonomy events that modules can hook into.
 * Terms represent categories, tags, and other taxonomies in the content system.
 *
 * @example
 * // Update search index when term is created
 * Hook::addAction(TermActionHook::TERM_CREATED_AFTER, function ($term) {
 *     SearchIndex::indexTerm($term);
 * });
 */
enum TermActionHook: string
{
    // ==========================================================================
    // Term CRUD Events
    // ==========================================================================

    /**
     * Fired before a term is created.
     *
     * @param array $data The term data
     * @param string $taxonomy The taxonomy type
     */
    case TERM_CREATED_BEFORE = 'action.term.created_before';

    /**
     * Fired after a term is created.
     *
     * @param \App\Models\Term $term The created term
     */
    case TERM_CREATED_AFTER = 'action.term.created_after';

    /**
     * Fired before a term is updated.
     *
     * @param \App\Models\Term $term The term being updated
     * @param array $data The new data
     */
    case TERM_UPDATED_BEFORE = 'action.term.updated_before';

    /**
     * Fired after a term is updated.
     *
     * @param \App\Models\Term $term The updated term
     */
    case TERM_UPDATED_AFTER = 'action.term.updated_after';

    /**
     * Fired before a term is deleted.
     *
     * @param \App\Models\Term $term The term being deleted
     */
    case TERM_DELETED_BEFORE = 'action.term.deleted_before';

    /**
     * Fired after a term is deleted.
     *
     * @param int $termId The ID of the deleted term
     * @param string $taxonomy The taxonomy type
     */
    case TERM_DELETED_AFTER = 'action.term.deleted_after';

    // ==========================================================================
    // Bulk Operations
    // ==========================================================================

    /**
     * Fired before terms are bulk deleted.
     *
     * @param array $termIds The IDs being deleted
     * @param string $taxonomy The taxonomy type
     */
    case TERM_BULK_DELETED_BEFORE = 'action.term.bulk_deleted_before';

    /**
     * Fired after terms are bulk deleted.
     *
     * @param array $termIds The deleted IDs
     * @param string $taxonomy The taxonomy type
     */
    case TERM_BULK_DELETED_AFTER = 'action.term.bulk_deleted_after';

    // ==========================================================================
    // Taxonomy Events
    // ==========================================================================

    /**
     * Fired when a custom taxonomy is registered.
     *
     * @param string $taxonomy The taxonomy name
     * @param array $config The taxonomy configuration
     */
    case TAXONOMY_REGISTERED = 'action.term.taxonomy_registered';

    /**
     * Fired when a taxonomy is unregistered.
     *
     * @param string $taxonomy The taxonomy name
     */
    case TAXONOMY_UNREGISTERED = 'action.term.taxonomy_unregistered';

    // ==========================================================================
    // Term Relationship Events
    // ==========================================================================

    /**
     * Fired when a term is assigned to a post.
     *
     * @param \App\Models\Term $term The term
     * @param \App\Models\Post $post The post
     */
    case TERM_ASSIGNED_TO_POST = 'action.term.assigned_to_post';

    /**
     * Fired when a term is removed from a post.
     *
     * @param \App\Models\Term $term The term
     * @param \App\Models\Post $post The post
     */
    case TERM_REMOVED_FROM_POST = 'action.term.removed_from_post';

    /**
     * Fired when a term's parent is changed.
     *
     * @param \App\Models\Term $term The term
     * @param int|null $oldParentId The old parent ID
     * @param int|null $newParentId The new parent ID
     */
    case TERM_PARENT_CHANGED = 'action.term.parent_changed';

    // ==========================================================================
    // Term Media Events
    // ==========================================================================

    /**
     * Fired when a featured image is added to a term.
     *
     * @param \App\Models\Term $term The term
     * @param mixed $media The media
     */
    case TERM_FEATURED_IMAGE_ADDED = 'action.term.featured_image_added';

    /**
     * Fired when a featured image is removed from a term.
     *
     * @param \App\Models\Term $term The term
     */
    case TERM_FEATURED_IMAGE_REMOVED = 'action.term.featured_image_removed';
}
