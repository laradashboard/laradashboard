<?php

declare(strict_types=1);

namespace App\Services\Emails;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;

class EmailManager
{
    public function __construct(private readonly EmailVariable $emailVariable) {
    }

    public function sendEmail($subject, $content, $from = null, $variables = []): MailMessage
    {
        try {
            $variables = array_merge($this->emailVariable->getReplacementData(), $variables);
            $formattedSubject = $this->emailVariable->replaceVariables($subject, $variables);
            $formattedContent = $this->emailVariable->replaceVariables($content, $variables);

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
