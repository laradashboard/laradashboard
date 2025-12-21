<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use App\Services\NotificationService;
use App\Services\Emails\EmailTemplateService;
use App\Http\Requests\NotificationRequest;
use App\Models\Notification;
use App\Models\Setting;
use App\Services\NotificationTypeRegistry;
use App\Services\ReceiverTypeRegistry;

class NotificationController extends Controller
{
    public function __construct(
        private readonly NotificationService $notificationService,
        private readonly EmailTemplateService $emailTemplateService,
    ) {
    }

    public function index(): Renderable
    {
        $this->authorize('manage', Setting::class);

        $this->setBreadcrumbTitle(__('Notifications'))
            ->setBreadcrumbIcon('lucide:bell')
            ->setBreadcrumbActionButton(
                route('admin.notifications.create'),
                __('New Notification'),
                'feather:plus',
                'settings.edit'
            );

        return $this->renderViewWithBreadcrumbs('backend.pages.notifications.index');
    }

    public function create(): Renderable
    {
        $this->authorize('manage', Setting::class);

        $this->setBreadcrumbTitle(__('Create Notification'))
            ->setBreadcrumbIcon('lucide:bell')
            ->addBreadcrumbItem(__('Settings'), route('admin.settings.index'))
            ->addBreadcrumbItem(__('Notifications'), route('admin.notifications.index'));

        return $this->renderViewWithBreadcrumbs('backend.pages.notifications.create', [
            'notificationTypes' => NotificationTypeRegistry::getDropdownItems(),
            'receiverTypes' => ReceiverTypeRegistry::getDropdownItems(),
            'emailTemplates' => $this->emailTemplateService->getEmailTemplatesDropdown(),
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
            ->setBreadcrumbIcon('lucide:bell')
            ->addBreadcrumbItem(__('Settings'), route('admin.settings.index'))
            ->addBreadcrumbItem(__('Notifications'), route('admin.notifications.index'));

        // Load the email template relationship
        $notification->load('emailTemplate');

        // Use raw template HTML to show the template structure with variable placeholders visible
        $previewHtml = $notification->emailTemplate->body_html ?? '';

        return $this->renderViewWithBreadcrumbs('backend.pages.notifications.show', compact('notification', 'previewHtml'));
    }

    public function edit(Notification $notification): Renderable
    {
        $this->authorize('manage', Setting::class);

        $this->setBreadcrumbTitle(__('Edit Notification'))
            ->setBreadcrumbIcon('lucide:bell')
            ->addBreadcrumbItem(__('Settings'), route('admin.settings.index'))
            ->addBreadcrumbItem(__('Notifications'), route('admin.notifications.index'))
            ->setBreadcrumbActionButton(
                route('admin.notifications.show', $notification->id),
                __('View Notification'),
                'lucide:eye',
                'settings.view',
                true,
            );

        return $this->renderViewWithBreadcrumbs('backend.pages.notifications.edit', [
            'notification' => $notification,
            'notificationTypes' => NotificationTypeRegistry::getDropdownItems(),
            'receiverTypes' => ReceiverTypeRegistry::getDropdownItems(),
            'emailTemplates' => $this->emailTemplateService->getEmailTemplatesDropdown(),
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
