<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\NotificationService;
use App\Services\EmailTemplateService;
use App\Http\Requests\NotificationRequest;
use App\Enums\ReceiverType;
use App\Models\NotificationType;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationsController extends Controller
{
    public function __construct(
        private readonly NotificationService $notificationService,
        private readonly EmailTemplateService $emailTemplateService,
    ) {
    }

    public function index(): Renderable
    {
        $this->authorize('manage', Setting::class);

        $this->breadcrumbs = [
            'title' => __('Notifications'),
            'items' => [
                ['label' => __('Settings'), 'url' => route('admin.settings.index')],
            ],
        ];

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

        return view('backend.pages.notifications.create', [
            'notificationTypes' => $notificationTypes,
            'receiverTypes' => $receiverTypes,
            'emailTemplates' => $emailTemplates,
            'breadcrumbs' => [
                'title' => __('Create Notification'),
                'items' => [
                    ['label' => __('Settings'), 'url' => route('admin.settings.index')],
                    ['label' => __('Notifications'), 'url' => route('admin.notifications.index')],
                ],
            ],
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

    public function show(int $notification): Renderable
    {
        $this->authorize('manage', Setting::class);

        $notificationModel = $this->notificationService->getNotificationById($notification);

        if (! $notificationModel) {
            abort(404, __('Notification not found.'));
        }

        return view('backend.pages.notifications.show', [
            'notification' => $notificationModel,
            'breadcrumbs' => [
                'title' => $notificationModel->name,
                'items' => [
                    ['label' => __('Settings'), 'url' => route('admin.settings.index')],
                    ['label' => __('Notifications'), 'url' => route('admin.notifications.index')],
                ],
            ],
        ]);
    }

    public function edit(int $notification): Renderable
    {
        $this->authorize('manage', Setting::class);

        $notificationModel = $this->notificationService->getNotificationById($notification);

        if (! $notificationModel) {
            abort(404, __('Notification not found.'));
        }

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

        return view('backend.pages.notifications.edit', [
            'notification' => $notificationModel,
            'notificationTypes' => $notificationTypes,
            'receiverTypes' => $receiverTypes,
            'emailTemplates' => $emailTemplates,
            'breadcrumbs' => [
                'title' => __('Edit Notification'),
                'items' => [
                    ['label' => __('Settings'), 'url' => route('admin.settings.index')],
                    ['label' => __('Notifications'), 'url' => route('admin.notifications.index')],
                ],
            ],
        ]);
    }

    public function update(NotificationRequest $request, int $notification): RedirectResponse
    {
        $this->authorize('manage', Setting::class);

        $notificationModel = $this->notificationService->getNotificationById($notification);

        if (! $notificationModel) {
            abort(404, __('Notification not found.'));
        }

        try {
            $this->notificationService->updateNotification($notificationModel, $request->validated());

            return redirect()
                ->route('admin.notifications.index')
                ->with('success', __('Notification updated successfully.'));
        } catch (\Exception $e) {
            Log::error('Failed to update notification', [
                'notification_id' => $notification,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', __('Failed to update notification: :error', ['error' => $e->getMessage()]));
        }
    }

    public function destroy(int $notification): RedirectResponse
    {
        $this->authorize('manage', Setting::class);

        $notificationModel = $this->notificationService->getNotificationById($notification);

        if (! $notificationModel) {
            abort(404, __('Notification not found.'));
        }

        try {
            $this->notificationService->deleteNotification($notificationModel);

            return redirect()
                ->route('admin.notifications.index')
                ->with('success', __('Notification deleted successfully.'));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', __('Failed to delete notification: :error', ['error' => $e->getMessage()]));
        }
    }

    public function previewPage(int $notification): Renderable
    {
        $this->authorize('manage', Setting::class);

        $notificationModel = $this->notificationService->getNotificationById($notification);

        if (! $notificationModel) {
            abort(404, __('Notification not found.'));
        }

        // Load email template if exists
        if ($notificationModel->email_template_id) {
            $notificationModel->load(['emailTemplate.headerTemplate', 'emailTemplate.footerTemplate']);
        }

        $sampleData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'full_name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'phone' => '+1 (555) 123-4567',
            'company' => 'Lara Dashboard',
            'job_title' => 'Marketing Manager',
            'dob' => '1980-01-15',
            'industry' => 'Technology',
            'website' => 'www.example.com',
        ];

        $rendered = $this->renderNotificationContent($notificationModel, $sampleData);

        return view('backend.pages.notifications.preview', [
            'notification' => $notificationModel,
            'rendered' => $rendered,
            'breadcrumbs' => [
                'title' => __('Preview Notification'),
                'items' => [
                    ['label' => __('Settings'), 'url' => route('admin.settings.index')],
                    ['label' => __('Notifications'), 'url' => route('admin.notifications.index')],
                    ['label' => $notificationModel->name, 'url' => route('admin.notifications.show', $notification)],
                ],
            ],
        ]);
    }

    public function sendTestEmail(int $notification, Request $request): JsonResponse
    {
        $this->authorize('manage', Setting::class);

        $notificationModel = $this->notificationService->getNotificationById($notification);

        if (! $notificationModel) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        $request->validate([
            'email' => 'required|email',
        ]);

        try {
            Log::info('Sending test notification email', ['notification_id' => $notification, 'email' => $request->input('email')]);

            // Load email template if exists
            if ($notificationModel->email_template_id) {
                $notificationModel->load(['emailTemplate.headerTemplate', 'emailTemplate.footerTemplate']);
            }

            $sampleData = [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'full_name' => 'John Doe',
                'email' => $request->input('email'),
                'phone' => '+1 (555) 123-4567',
                'company' => 'Lara Dashboard',
                'job_title' => 'Marketing Manager',
                'dob' => '1980-01-15',
                'industry' => 'Technology',
                'website' => 'www.example.com',
            ];

            $rendered = $this->renderNotificationContent($notificationModel, $sampleData);
            Log::info('Notification content rendered', ['subject' => $rendered['subject']]);

            $fromEmail = $notificationModel->from_email ?: config('mail.from.address');
            $fromName = $notificationModel->from_name ?: config('mail.from.name');

            Mail::send([], [], function ($message) use ($rendered, $request, $fromEmail, $fromName) {
                $message->to($request->input('email'))
                    ->from($fromEmail, $fromName)
                    ->subject($rendered['subject'])
                    ->html($rendered['body_html']);
            });

            Log::info('Test notification email sent successfully');

            return response()->json(['message' => 'Test email sent successfully']);
        } catch (\Exception $e) {
            Log::error('Failed to send test notification email', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Failed to send test email: ' . $e->getMessage()], 500);
        }
    }

    private function renderNotificationContent($notification, array $data): array
    {
        $subject = $notification->name;
        $bodyHtml = '';

        // If notification has custom content, use it
        if ($notification->body_html) {
            $bodyHtml = $this->replaceVariables($notification->body_html ?? '', $data);

            // If there's an email template, we might want to use subject from it
            if ($notification->emailTemplate) {
                $subject = $this->replaceVariables($notification->emailTemplate->subject, $data);
            }
        }
        // Otherwise, use the email template
        elseif ($notification->emailTemplate) {
            $rendered = $this->emailTemplateService->renderTemplate($notification->emailTemplate, $data);
            $subject = $rendered['subject'];
            $bodyHtml = $rendered['body_html'];
        }

        return [
            'subject' => $subject,
            'body_html' => $bodyHtml,
        ];
    }

    private function replaceVariables(string $content, array $data): string
    {
        foreach ($data as $key => $value) {
            $content = str_replace('{' . $key . '}', $value, $content);
        }
        return $content;
    }
}
