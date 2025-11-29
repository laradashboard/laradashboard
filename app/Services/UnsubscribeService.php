<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Crypt;

class UnsubscribeService
{
    public function generateLink(string $email): string
    {
        $hash = Crypt::encryptString($email);
        return route('unsubscribe', $hash);
    }

    public function process(string $hash)
    {
        try {
            $email = Crypt::decryptString($hash);
            $user = User::where('email', $email)->first();

            if ($user) {
                $user->update(['email_subscribed' => false]);
                return view('unsubscribe-success');
            }
        } catch (\Exception $e) {
            // Invalid hash
        }

        return abort(404);
    }

    public function addFooterToEmail(string $content, string $email): string
    {
        $link = $this->generateLink($email);
        return $content . "<p><a href='{$link}'>Unsubscribe</a></p>";
    }
}
