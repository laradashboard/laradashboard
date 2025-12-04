<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class TestEmailCommand extends Command
{
    protected $signature = 'email:test {email}';
    protected $description = 'Send a test email to verify email configuration';

    public function handle()
    {
        $email = $this->argument('email');

        try {
            $this->info('Testing email configuration...');
            $this->info('MAIL_MAILER: ' . config('mail.default'));
            $this->info('MAIL_HOST: ' . config('mail.mailers.smtp.host'));
            $this->info('MAIL_PORT: ' . config('mail.mailers.smtp.port'));
            $this->info('MAIL_FROM_ADDRESS: ' . config('mail.from.address'));

            Mail::raw('This is a test email from Lara Dashboard.', function ($message) use ($email) {
                $message->to($email)
                    ->from(config('mail.from.address'), config('mail.from.name'))
                    ->subject('Test Email from Lara Dashboard');
            });

            $this->info('Test email sent successfully to: ' . $email);

        } catch (\Exception $e) {
            $this->error('Failed to send test email: ' . $e->getMessage());
            Log::error('Test email failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }
    }
}
