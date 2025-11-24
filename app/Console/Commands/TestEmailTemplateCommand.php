<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Emails\EmailTemplateService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class TestEmailTemplateCommand extends Command
{
    protected $signature = 'email:test-template {uuid} {email}';
    protected $description = 'Send a test email using an email template';

    public function __construct(private readonly EmailTemplateService $emailTemplateService)
    {
        parent::__construct();
    }

    public function handle()
    {
        $uuid = $this->argument('uuid');
        $email = $this->argument('email');

        try {
            $this->info('Testing email template...');
            $this->info('Template UUID: ' . $uuid);
            $this->info('Email: ' . $email);

            $template = $this->emailTemplateService->getTemplateByUuid($uuid);

            if (! $template) {
                $this->error('Template not found with UUID: ' . $uuid);
                return;
            }

            $this->info('Template found: ' . $template->name);

            $sampleData = [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'full_name' => 'John Doe',
                'email' => $email,
                'phone' => '+1 (555) 123-4567',
                'company' => 'Lara Dashboard',
                'job_title' => 'Marketing Manager',
                'dob' => '1980-01-15',
                'industry' => 'Technology',
                'website' => 'www.example.com',
            ];

            $rendered = $template->renderTemplate($sampleData);
            $this->info('Template rendered successfully');
            $this->info('Subject: ' . $rendered['subject']);

            Mail::send([], [], function ($message) use ($rendered, $email) {
                $message->to($email)
                    ->from(config('mail.from.address'), config('mail.from.name'))
                    ->subject($rendered['subject'])
                    ->html($rendered['body_html']);
            });

            $this->info('Test email sent successfully to: ' . $email);

        } catch (\Exception $e) {
            $this->error('Failed to send test email: ' . $e->getMessage());
            Log::error('Test email template failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }
    }
}
