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
                ->subject($formattedSubject)
                ->view('emails.custom-html', [
                    'content' => $formattedContent,
                    'settings' => config('settings'),
                ]);

            $message->from($from ?? config('mail.from.address'), $from ? null : config('mail.from.name'));

            return $message;
        } catch (\Throwable $th) {
            Log::error('Failed to send email', ['error' => $th->getMessage(), 'from' => $from]);
            throw $th;
        }
    }
}
