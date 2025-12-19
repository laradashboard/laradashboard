<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\EmailSubscriptionService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Crypt;

class UnsubscribeController extends Controller
{
    public function __construct(
        private EmailSubscriptionService $subscriptionService
    ) {
    }

    public function unsubscribe(string $encryptedEmail): View
    {
        $result = $this->subscriptionService->processUnsubscribe($encryptedEmail);

        return view('unsubscribe.result', [
            'success' => $result['success'],
            'message' => $result['message'],
            'email' => $result['email'],
        ]);
    }

    public function confirm(string $encryptedEmail): View
    {
        try {
            $email = Crypt::decryptString($encryptedEmail);

            return view('unsubscribe.confirm', [
                'email' => $email,
                'encryptedEmail' => $encryptedEmail,
            ]);
        } catch (\Exception $e) {
            return view('unsubscribe.result', [
                'success' => false,
                'message' => __('Invalid unsubscribe link.'),
                'email' => null,
            ]);
        }
    }

    public function processConfirmed(Request $request, string $encryptedEmail): RedirectResponse
    {
        $result = $this->subscriptionService->processUnsubscribe($encryptedEmail);

        return redirect()->route('unsubscribe.result', $encryptedEmail)
            ->with('result', $result);
    }
}
