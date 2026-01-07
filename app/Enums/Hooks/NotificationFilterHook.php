<?php

declare(strict_types=1);

namespace App\Enums\Hooks;

/**
 * Notification Filter Hooks
 *
 * Provides filter hooks for notifications that modules can use to modify behavior.
 * Filter hooks receive a value and must return it (modified or not).
 *
 * @example
 * // Add custom notification channels
 * Hook::addFilter(NotificationFilterHook::NOTIFICATION_CHANNELS, function ($channels) {
 *     $channels[] = 'slack';
 *     return $channels;
 * });
 */
enum NotificationFilterHook: string
{
    // ==========================================================================
    // Data Filters
    // ==========================================================================

    /**
     * Filter notification data before creation.
     *
     * @param array $data The notification data
     * @return array Modified data
     */
    case NOTIFICATION_CREATED_BEFORE = 'filter.notification.created_before';

    /**
     * Filter notification after creation.
     *
     * @param mixed $notification The notification
     * @return mixed Modified notification
     */
    case NOTIFICATION_CREATED_AFTER = 'filter.notification.created_after';

    /**
     * Filter notification data before update.
     *
     * @param array $data The notification data
     * @return array Modified data
     */
    case NOTIFICATION_UPDATED_BEFORE = 'filter.notification.updated_before';

    /**
     * Filter notification after update.
     *
     * @param mixed $notification The notification
     * @return mixed Modified notification
     */
    case NOTIFICATION_UPDATED_AFTER = 'filter.notification.updated_after';

    // ==========================================================================
    // Notification Types and Channels
    // ==========================================================================

    /**
     * Filter available notification types.
     * Use this to register custom notification types from modules.
     *
     * @param array $types The available types
     * @return array Modified types
     */
    case NOTIFICATION_TYPES = 'filter.notification.types';

    /**
     * Filter available notification channels.
     *
     * @param array $channels The available channels (mail, database, etc.)
     * @return array Modified channels
     */
    case NOTIFICATION_CHANNELS = 'filter.notification.channels';

    /**
     * Filter notification receivers for a specific notification type.
     *
     * @param array $receivers The receivers
     * @param string $type The notification type
     * @return array Modified receivers
     */
    case NOTIFICATION_RECEIVERS = 'filter.notification.receivers';

    // ==========================================================================
    // Notification Content
    // ==========================================================================

    /**
     * Filter the notification content/message.
     *
     * @param string $content The content
     * @param mixed $notification The notification
     * @return string Modified content
     */
    case NOTIFICATION_CONTENT = 'filter.notification.content';

    /**
     * Filter the notification title.
     *
     * @param string $title The title
     * @param mixed $notification The notification
     * @return string Modified title
     */
    case NOTIFICATION_TITLE = 'filter.notification.title';

    /**
     * Filter the notification URL/link.
     *
     * @param string $url The URL
     * @param mixed $notification The notification
     * @return string Modified URL
     */
    case NOTIFICATION_URL = 'filter.notification.url';

    /**
     * Filter the notification icon.
     *
     * @param string $icon The icon
     * @param mixed $notification The notification
     * @return string Modified icon
     */
    case NOTIFICATION_ICON = 'filter.notification.icon';

    // ==========================================================================
    // Validation Hooks
    // ==========================================================================

    /**
     * Filter validation rules for storing notifications.
     *
     * @param array $rules The validation rules
     * @return array Modified rules
     */
    case NOTIFICATION_STORE_VALIDATION_RULES = 'filter.notification.store.validation.rules';

    // ==========================================================================
    // UI Hooks
    // ==========================================================================

    /**
     * Hook after the notifications dropdown header.
     */
    case NOTIFICATIONS_DROPDOWN_HEADER = 'filter.notifications.dropdown.header';

    /**
     * Hook after the notifications dropdown items.
     */
    case NOTIFICATIONS_DROPDOWN_ITEMS = 'filter.notifications.dropdown.items';

    /**
     * Hook for the notifications dropdown footer.
     */
    case NOTIFICATIONS_DROPDOWN_FOOTER = 'filter.notifications.dropdown.footer';

    /**
     * Hook after the notifications page breadcrumbs.
     */
    case NOTIFICATIONS_AFTER_BREADCRUMBS = 'filter.notifications.after_breadcrumbs';

    /**
     * Hook after the notifications table.
     */
    case NOTIFICATIONS_AFTER_TABLE = 'filter.notifications.after_table';

    /**
     * Hook before the notifications table.
     */
    case NOTIFICATIONS_BEFORE_TABLE = 'filter.notifications.before_table';

    /**
     * Hook after the notification show page breadcrumbs.
     */
    case NOTIFICATION_SHOW_AFTER_BREADCRUMBS = 'filter.notification.show.after_breadcrumbs';

    /**
     * Hook after the notification show page content.
     */
    case NOTIFICATION_SHOW_AFTER_CONTENT = 'filter.notification.show.after_content';
}
