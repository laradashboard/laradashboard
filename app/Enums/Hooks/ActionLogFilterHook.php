<?php

declare(strict_types=1);

namespace App\Enums\Hooks;

/**
 * Action Log Filter Hooks
 *
 * Provides filter hooks for action logging/audit trail that modules can use.
 * Use these hooks to customize what gets logged and how.
 *
 * @example
 * // Exclude certain fields from being logged
 * Hook::addFilter(ActionLogFilterHook::ACTION_LOG_EXCLUDED_FIELDS, function ($fields) {
 *     $fields[] = 'api_secret';
 *     return $fields;
 * });
 */
enum ActionLogFilterHook: string
{
    // ==========================================================================
    // Logging Filters
    // ==========================================================================

    /**
     * Filter whether an action should be logged.
     * Return false to prevent logging.
     *
     * @param bool $shouldLog Whether to log
     * @param string $action The action being performed
     * @param mixed $model The model involved
     * @return bool Whether to log
     */
    case ACTION_LOG_SHOULD_LOG = 'filter.action_log.should_log';

    /**
     * Filter the action log data before saving.
     *
     * @param array $data The log data
     * @return array Modified log data
     */
    case ACTION_LOG_DATA = 'filter.action_log.data';

    /**
     * Filter excluded/sensitive fields that should not be logged.
     *
     * @param array $fields The excluded field names
     * @return array Modified field names
     */
    case ACTION_LOG_EXCLUDED_FIELDS = 'filter.action_log.excluded_fields';

    /**
     * Filter fields that should be masked in logs (e.g., passwords).
     *
     * @param array $fields The masked field names
     * @return array Modified field names
     */
    case ACTION_LOG_MASKED_FIELDS = 'filter.action_log.masked_fields';

    /**
     * Filter models that should be excluded from logging.
     *
     * @param array $models The excluded model classes
     * @return array Modified model classes
     */
    case ACTION_LOG_EXCLUDED_MODELS = 'filter.action_log.excluded_models';

    /**
     * Filter actions that should be excluded from logging.
     *
     * @param array $actions The excluded actions
     * @return array Modified actions
     */
    case ACTION_LOG_EXCLUDED_ACTIONS = 'filter.action_log.excluded_actions';

    // ==========================================================================
    // Retention Filters
    // ==========================================================================

    /**
     * Filter the log retention period (in days).
     *
     * @param int $days The retention period
     * @return int Modified retention period
     */
    case ACTION_LOG_RETENTION_DAYS = 'filter.action_log.retention_days';

    /**
     * Filter logs before cleanup/deletion.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query The query
     * @return \Illuminate\Database\Eloquent\Builder Modified query
     */
    case ACTION_LOG_CLEANUP_QUERY = 'filter.action_log.cleanup_query';

    // ==========================================================================
    // Display Filters
    // ==========================================================================

    /**
     * Filter the formatted log entry for display.
     *
     * @param string $formatted The formatted entry
     * @param mixed $log The log entry
     * @return string Modified formatted entry
     */
    case ACTION_LOG_FORMATTED_ENTRY = 'filter.action_log.formatted_entry';

    /**
     * Filter available action log types/categories for filtering.
     *
     * @param array $types The available types
     * @return array Modified types
     */
    case ACTION_LOG_TYPES = 'filter.action_log.types';

    // ==========================================================================
    // UI Hooks
    // ==========================================================================

    /**
     * Hook after the action log page breadcrumbs.
     */
    case ACTION_LOG_AFTER_BREADCRUMBS = 'filter.action_log.after_breadcrumbs';

    /**
     * Hook after the action log table.
     */
    case ACTION_LOG_AFTER_TABLE = 'filter.action_log.after_table';

    /**
     * Hook for custom columns in the action log table.
     */
    case ACTION_LOG_TABLE_COLUMNS = 'filter.action_log.table.columns';

    /**
     * Hook for custom filters in the action log view.
     */
    case ACTION_LOG_TABLE_FILTERS = 'filter.action_log.table.filters';
}
