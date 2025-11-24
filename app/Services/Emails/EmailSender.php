<?php

declare(strict_types=1);

namespace App\Services\Emails;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;

class EmailSender
{
    private string $subject;
    private string $content;
    private EmailVariable $emailVariable;

    public function __construct(
        private string $s = '',
        private string $c = '',
    ) {
        $this->subject = $s;
        $this->content = $c;
    }

    public function getMailMessage($from = null, $variables = []): MailMessage
    {
        $this->emailVariable = new EmailVariable();

        try {
            $variables = array_merge($this->emailVariable->getReplacementData(), $variables);
            $formattedSubject = $this->emailVariable->replaceVariables($this->subject, $variables);
            $formattedContent = $this->emailVariable->replaceVariables($this->content, $variables);

            $message = (new MailMessage())
                ->subject($formattedSubject);

            // Set from name/email if provided in settings.
            $fromEmail = $from ?? config('settings.email_from_email');
            $fromName = config('settings.email_from_name');
            if (! empty($fromEmail)) {
                $message->from($fromEmail, $fromName ?: null);
            }

            // Set reply-to if set in settings.
            $replyToEmail = config('settings.email_reply_to_email');
            $replyToName = config('settings.email_reply_to_name');
            if (! empty($replyToEmail)) {
                $message->replyTo($replyToEmail, $replyToName ?: null);
            }

            // Append UTM parameters to all links in the email content.
            $utmSource = config('settings.email_utm_source_default');
            $utmMedium = config('settings.email_utm_medium_default', 'email');
            if (! empty($utmSource)) {
                $formattedContent = $this->emailVariable->appendUtmParametersToLinks($formattedContent, $utmSource, $utmMedium);
            }

            $message
                ->view('emails.custom-html', [
                    'content' => $formattedContent,
                    'settings' => config('settings'),
                ]);

            return $message;
        } catch (\Throwable $th) {
            Log::error('Failed to send email', ['error' => $th->getMessage(), 'from' => $from]);
            throw $th;
        }
    }
}
