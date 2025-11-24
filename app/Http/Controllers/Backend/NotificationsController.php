<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\NotificationService;
use App\Services\Emails\EmailTemplateService;
use App\Http\Requests\NotificationRequest;
use App\Models\Notification;
use App\Models\Setting;
use App\Services\Emails\EmailVariable;
use App\Services\NotificationTypeRegistry;
use App\Services\ReceiverTypeRegistry;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationsController extends Controller
{
    public function __construct(
        private readonly NotificationService $notificationService,
        private readonly EmailTemplateService $emailTemplateService,
        private readonly EmailVariable $emailVariable,
    ) {
    }

    public function index(): Renderable
    {
        $this->authorize('manage', Setting::class);

        $this->setBreadcrumbTitle(__('Notifications'))
            ->addBreadcrumbItem(__('Settings'), route('admin.settings.index'));

        return $this->renderViewWithBreadcrumbs('backend.pages.notifications.index');
    }

    public function create(): Renderable
    {
        $this->authorize('manage', Setting::class);

        $this->setBreadcrumbTitle(__('Create Notification'))
            ->addBreadcrumbItem(__('Settings'), route('admin.settings.index'))
            ->addBreadcrumbItem(__('Notifications'), route('admin.notifications.index'));

        $notificationTypes = collect(NotificationTypeRegistry::all())
            ->mapWithKeys(function ($type) {
                $label = NotificationTypeRegistry::getLabel($type) ?: (\App\Enums\NotificationType::tryFrom($type)?->label() ?? ucfirst(str_replace('_', ' ', $type)));
                return [$type => $label];
            })
            ->toArray();

        $receiverTypes = collect(ReceiverTypeRegistry::all())
            ->mapWithKeys(function ($type) {
                $label = ReceiverTypeRegistry::getLabel($type) ?: (\App\Enums\ReceiverType::tryFrom($type)?->label() ?? ucfirst(str_replace('_', ' ', $type)));
                return [$type => $label];
            })
            ->toArray();

        return $this->renderViewWithBreadcrumbs('backend.pages.notifications.create', [
            'notificationTypes' => $notificationTypes,
            'receiverTypes' => $receiverTypes,
            'emailTemplates' => array_merge(
                ['' => __('None - Use Custom Content')],
                $this->emailTemplateService->getEmailTemplatesDropdown()
            ),
        ]);
    }

    public function store(NotificationRequest $request): RedirectResponse
    {
        $this->authorize('manage', Setting::class);

        try {
            $notification = $this->notificationService->createNotification($request->validated());

            return redirect()
                ->route('admin.notifications.show', $notification->id)
                ->with('success', __('Notification created successfully.'));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', __('Failed to create notification: :error', ['error' => $e->getMessage()]));
        }
    }

    public function show(Notification $notification): Renderable
    {
        $this->authorize('manage', Setting::class);

        $this->setBreadcrumbTitle(__('View Notification'))
            ->addBreadcrumbItem(__('Settings'), route('admin.settings.index'))
            ->addBreadcrumbItem(__('Notifications'), route('admin.notifications.index'));

        $notification->body_html = $this->emailVariable->replaceVariables(
            $notification->body_html ?? $notification->emailTemplate->body_html ?? '',
            $this->emailVariable->getPreviewSampleData()
        );

        return $this->renderViewWithBreadcrumbs('backend.pages.notifications.show', compact('notification'));
    }

    public function edit(Notification $notification): Renderable
    {
        $this->authorize('manage', Setting::class);

        $receiverTypes = collect(\App\Services\ReceiverTypeRegistry::all())
            ->mapWithKeys(function ($type) {
                $label = \App\Services\ReceiverTypeRegistry::getLabel($type) ?: (\App\Enums\ReceiverType::tryFrom($type)?->label() ?? ucfirst(str_replace('_', ' ', $type)));
                return [$type => $label];
            })
            ->toArray();

        $this->setBreadcrumbTitle(__('Edit Notification'))
            ->addBreadcrumbItem(__('Settings'), route('admin.settings.index'))
            ->addBreadcrumbItem(__('Notifications'), route('admin.notifications.index'));

        $notificationTypes = collect(NotificationTypeRegistry::all())
            ->mapWithKeys(function ($type) {
                $label = NotificationTypeRegistry::getLabel($type) ?: (\App\Enums\NotificationType::tryFrom($type)?->label() ?? ucfirst(str_replace('_', ' ', $type)));
                return [$type => $label];
            })
            ->toArray();

        return $this->renderViewWithBreadcrumbs('backend.pages.notifications.edit', [
            'notification' => $notification,
            'notificationTypes' => $notificationTypes,
            'receiverTypes' => $receiverTypes,
            'emailTemplates' => array_merge([['label' => __('None - Use Custom Content'), 'value' => '']], $this->emailTemplateService->getEmailTemplatesDropdown()),
        ]);
    }

    public function update(NotificationRequest $request, int $notification): RedirectResponse
    {
        $this->authorize('manage', Setting::class);

        $notification = $this->notificationService->getNotificationById($notification);
        if (! $notification) {
            return redirect()
                ->back()
                ->with('error', __('Notification not found.'));
        }

        try {
            $this->notificationService->updateNotification($notification, $request->validated());

            return redirect()
                ->route('admin.notifications.show', $notification->id)
                ->with('success', __('Notification updated successfully.'));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', __('Failed to update notification: :error', ['error' => $e->getMessage()]));
        }
    }

    public function destroy(Notification $notification): RedirectResponse
    {
        $this->authorize('manage', Setting::class);

        try {
            $this->notificationService->deleteNotification($notification);

            return redirect()
                ->route('admin.notifications.index')
                ->with('success', __('Notification deleted successfully.'));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', __('Failed to delete notification: :error', ['error' => $e->getMessage()]));
        }
    }


}
