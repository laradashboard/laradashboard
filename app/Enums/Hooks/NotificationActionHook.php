<?php

declare(strict_types=1);

namespace App\Enums\Hooks;

/**
 * Notification Action Hooks
 *
 * Provides action hooks for notification events that modules can hook into.
 * Action hooks are fire-and-forget - they notify listeners but don't expect return values.
 *
 * @example
 * // Listen to notification sent events
 * Hook::addAction(NotificationActionHook::NOTIFICATION_SENT_AFTER, function ($notification, $recipient) {
 *     // Track notification delivery
 * });
 */
enum NotificationActionHook: string
{
    // ==========================================================================
    // Notification CRUD Events
    // ==========================================================================

    /**
     * Fired before a notification is created.
     *
     * @param array $data The notification data
     */
    case NOTIFICATION_CREATED_BEFORE = 'action.notification.created_before';

    /**
     * Fired after a notification is created.
     *
     * @param mixed $notification The created notification
     */
    case NOTIFICATION_CREATED_AFTER = 'action.notification.created_after';

    /**
     * Fired before a notification is updated.
     *
     * @param mixed $notification The notification being updated
     * @param array $data The new data
     */
    case NOTIFICATION_UPDATED_BEFORE = 'action.notification.updated_before';

    /**
     * Fired after a notification is updated.
     *
     * @param mixed $notification The updated notification
     */
    case NOTIFICATION_UPDATED_AFTER = 'action.notification.updated_after';

    /**
     * Fired before a notification is deleted.
     *
     * @param mixed $notification The notification being deleted
     */
    case NOTIFICATION_DELETED_BEFORE = 'action.notification.deleted_before';

    /**
     * Fired after a notification is deleted.
     *
     * @param string $notificationId The ID of the deleted notification
     */
    case NOTIFICATION_DELETED_AFTER = 'action.notification.deleted_after';

    // ==========================================================================
    // Notification Sending Events
    // ==========================================================================

    /**
     * Fired before a notification is sent.
     *
     * @param mixed $notification The notification
     * @param mixed $notifiable The notifiable entity
     */
    case NOTIFICATION_SENDING_BEFORE = 'action.notification.sending_before';

    /**
     * Fired after a notification is successfully sent.
     *
     * @param mixed $notification The notification
     * @param mixed $notifiable The notifiable entity
     */
    case NOTIFICATION_SENT_AFTER = 'action.notification.sent_after';

    /**
     * Fired when a notification fails to send.
     *
     * @param mixed $notification The notification
     * @param mixed $notifiable The notifiable entity
     * @param \Throwable $exception The exception
     */
    case NOTIFICATION_SEND_FAILED = 'action.notification.send_failed';

    // ==========================================================================
    // Notification Read Events
    // ==========================================================================

    /**
     * Fired when a notification is marked as read.
     *
     * @param mixed $notification The notification
     */
    case NOTIFICATION_READ = 'action.notification.read';

    /**
     * Fired when all notifications are marked as read.
     *
     * @param mixed $notifiable The notifiable entity
     */
    case NOTIFICATIONS_ALL_READ = 'action.notification.all_read';

    // ==========================================================================
    // Bulk Operations
    // ==========================================================================

    /**
     * Fired before notifications are bulk deleted.
     *
     * @param array $notificationIds The IDs being deleted
     */
    case NOTIFICATION_BULK_DELETED_BEFORE = 'action.notification.bulk_deleted_before';

    /**
     * Fired after notifications are bulk deleted.
     *
     * @param array $notificationIds The deleted IDs
     */
    case NOTIFICATION_BULK_DELETED_AFTER = 'action.notification.bulk_deleted_after';
}
