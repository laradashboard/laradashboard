<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\EmailSubscription;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class EmailSubscriptionService
{
    public function subscribe(string $email): EmailSubscription
    {
        return EmailSubscription::updateOrCreate(
            ['email' => $email],
            [
                'subscribed' => true,
                'unsubscribed_at' => null,
            ]
        );
    }

    public function unsubscribe(string $email): bool
    {
        $subscription = EmailSubscription::where('email', $email)->first();

        if (! $subscription) {
            return false;
        }

        return $subscription->unsubscribe();
    }

    public function isSubscribed(string $email): bool
    {
        $subscription = EmailSubscription::where('email', $email)->first();

        return $subscription ? $subscription->isSubscribed() : false;
    }

    public function generateUnsubscribeUrl(string $email): string
    {
        $subscription = $this->subscribe($email);

        if (! $subscription->unsubscribe_token) {
            $subscription->generateUnsubscribeToken();
        }

        $encryptedEmail = Crypt::encryptString($email);

        return url("/unsubscribe/{$encryptedEmail}");
    }

    public function processUnsubscribe(string $encryptedEmail): array
    {
        try {
            $email = Crypt::decryptString($encryptedEmail);

            $success = $this->unsubscribe($email);

            if ($success) {
                Log::info("Email unsubscribed successfully: {$email}");
                return [
                    'success' => true,
                    'message' => __('You have been successfully unsubscribed.'),
                    'email' => $email,
                ];
            }

            return [
                'success' => false,
                'message' => __('Email address not found in our subscription list.'),
                'email' => $email,
            ];

        } catch (\Exception $e) {
            Log::error('Unsubscribe error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => __('Invalid unsubscribe link.'),
                'email' => null,
            ];
        }
    }

    public function getStats(): array
    {
        return [
            'total' => EmailSubscription::count(),
            'subscribed' => EmailSubscription::subscribed()->count(),
            'unsubscribed' => EmailSubscription::unsubscribed()->count(),
        ];
    }

    public function getUnsubscribeFooter(string $email): string
    {
        $unsubscribeUrl = $this->generateUnsubscribeUrl($email);

        $unsubscribeText = __('If you no longer wish to receive these emails, you can');
        $unsubscribeLinkText = __('unsubscribe here');
        $emailSentText = __('This email was sent to');

        return <<<HTML
        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #e5e7eb; text-align: center; font-size: 12px; color: #9ca3af;">
            <p style="margin: 0 0 10px 0;">
                {$unsubscribeText} 
                <a href="{$unsubscribeUrl}" style="color: #3b82f6; text-decoration: none;">{$unsubscribeLinkText}</a>.
            </p>
            <p style="margin: 0; font-size: 11px; color: #6b7280;">
                {$emailSentText} {$email}
            </p>
        </div>
        HTML;
    }
}
