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
use App\Enums\ReceiverType;
use App\Models\Notification;
use App\Models\NotificationType;
use App\Models\Setting;
use App\Services\Emails\EmailVariable;
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

        $notificationTypeInstance = new NotificationType();
        $notificationTypes = collect(NotificationType::getValues())
            ->mapWithKeys(function ($type) use ($notificationTypeInstance) {
                return [$type => $notificationTypeInstance->label($type)];
            })
            ->toArray();

        $receiverTypes = collect(ReceiverType::cases())
            ->mapWithKeys(function ($type) {
                return [$type->value => $type->label()];
            })
            ->toArray();

        $emailTemplates = $this->emailTemplateService->getAllTemplates();

        $this->setBreadcrumbTitle(__('Create Notification'))
            ->addBreadcrumbItem(__('Settings'), route('admin.settings.index'))
            ->addBreadcrumbItem(__('Notifications'), route('admin.notifications.index'));

        return $this->renderViewWithBreadcrumbs('backend.pages.notifications.create', [
            'notificationTypes' => $notificationTypes,
            'receiverTypes' => $receiverTypes,
            'emailTemplates' => $emailTemplates,
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

        $notificationTypeInstance = new NotificationType();
        $notificationTypes = collect(NotificationType::getValues())
            ->mapWithKeys(function ($type) use ($notificationTypeInstance) {
                return [$type => $notificationTypeInstance->label($type)];
            })
            ->toArray();

        $receiverTypes = collect(ReceiverType::cases())
            ->mapWithKeys(function ($type) {
                return [$type->value => $type->label()];
            })
            ->toArray();

        $emailTemplates = $this->emailTemplateService->getAllTemplates();

        $this->setBreadcrumbTitle(__('Edit Notification'))
            ->addBreadcrumbItem(__('Settings'), route('admin.settings.index'))
            ->addBreadcrumbItem(__('Notifications'), route('admin.notifications.index'));

        return $this->renderViewWithBreadcrumbs('backend.pages.notifications.edit', [
            'notification' => $notification,
            'notificationTypes' => $notificationTypes,
            'receiverTypes' => $receiverTypes,
            'emailTemplates' => $emailTemplates,
        ]);
    }

    public function update(NotificationRequest $request, Notification $notification): RedirectResponse
    {
        $this->authorize('manage', Setting::class);

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
