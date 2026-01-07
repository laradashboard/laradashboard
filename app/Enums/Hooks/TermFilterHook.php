<?php

declare(strict_types=1);

namespace App\Enums\Hooks;

/**
 * Term (Category/Tag) Filter Hooks
 *
 * Provides filter hooks for taxonomy terms that modules can use to modify behavior.
 * Filter hooks receive a value and must return it (modified or not).
 *
 * @example
 * // Add custom field after term name
 * Hook::addFilter(TermFilterHook::TERM_FORM_AFTER_NAME, function ($content, $term) {
 *     return $content . '<div class="my-custom-field">...</div>';
 * });
 */
enum TermFilterHook: string
{
    // ==========================================================================
    // Data Filters
    // ==========================================================================

    /**
     * Filter term data before creation.
     *
     * @param array $data The term data
     * @return array Modified data
     */
    case TERM_CREATED_BEFORE = 'filter.term.created_before';

    /**
     * Filter term after creation.
     *
     * @param mixed $term The term
     * @return mixed Modified term
     */
    case TERM_CREATED_AFTER = 'filter.term.created_after';

    /**
     * Filter term data before update.
     *
     * @param array $data The term data
     * @param mixed $term The term being updated
     * @return array Modified data
     */
    case TERM_UPDATED_BEFORE = 'filter.term.updated_before';

    /**
     * Filter term after update.
     *
     * @param mixed $term The updated term
     * @return mixed Modified term
     */
    case TERM_UPDATED_AFTER = 'filter.term.updated_after';

    /**
     * Filter term before deletion.
     *
     * @param mixed $term The term to delete
     * @return mixed|false Return false to prevent deletion
     */
    case TERM_DELETED_BEFORE = 'filter.term.deleted_before';

    /**
     * Filter after term deletion.
     *
     * @param int $termId The deleted term ID
     * @return int
     */
    case TERM_DELETED_AFTER = 'filter.term.deleted_after';

    case TERM_BULK_DELETED_BEFORE = 'filter.term.bulk_deleted_before';
    case TERM_BULK_DELETED_AFTER = 'filter.term.bulk_deleted_after';

    // ==========================================================================
    // Validation Hooks
    // ==========================================================================

    /**
     * Filter validation rules for storing terms.
     *
     * @param array $rules The validation rules
     * @return array Modified rules
     */
    case TERM_STORE_VALIDATION_RULES = 'filter.term.store.validation.rules';

    /**
     * Filter validation rules for updating terms.
     *
     * @param array $rules The validation rules
     * @return array Modified rules
     */
    case TERM_UPDATE_VALIDATION_RULES = 'filter.term.update.validation.rules';

    // ==========================================================================
    // UI Hooks - Index Page
    // ==========================================================================

    /**
     * Hook after the terms page breadcrumbs.
     */
    case TERM_AFTER_BREADCRUMBS = 'filter.term.after_breadcrumbs';

    /**
     * Hook before the terms table.
     */
    case TERM_BEFORE_TABLE = 'filter.term.before_table';

    /**
     * Hook after the terms table.
     */
    case TERM_AFTER_TABLE = 'filter.term.after_table';

    // ==========================================================================
    // UI Hooks - Form
    // ==========================================================================

    /**
     * Hook at the start of the term form.
     */
    case TERM_FORM_START = 'filter.term.form.start';

    /**
     * Hook after the term name field.
     */
    case TERM_FORM_AFTER_NAME = 'filter.term.form.after_name';

    /**
     * Hook after the term slug field.
     */
    case TERM_FORM_AFTER_SLUG = 'filter.term.form.after_slug';

    /**
     * Hook after the term description field.
     */
    case TERM_FORM_AFTER_DESCRIPTION = 'filter.term.form.after_description';

    /**
     * Hook after the term additional settings.
     */
    case TERM_FORM_AFTER_ADDITIONAL_SETTINGS = 'filter.term.form.after_additional_settings';

    /**
     * Hook at the end of the term form (before submit buttons).
     */
    case TERM_FORM_END = 'filter.term.form.end';

    // ==========================================================================
    // UI Hooks - Show Page
    // ==========================================================================

    /**
     * Hook after the term show page breadcrumbs.
     */
    case TERM_SHOW_AFTER_BREADCRUMBS = 'filter.term.show.after_breadcrumbs';

    /**
     * Hook after the term show page main content.
     */
    case TERM_SHOW_AFTER_MAIN_CONTENT = 'filter.term.show.after_main_content';

    /**
     * Hook after the term show page sidebar.
     */
    case TERM_SHOW_AFTER_SIDEBAR = 'filter.term.show.after_sidebar';

    /**
     * Hook at the end of term show page.
     */
    case TERM_SHOW_AFTER_CONTENT = 'filter.term.show.after_content';
}
