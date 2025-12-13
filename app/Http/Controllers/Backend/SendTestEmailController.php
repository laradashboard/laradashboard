<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\SendTestEmailRequest;
use App\Models\EmailTemplate;
use App\Models\Notification;
use App\Services\Emails\EmailVariable;
use App\Services\Emails\EmailSender;
use App\Services\Emails\Mailer;
use App\Support\Facades\Hook;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;

class SendTestEmailController extends Controller
{
    public function __construct(
        private readonly EmailVariable $emailVariable,
        private readonly Mailer $mailer,
    ) {
    }

    public function sendTestEmail(SendTestEmailRequest $request): JsonResponse
    {
        $type = $request->input('type');
        $id = (int) $request->input('id');
        $email = $request->input('email');

        switch ($type) {
            case 'email-template':
                $emailTemplate = EmailTemplate::findOrFail($id);
                return $this->sendTestEmailTemplate($emailTemplate, $email);
            case 'notification':
                $notification = Notification::findOrFail($id);
                return $this->sendTestNotification($notification, $email);
            default:
                break;
        }

        return Hook::applyFilters(
            'send_test_email_controller_send_test_email',
            response()->json(['message' => 'Invalid email type specified.'], 400),
            $request
        );
    }

    public function sendTestEmailTemplate(EmailTemplate $emailTemplate, string $email): JsonResponse
    {
        try {
            $rendered = $emailTemplate->renderTemplate($this->emailVariable->getPreviewSampleData());

            $emailSender = app(EmailSender::class);
            $emailSender->setSubject($rendered['subject'] ?? '')->setContent($rendered['body_html'] ?? '');

            $this->sendMailMessageToRecipient($emailSender, $email, null, $this->emailVariable->getPreviewSampleData());

            return response()->json(['message' => __('Test email sent successfully.')]);
        } catch (\Exception $e) {
            Log::error('Failed to send test email template', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Failed to send test email: ' . $e->getMessage()], 500);
        }
    }

    public function sendTestNotification(Notification $notification, string $email): JsonResponse
    {
        try {
            $notification->load('emailTemplate');

            if (! $notification->emailTemplate) {
                throw new \Exception('No email template associated with this notification.');
            }

            $emailSender = app(EmailSender::class);
            $emailSender->setSubject($notification->emailTemplate->subject)
                ->setContent($notification->emailTemplate->body_html ?? '');

            $this->sendMailMessageToRecipient($emailSender, $email, null, $this->emailVariable->getPreviewSampleData());

            return response()->json(['message' => __('Test email sent successfully.')]);
        } catch (\Exception $e) {
            Log::error('Failed to send test notification email', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return response()->json(['message' => 'Failed to send test email: ' . $e->getMessage()], 500);
        }
    }

    private function sendMailMessageToRecipient(EmailSender $emailSender, string $recipient, ?string $from = null, array $variables = []): void
    {
        /** @var MailMessage $mailMessage */
        $mailMessage = $emailSender->getMailMessage($from, $variables);

        $html = (string) $mailMessage->render();
        $subject = (string) $mailMessage->subject;
        $fromEmail = $mailMessage->from[0] ?? config('mail.from.address');
        $fromName = $mailMessage->from[1] ?? config('mail.from.name');
        $replyTo = $mailMessage->replyTo[0] ?? null;

        // Ensure fromEmail is always a valid string
        if (empty($fromEmail) || ! filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
            $fromEmail = 'no-reply@laradashboard.com';
        }
        if (empty($fromName)) {
            $fromName = 'Lara Dashboard';
        }

        // Use the unified Mailer service which respects email connections
        $this->mailer->html($html, function ($message) use ($subject, $recipient, $fromEmail, $fromName, $replyTo) {
            $message->to($recipient)
                ->from($fromEmail, $fromName)
                ->subject($subject);
            if (! empty($replyTo)) {
                $message->replyTo($replyTo[0] ?? $replyTo, $replyTo[1] ?? null);
            }
        });
    }
}
