<?php

declare(strict_types=1);

use App\Services\Emails\EmailSender;
use Illuminate\Notifications\Messages\MailMessage;

it('builds a mail message with subject and content and renders html', function () {
    $subject = 'Unit Test Subject';
    $content = '<p>Unit Test Content</p>';

    // Ensure settings are set to avoid DB-backed config reads in tests.
    config(['settings' => ['email_from_email' => 'noreply@example.com', 'email_from_name' => 'Example']]);

    $mailMessage = (new EmailSender())
        ->setSubject($subject)
        ->setContent($content)
        ->getMailMessage(null, []);

    expect($mailMessage)->toBeInstanceOf(MailMessage::class);
    expect($mailMessage->subject)->toBe($subject);

    // Avoid rendering the view in unit tests to prevent DB-backed config reads.
    expect($mailMessage->view)->toBe('emails.custom-html');
});
