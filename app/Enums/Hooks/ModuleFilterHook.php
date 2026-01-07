<?php

declare(strict_types=1);

namespace App\Enums\Hooks;

/**
 * Module Filter Hooks
 *
 * Provides filter hooks for module management that modules can use to modify behavior.
 * Filter hooks receive a value and must return it (modified or not).
 *
 * @example
 * // Add custom actions to module card
 * Hook::addFilter(ModuleFilterHook::MODULE_CARD_ACTIONS, function ($actions, $module) {
 *     $actions[] = '<a href="#">Custom Action</a>';
 *     return $actions;
 * });
 */
enum ModuleFilterHook: string
{
    // ==========================================================================
    // Data Filters
    // ==========================================================================

    /**
     * Filter module data before creation.
     *
     * @param array $data The module data
     * @return array Modified data
     */
    case MODULE_CREATED_BEFORE = 'filter.module.created_before';

    /**
     * Filter module after creation.
     *
     * @param mixed $module The module
     * @return mixed Modified module
     */
    case MODULE_CREATED_AFTER = 'filter.module.created_after';

    /**
     * Filter module data before update.
     *
     * @param array $data The module data
     * @return array Modified data
     */
    case MODULE_UPDATED_BEFORE = 'filter.module.updated_before';

    /**
     * Filter module after update.
     *
     * @param mixed $module The updated module
     * @return mixed Modified module
     */
    case MODULE_UPDATED_AFTER = 'filter.module.updated_after';

    /**
     * Filter module before deletion.
     *
     * @param mixed $module The module to delete
     * @return mixed|false Return false to prevent deletion
     */
    case MODULE_DELETED_BEFORE = 'filter.module.deleted_before';

    /**
     * Filter after module deletion.
     *
     * @param string $moduleName The deleted module name
     * @return string
     */
    case MODULE_DELETED_AFTER = 'filter.module.deleted_after';

    case MODULE_BULK_DELETED_BEFORE = 'filter.module.bulk_deleted_before';
    case MODULE_BULK_DELETED_AFTER = 'filter.module.bulk_deleted_after';

    // ==========================================================================
    // Module Status Filters
    // ==========================================================================

    /**
     * Filter before enabling a module.
     *
     * @param mixed $module The module
     * @return mixed|false Return false to prevent enabling
     */
    case MODULE_ENABLE_BEFORE = 'filter.module.enable_before';

    /**
     * Filter after enabling a module.
     *
     * @param mixed $module The enabled module
     * @return mixed
     */
    case MODULE_ENABLE_AFTER = 'filter.module.enable_after';

    /**
     * Filter before disabling a module.
     *
     * @param mixed $module The module
     * @return mixed|false Return false to prevent disabling
     */
    case MODULE_DISABLE_BEFORE = 'filter.module.disable_before';

    /**
     * Filter after disabling a module.
     *
     * @param mixed $module The disabled module
     * @return mixed
     */
    case MODULE_DISABLE_AFTER = 'filter.module.disable_after';

    // ==========================================================================
    // Validation Hooks
    // ==========================================================================

    /**
     * Filter validation rules for module upload.
     *
     * @param array $rules The validation rules
     * @return array Modified rules
     */
    case MODULE_STORE_VALIDATION_RULES = 'filter.module.store.validation.rules';

    /**
     * Filter validation messages for module upload.
     *
     * @param array $messages The validation messages
     * @return array Modified messages
     */
    case MODULE_STORE_VALIDATION_MESSAGES = 'filter.module.store.validation.messages';

    // ==========================================================================
    // UI Hooks - Index Page
    // ==========================================================================

    /**
     * Hook after the modules page breadcrumbs.
     */
    case MODULES_AFTER_BREADCRUMBS = 'filter.modules.after_breadcrumbs';

    /**
     * Hook before the modules list/grid.
     */
    case MODULES_BEFORE_LIST = 'filter.modules.before_list';

    /**
     * Hook after the modules list/grid.
     */
    case MODULES_AFTER_LIST = 'filter.modules.after_list';

    /**
     * Hook for module card actions.
     */
    case MODULE_CARD_ACTIONS = 'filter.module.card.actions';

    // ==========================================================================
    // UI Hooks - Show Page
    // ==========================================================================

    /**
     * Hook after the module show page breadcrumbs.
     */
    case MODULE_SHOW_AFTER_BREADCRUMBS = 'filter.module.show.after_breadcrumbs';

    /**
     * Hook after the module header.
     */
    case MODULE_SHOW_AFTER_HEADER = 'filter.module.show.after_header';

    /**
     * Hook after the module description.
     */
    case MODULE_SHOW_AFTER_DESCRIPTION = 'filter.module.show.after_description';

    /**
     * Hook after the module main content area.
     */
    case MODULE_SHOW_AFTER_MAIN_CONTENT = 'filter.module.show.after_main_content';

    /**
     * Hook at the end of the module show page.
     */
    case MODULE_SHOW_AFTER_CONTENT = 'filter.module.show.after_content';

    /**
     * Hook before the module sidebar.
     */
    case MODULE_SHOW_SIDEBAR_BEFORE = 'filter.module.show.sidebar_before';

    /**
     * Hook after the module sidebar.
     */
    case MODULE_SHOW_SIDEBAR_AFTER = 'filter.module.show.sidebar_after';

    // ==========================================================================
    // UI Hooks - Upload Form
    // ==========================================================================

    /**
     * Hook after the module upload form.
     */
    case MODULE_UPLOAD_FORM_AFTER = 'filter.module.upload_form.after';
}
