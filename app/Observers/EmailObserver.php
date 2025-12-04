<?php

declare(strict_types=1);

namespace App\Observers;

use App\Services\EmailSubscriptionService;
use Illuminate\Mail\Events\MessageSending;

class EmailObserver
{
    public function __construct(
        private EmailSubscriptionService $subscriptionService
    ) {
    }

    public function sending(MessageSending $event): void
    {
        $message = $event->message;
        $to = $message->getTo();
        $email = array_keys($to)[0] ?? null;

        if (! $email) {
            return;
        }

        if (! $this->subscriptionService->isSubscribed($email)) {
            return;
        }

        $body = $message->getHtmlBody() ?? $message->getTextBody() ?? '';

        if (empty($body)) {
            return;
        }

        $unsubscribeFooter = $this->subscriptionService->getUnsubscribeFooter($email);
        $bodyWithFooter = $this->addUnsubscribeFooter($body, $unsubscribeFooter, $email);

        $message->html($bodyWithFooter);
    }

    private function addUnsubscribeFooter(string $body, string $footer, string $email): string
    {
        $footer = str_replace('{email}', $email, $footer);
        $unsubscribeUrl = $this->subscriptionService->generateUnsubscribeUrl($email);
        $footer = str_replace('{unsubscribe_url}', $unsubscribeUrl, $footer);

        if (str_contains($body, '</body>')) {
            return str_replace('</body>', $footer . '\n</body>', $body);
        }

        return $body . $footer;
    }
}
