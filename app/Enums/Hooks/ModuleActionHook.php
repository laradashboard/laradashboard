<?php

declare(strict_types=1);

namespace App\Enums\Hooks;

/**
 * Module Action Hooks
 *
 * Provides action hooks for module lifecycle events that modules can hook into.
 * These hooks allow modules to perform setup/cleanup during installation, activation, etc.
 *
 * @example
 * // Run migrations when module is enabled
 * Hook::addAction(ModuleActionHook::MODULE_ENABLED_AFTER, function ($moduleName) {
 *     Artisan::call('migrate', ['--path' => "modules/{$moduleName}/database/migrations"]);
 * });
 */
enum ModuleActionHook: string
{
    // ==========================================================================
    // Module Installation Events
    // ==========================================================================

    /**
     * Fired before a module is installed/uploaded.
     *
     * @param string $moduleName The module name
     * @param array $moduleData The module metadata
     */
    case MODULE_INSTALLING_BEFORE = 'action.module.installing_before';

    /**
     * Fired after a module is successfully installed.
     *
     * @param string $moduleName The module name
     * @param string $modulePath The module path
     */
    case MODULE_INSTALLED_AFTER = 'action.module.installed_after';

    /**
     * Fired when module installation fails.
     *
     * @param string $moduleName The module name
     * @param \Throwable $exception The exception
     */
    case MODULE_INSTALL_FAILED = 'action.module.install_failed';

    // ==========================================================================
    // Module Enable/Disable Events
    // ==========================================================================

    /**
     * Fired before a module is enabled.
     *
     * @param string $moduleName The module name
     */
    case MODULE_ENABLING_BEFORE = 'action.module.enabling_before';

    /**
     * Fired after a module is successfully enabled.
     *
     * @param string $moduleName The module name
     */
    case MODULE_ENABLED_AFTER = 'action.module.enabled_after';

    /**
     * Fired before a module is disabled.
     *
     * @param string $moduleName The module name
     */
    case MODULE_DISABLING_BEFORE = 'action.module.disabling_before';

    /**
     * Fired after a module is successfully disabled.
     *
     * @param string $moduleName The module name
     */
    case MODULE_DISABLED_AFTER = 'action.module.disabled_after';

    // ==========================================================================
    // Module Update Events
    // ==========================================================================

    /**
     * Fired before a module is updated/replaced.
     *
     * @param string $moduleName The module name
     * @param string $currentVersion The current version
     * @param string $newVersion The new version
     */
    case MODULE_UPDATING_BEFORE = 'action.module.updating_before';

    /**
     * Fired after a module is successfully updated.
     *
     * @param string $moduleName The module name
     * @param string $previousVersion The previous version
     * @param string $currentVersion The current version
     */
    case MODULE_UPDATED_AFTER = 'action.module.updated_after';

    // ==========================================================================
    // Module Deletion Events
    // ==========================================================================

    /**
     * Fired before a module is deleted.
     * Use this to clean up module data (database tables, files, etc.).
     *
     * @param string $moduleName The module name
     */
    case MODULE_DELETING_BEFORE = 'action.module.deleting_before';

    /**
     * Fired after a module is successfully deleted.
     *
     * @param string $moduleName The module name
     */
    case MODULE_DELETED_AFTER = 'action.module.deleted_after';

    // ==========================================================================
    // Module Migration Events
    // ==========================================================================

    /**
     * Fired before module migrations are run.
     *
     * @param string $moduleName The module name
     */
    case MODULE_MIGRATING_BEFORE = 'action.module.migrating_before';

    /**
     * Fired after module migrations are complete.
     *
     * @param string $moduleName The module name
     */
    case MODULE_MIGRATED_AFTER = 'action.module.migrated_after';

    // ==========================================================================
    // Module Asset Events
    // ==========================================================================

    /**
     * Fired before module assets are published.
     *
     * @param string $moduleName The module name
     */
    case MODULE_ASSETS_PUBLISHING_BEFORE = 'action.module.assets.publishing_before';

    /**
     * Fired after module assets are published.
     *
     * @param string $moduleName The module name
     */
    case MODULE_ASSETS_PUBLISHED_AFTER = 'action.module.assets.published_after';

    // ==========================================================================
    // Bulk Operations
    // ==========================================================================

    /**
     * Fired before modules are bulk activated.
     *
     * @param array $moduleNames The module names
     */
    case MODULES_BULK_ACTIVATING_BEFORE = 'action.module.bulk.activating_before';

    /**
     * Fired after modules are bulk activated.
     *
     * @param array $results The results per module
     */
    case MODULES_BULK_ACTIVATED_AFTER = 'action.module.bulk.activated_after';

    /**
     * Fired before modules are bulk deactivated.
     *
     * @param array $moduleNames The module names
     */
    case MODULES_BULK_DEACTIVATING_BEFORE = 'action.module.bulk.deactivating_before';

    /**
     * Fired after modules are bulk deactivated.
     *
     * @param array $results The results per module
     */
    case MODULES_BULK_DEACTIVATED_AFTER = 'action.module.bulk.deactivated_after';
}
