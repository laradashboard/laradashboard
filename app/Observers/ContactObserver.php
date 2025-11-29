<?php

declare(strict_types=1);

namespace App\Observers;

use Modules\Crm\Models\Contact;
use App\Services\EmailSubscriptionService;
use Illuminate\Support\Facades\Log;

class ContactObserver
{
    public function __construct(
        private EmailSubscriptionService $subscriptionService
    ) {}

    public function created(Contact $contact): void
    {
        if ($contact->email) {
            try {
                $this->subscriptionService->subscribe($contact->email);
                Log::info("Email subscription created for contact: {$contact->email}");
            } catch (\Exception $e) {
                Log::error("Failed to create email subscription for contact {$contact->email}: " . $e->getMessage());
            }
        }
    }

    public function updated(Contact $contact): void
    {
        if ($contact->isDirty('email') && $contact->email) {
            try {
                $this->subscriptionService->subscribe($contact->email);
                Log::info("Email subscription updated for contact: {$contact->email}");
            } catch (\Exception $e) {
                Log::error("Failed to update email subscription for contact {$contact->email}: " . $e->getMessage());
            }
        }
    }

    public function deleted(Contact $contact): void
    {
        if ($contact->email) {
            try {
                $this->subscriptionService->unsubscribe($contact->email);
                Log::info("Email unsubscribed for deleted contact: {$contact->email}");
            } catch (\Exception $e) {
                Log::error("Failed to unsubscribe deleted contact {$contact->email}: " . $e->getMessage());
            }
        }
    }

    public function restored(Contact $contact): void
    {
        if ($contact->email) {
            try {
                $this->subscriptionService->subscribe($contact->email);
            } catch (\Exception $e) {
                Log::error("Failed to restore email subscription for contact {$contact->email}: " . $e->getMessage());
            }
        }
    }

    public function forceDeleted(Contact $contact): void
    {
        if ($contact->email) {
            try {
                $this->subscriptionService->unsubscribe($contact->email);
            } catch (\Exception $e) {
                Log::error("Failed to force unsubscribe contact {$contact->email}: " . $e->getMessage());
            }
        }
    }
}
