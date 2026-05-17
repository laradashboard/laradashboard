<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class DailyErrorDigest extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @param  Collection<int, \App\Models\ErrorNotificationLog>  $errors
     */
    public function __construct(
        public readonly Collection $errors,
        public readonly int $windowHours,
    ) {
    }

    public function envelope(): Envelope
    {
        $count = $this->errors->count();
        $appName = (string) config('app.name', 'Application');

        return new Envelope(
            subject: __(':app — :count new error(s) in the last :hours h', [
                'app' => $appName,
                'count' => $count,
                'hours' => $this->windowHours,
            ]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.daily_error_digest',
        );
    }
}
