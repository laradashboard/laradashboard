<?php

declare(strict_types=1);

namespace App\Enums\Hooks;

/**
 * Setting Action Hooks
 *
 * Provides action hooks for settings events that modules can hook into.
 * Action hooks are fire-and-forget - they notify listeners but don't expect return values.
 *
 * @example
 * // Clear cache when settings are updated
 * Hook::addAction(SettingActionHook::SETTINGS_SAVED_AFTER, function ($settings) {
 *     Cache::tags(['settings'])->flush();
 * });
 */
enum SettingActionHook: string
{
    // ==========================================================================
    // Settings Save Events
    // ==========================================================================

    /**
     * Fired before settings are saved.
     *
     * @param array $settings The settings being saved
     */
    case SETTINGS_SAVING_BEFORE = 'action.settings.saving_before';

    /**
     * Fired after settings are saved.
     *
     * @param array $settings The saved settings
     */
    case SETTINGS_SAVED_AFTER = 'action.settings.saved_after';

    // ==========================================================================
    // Individual Setting Events
    // ==========================================================================

    /**
     * Fired before a setting is created.
     *
     * @param string $key The setting key
     * @param mixed $value The setting value
     */
    case SETTING_CREATED_BEFORE = 'action.setting.created_before';

    /**
     * Fired after a setting is created.
     *
     * @param \App\Models\Setting $setting The created setting
     */
    case SETTING_CREATED_AFTER = 'action.setting.created_after';

    /**
     * Fired before a setting is updated.
     *
     * @param \App\Models\Setting $setting The setting being updated
     * @param mixed $newValue The new value
     */
    case SETTING_UPDATED_BEFORE = 'action.setting.updated_before';

    /**
     * Fired after a setting is updated.
     *
     * @param \App\Models\Setting $setting The updated setting
     * @param mixed $oldValue The previous value
     */
    case SETTING_UPDATED_AFTER = 'action.setting.updated_after';

    /**
     * Fired before a setting is deleted.
     *
     * @param \App\Models\Setting $setting The setting being deleted
     */
    case SETTING_DELETED_BEFORE = 'action.setting.deleted_before';

    /**
     * Fired after a setting is deleted.
     *
     * @param string $key The deleted setting key
     */
    case SETTING_DELETED_AFTER = 'action.setting.deleted_after';

    // ==========================================================================
    // Settings Import/Export Events
    // ==========================================================================

    /**
     * Fired before settings are imported.
     *
     * @param array $settings The settings being imported
     */
    case SETTINGS_IMPORTING_BEFORE = 'action.settings.importing_before';

    /**
     * Fired after settings are imported.
     *
     * @param array $settings The imported settings
     */
    case SETTINGS_IMPORTED_AFTER = 'action.settings.imported_after';

    /**
     * Fired before settings are exported.
     */
    case SETTINGS_EXPORTING_BEFORE = 'action.settings.exporting_before';

    /**
     * Fired after settings are exported.
     *
     * @param array $settings The exported settings
     */
    case SETTINGS_EXPORTED_AFTER = 'action.settings.exported_after';

    // ==========================================================================
    // Cache Events
    // ==========================================================================

    /**
     * Fired when settings cache is cleared.
     */
    case SETTINGS_CACHE_CLEARED = 'action.settings.cache_cleared';

    /**
     * Fired when settings cache is refreshed.
     */
    case SETTINGS_CACHE_REFRESHED = 'action.settings.cache_refreshed';
}
