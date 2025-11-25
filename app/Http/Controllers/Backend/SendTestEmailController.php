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
use Illuminate\Notifications\Messages\MailMessage;
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
        try {
            $rendered = $emailTemplate->renderTemplate($this->emailVariable->getPreviewSampleData());

            $emailSender = app(EmailSender::class);
            $emailSender->setSubject($rendered['subject'] ?? '')->setContent($rendered['body_html'] ?? '');

            $this->sendMailMessageToRecipient($emailSender, $request->input('email'), null, $this->emailVariable->getPreviewSampleData());

            return response()->json(['message' => __('Test email sent successfully.')]);
        } catch (\Exception $e) {
            Log::error('Failed to send test email template', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Failed to send test email: ' . $e->getMessage()], 500);
        }
    }

    public function sendTestNotification(Notification $notification, SendTestEmailRequest $request): JsonResponse
    {
        try {
            if ($notification->email_template_id) {
                $notification->load(['emailTemplate', 'emailTemplate.headerTemplate', 'emailTemplate.footerTemplate']);
            }

            if (! $notification->emailTemplate) {
                throw new \Exception('No email template associated with this notification.');
            }

            $emailSender = app(EmailSender::class);
            $emailSender->setSubject($notification->emailTemplate->subject)
                ->setContent($notification->body_html ?? $notification->emailTemplate->body_html ?? '');

            $this->sendMailMessageToRecipient($emailSender, $request->input('email'), null, $this->emailVariable->getPreviewSampleData());
            return response()->json(['message' => __('Test email sent successfully.')]);
        } catch (\Exception $e) {
            Log::error('Failed to send test notification email', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Failed to send test email: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Build the MailMessage via EmailSender and send it to a recipient.
     */
    private function sendMailMessageToRecipient(EmailSender $emailSender, string $recipient, ?string $from = null, array $variables = []): void
    {
        /** @var MailMessage $mailMessage */
        $mailMessage = $emailSender->getMailMessage($from, $variables);

        $html = (string) $mailMessage->render();
        $subject = (string) $mailMessage->subject;
        $fromEmail = $mailMessage->from[0] ?? config('mail.from.address');
        $fromName = $mailMessage->from[1] ?? config('mail.from.name');
        $replyTo = $mailMessage->replyTo[0] ?? null;

        Mail::send([], [], function ($message) use ($html, $subject, $recipient, $fromEmail, $fromName, $replyTo) {
            $message->to($recipient)
                ->from($fromEmail, $fromName)
                ->subject($subject)
                ->html($html);
            if (! empty($replyTo)) {
                $message->replyTo($replyTo[0] ?? $replyTo, $replyTo[1] ?? null);
            }
        });
    }
}
