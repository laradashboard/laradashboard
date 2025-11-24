<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\SendTestEmailRequest;
use App\Models\EmailTemplate;
use App\Models\Notification;
use App\Models\Setting;
use App\Services\Emails\EmailVariable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendTestEmailController extends Controller
{
    public function __construct(
        private readonly EmailVariable $emailVariable,
    ) {
    }

    public function sendTestEmailTemplate(EmailTemplate $emailTemplate, SendTestEmailRequest $request): JsonResponse
    {
        $this->authorize('manage', Setting::class);

        try {
            $rendered = $emailTemplate->renderTemplate($this->emailVariable->getPreviewSampleData());
            Mail::send([], [], function ($message) use ($rendered, $request) {
                $message->to($request->input('email'))
                    ->from(config('mail.from.address'), config('mail.from.name'))
                    ->subject($rendered['subject'])
                    ->html($rendered['body_html']);
            });

            return response()->json(['message' => __('Test email sent successfully')]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to send test email: ' . $e->getMessage()], 500);
        }
    }

    public function sendTestNotification(Notification $notification, SendTestEmailRequest $request): JsonResponse
    {
        $this->authorize('manage', Setting::class);

        try {
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