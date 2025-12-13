<?php

declare(strict_types=1);

namespace App\Services\Emails;

use App\Services\Builder\BlockRenderer;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;
use App\Services\UnsubscribeService;

class EmailSender
{
    private string $subject;
    private string $content;
    private ?EmailVariable $emailVariable = null;
    private ?BlockRenderer $blockRenderer = null;

    public function __construct(
        ?EmailVariable $emailVariable = null,
        ?BlockRenderer $blockRenderer = null,
    ) {
        if ($emailVariable !== null) {
            $this->emailVariable = $emailVariable;
        }
        if ($blockRenderer !== null) {
            $this->blockRenderer = $blockRenderer;
        }
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;
        return $this;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getMailMessage($from = null, $variables = [], $recipientEmail = null, $userType = 'user', $userId = null): MailMessage
    {
        if ($this->emailVariable === null) {
            $this->emailVariable = new EmailVariable();
        }

        if ($this->blockRenderer === null) {
            $this->blockRenderer = app(BlockRenderer::class);
        }

        try {
            $variables = array_merge($this->emailVariable->getReplacementData(), $variables);
            $formattedSubject = $this->emailVariable->replaceVariables($this->subject, $variables);
            $formattedContent = $this->emailVariable->replaceVariables($this->content, $variables);

            // Process any dynamic blocks (e.g., CRM Contact) with server-side rendering
            $formattedContent = $this->blockRenderer->processContent($formattedContent, 'email');

            if ($recipientEmail) {
                $formattedContent = app(UnsubscribeService::class)->addFooterToEmail($formattedContent, $recipientEmail);
            }

            $message = (new MailMessage())->subject($formattedSubject);

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
