<?php

declare(strict_types=1);

use App\Database\Concerns\TogglesForeignKeyConstraints;
use App\Enums\NotificationType;
use App\Enums\ReceiverType;
use App\Enums\TemplateType;
use App\Services\Builder\BlockService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class () extends Migration {
    use TogglesForeignKeyConstraints;

    public function up(): void
    {
        $blockService = app(BlockService::class);
        $canvasSettings = $blockService->getDefaultCanvasSettings();

        $blocks = $this->getAccountCreatedBlocks($blockService);

        // Temporarily disable foreign key checks — users table may be empty during migration
        $this->disableForeignKeyChecks();

        // Seed the email template
        DB::table('email_templates')->insert([
            'uuid' => (string) Str::uuid(),
            'name' => 'Account Created',
            'subject' => 'Your Account on {app_name} — Get Started',
            'body_html' => $blockService->generateEmailHtml($blocks, $canvasSettings),
            'design_json' => json_encode([
                'blocks' => $blocks,
                'canvasSettings' => $canvasSettings,
                'version' => 1,
            ]),
            'type' => TemplateType::AUTHENTICATION->value,
            'description' => 'Email sent to users when their account is created by an administrator',
            'is_active' => true,
            'is_default' => false,
            'is_deleteable' => false,
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Link the notification to the template
        $template = DB::table('email_templates')->where('name', 'Account Created')->first();

        if ($template) {
            DB::table('notifications')->insert([
                'uuid' => (string) Str::uuid(),
                'name' => 'Account Created Notification',
                'description' => 'Email sent to users when their account is created by an administrator, including their username and a link to set their password.',
                'notification_type' => NotificationType::ACCOUNT_CREATED->value,
                'email_template_id' => $template->id,
                'receiver_type' => ReceiverType::USER->value,
                'receiver_ids' => json_encode([]),
                'receiver_emails' => json_encode([]),
                'is_active' => true,
                'is_deleteable' => false,
                'track_opens' => true,
                'track_clicks' => true,
                'from_email' => null,
                'from_name' => null,
                'reply_to_email' => null,
                'reply_to_name' => null,
                'settings' => json_encode([]),
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->enableForeignKeyChecks();
    }

    public function down(): void
    {
        $template = DB::table('email_templates')->where('name', 'Account Created')->first();

        if ($template) {
            DB::table('notifications')->where('email_template_id', $template->id)->delete();
            DB::table('email_templates')->where('id', $template->id)->delete();
        }
    }

    /**
     * Get the block structure for the Account Created email template.
     */
    private function getAccountCreatedBlocks(BlockService $blockService): array
    {
        return [
            $blockService->text('{site_icon_image}', 'center'),
            $blockService->spacer('10px'),
            $blockService->heading('Your Account Has Been Created', 'h1', 'center', '#635bff', '28px'),
            $blockService->spacer('20px'),
            $blockService->text('Hello <strong>{full_name}</strong>,'),
            $blockService->spacer('10px'),
            $blockService->text('An administrator has created an account for you on <strong>{app_name}</strong>. You can log in and start using your account right away.'),
            $blockService->spacer('10px'),
            $blockService->divider(),
            $blockService->spacer('10px'),
            $blockService->text('<strong>Your Account Details:</strong>', 'left', '#333333'),
            $blockService->spacer('10px'),
            $blockService->listBlock([
                'Username: {username}',
                'Email: {email}',
            ]),
            $blockService->spacer('20px'),
            $blockService->text('To get started, please set your password by clicking the button below:'),
            $blockService->spacer('20px'),
            $blockService->button('Set Your Password', '{set_password_url}', '#635bff'),
            $blockService->spacer('20px'),
            $blockService->quote('This link will expire in {expiry_time}. If it expires, you can use the "Forgot Password" option on the login page to request a new link.'),
            $blockService->spacer('20px'),
            $blockService->text('If the button above doesn\'t work, copy and paste this URL into your browser:', 'left', '#666666', '14px'),
            $blockService->text('{set_password_url}', 'left', '#635bff', '13px'),
            $blockService->spacer('20px'),
            $blockService->text('You can also log in directly if you already know your password:', 'left', '#666666', '14px'),
            $blockService->text('{login_url}', 'left', '#635bff', '13px'),
            $blockService->spacer('30px'),
            $blockService->footer('{app_name}', '', '', showUnsubscribe: false),
        ];
    }
};
