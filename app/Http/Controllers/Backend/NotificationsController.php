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

        return $this->renderViewWithBreadcrumbs('backend.pages.notifications.create', [
            'notificationTypes' => NotificationTypeRegistry::all(),
            'receiverTypes' => ReceiverTypeRegistry::all(),
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

        return $this->renderViewWithBreadcrumbs('backend.pages.notifications.edit', [
            'notification' => $notification,
            'notificationTypes' => NotificationTypeRegistry::all(),
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

    public function sendTestEmail(Notification $notification, Request $request): JsonResponse
    {
        $this->authorize('manage', Setting::class);

        $request->validate([
            'email' => 'required|email',
        ]);

        try {
            // Load email template if exists.
            if ($notification->email_template_id) {
                $notification->load(['emailTemplate', 'emailTemplate.headerTemplate', 'emailTemplate.footerTemplate']);
            }

            if (! $notification->emailTemplate) {
                throw new \Exception('No email template associated with this notification.');
            }

            $subject = $this->emailVariable->replaceVariables($notification->emailTemplate->subject, $this->emailVariable->getPreviewSampleData());
            $content = $this->emailVariable->replaceVariables($notification->body_html ?? '', $this->emailVariable->getPreviewSampleData());

            $fromEmail = $notification->from_email ?: config('mail.from.address');
            $fromName = $notification->from_name ?: config('mail.from.name');

            Mail::send([], [], function ($message) use ($subject, $content, $request, $fromEmail, $fromName) {
                $message->to($request->input('email'))
                    ->from($fromEmail, $fromName)
                    ->subject($subject)
                    ->html($content);
            });
            return response()->json(['message' => __('Test email sent successfully.')]);
        } catch (\Exception $e) {
            Log::error('Failed to send test notification email', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Failed to send test email: ' . $e->getMessage()], 500);
        }
    }
}
