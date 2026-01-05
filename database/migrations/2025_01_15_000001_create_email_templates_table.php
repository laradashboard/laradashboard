<?php

declare(strict_types=1);

use App\Database\Concerns\TogglesForeignKeyConstraints;
use App\Enums\TemplateType;
use App\Services\Builder\BlockService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class () extends Migration {
    use TogglesForeignKeyConstraints;
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->string('name');
            $table->string('subject')->nullable();
            $table->longText('body_html')->nullable();
            $table->json('design_json')->nullable();
            $table->string('type')->default(TemplateType::EMAIL->value);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_deleteable')->default(true);
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
        });

        // Seed essential email templates
        $this->seedEssentialTemplates();
    }

    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }

    /**
     * Seed essential email templates required for core application functionality.
     */
    private function seedEssentialTemplates(): void
    {
        $blockService = app(BlockService::class);
        $canvasSettings = $blockService->getDefaultCanvasSettings();

        // Temporarily disable foreign key checks for seeding (no users exist yet during migration)
        $this->disableForeignKeyChecks();

        // Forgot Password Template
        $forgotPasswordBlocks = $this->getForgotPasswordBlocks($blockService);
        DB::table('email_templates')->insert([
            'uuid' => (string) Str::uuid(),
            'name' => 'Forgot Password',
            'subject' => 'Reset Your Password - {app_name}',
            'body_html' => $blockService->generateEmailHtml($forgotPasswordBlocks, $canvasSettings),
            'design_json' => json_encode([
                'blocks' => $forgotPasswordBlocks,
                'canvasSettings' => $canvasSettings,
                'version' => 1,
            ]),
            'type' => TemplateType::AUTHENTICATION->value,
            'description' => 'Password reset email with security tips',
            'is_active' => true,
            'is_default' => false,
            'is_deleteable' => false,
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Registration Welcome Template
        $registrationBlocks = $this->getRegistrationWelcomeBlocks($blockService);
        DB::table('email_templates')->insert([
            'uuid' => (string) Str::uuid(),
            'name' => 'Registration Welcome',
            'subject' => 'Welcome to {app_name} - Your Account Has Been Created!',
            'body_html' => $blockService->generateEmailHtml($registrationBlocks, $canvasSettings),
            'design_json' => json_encode([
                'blocks' => $registrationBlocks,
                'canvasSettings' => $canvasSettings,
                'version' => 1,
            ]),
            'type' => TemplateType::AUTHENTICATION->value,
            'description' => 'Welcome email sent to new users after registration',
            'is_active' => true,
            'is_default' => false,
            'is_deleteable' => false,
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Email Verification Template
        $verificationBlocks = $this->getEmailVerificationBlocks($blockService);
        DB::table('email_templates')->insert([
            'uuid' => (string) Str::uuid(),
            'name' => 'Email Verification',
            'subject' => 'Verify Your Email Address - {app_name}',
            'body_html' => $blockService->generateEmailHtml($verificationBlocks, $canvasSettings),
            'design_json' => json_encode([
                'blocks' => $verificationBlocks,
                'canvasSettings' => $canvasSettings,
                'version' => 1,
            ]),
            'type' => TemplateType::AUTHENTICATION->value,
            'description' => 'Email sent to verify user email address',
            'is_active' => true,
            'is_default' => false,
            'is_deleteable' => false,
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->enableForeignKeyChecks();
    }

    /**
     * Get the block structure for the Forgot Password email template.
     */
    private function getForgotPasswordBlocks(BlockService $blockService): array
    {
        return [
            $blockService->text('{site_icon_image}', 'center'),
            $blockService->spacer('10px'),
            $blockService->heading('Password Reset Request', 'h1', 'center', '#333333', '28px'),
            $blockService->spacer('20px'),
            $blockService->text('Hello <strong>{full_name}</strong>,'),
            $blockService->spacer('10px'),
            $blockService->text('We received a request to reset your password for your <strong>{app_name}</strong> account. If you didn\'t make this request, you can safely ignore this email.'),
            $blockService->spacer('10px'),
            $blockService->text('To reset your password, click the button below:'),
            $blockService->spacer('20px'),
            $blockService->button('Reset My Password', '{reset_url}', '#635bff'),
            $blockService->spacer('20px'),
            $blockService->quote('Important: This password reset link will expire in {expiry_time}. If the link expires, you\'ll need to request a new password reset.'),
            $blockService->spacer('20px'),
            $blockService->text('If the button above doesn\'t work, copy and paste this URL into your browser:', 'left', '#666666', '14px'),
            $blockService->text('{reset_url}', 'left', '#635bff', '13px'),
            $blockService->spacer('20px'),
            $blockService->divider(),
            $blockService->text('<strong>Security Tips:</strong>', 'left', '#333333'),
            $blockService->listBlock([
                'Never share your password with anyone',
                'Use a strong, unique password',
                'Enable two-factor authentication if available',
            ]),
            $blockService->spacer('30px'),
            $blockService->footer('{app_name}'),
        ];
    }

    /**
     * Get the block structure for the Registration Welcome email template.
     */
    private function getRegistrationWelcomeBlocks(BlockService $blockService): array
    {
        return [
            $blockService->text('{site_icon_image}', 'center'),
            $blockService->spacer('10px'),
            $blockService->heading('Welcome to {app_name}!', 'h1', 'center', '#10b981', '28px'),
            $blockService->spacer('20px'),
            $blockService->text('Hello <strong>{full_name}</strong>,'),
            $blockService->spacer('10px'),
            $blockService->text('Thank you for creating an account with <strong>{app_name}</strong>! We\'re excited to have you on board.'),
            $blockService->spacer('10px'),
            $blockService->text('Your account has been successfully created and you can now access all the features available to you.'),
            $blockService->spacer('20px'),
            $blockService->button('Go to Dashboard', '{dashboard_url}', '#10b981'),
            $blockService->spacer('20px'),
            $blockService->divider(),
            $blockService->spacer('20px'),
            $blockService->text('<strong>Your Account Details:</strong>', 'left', '#333333'),
            $blockService->spacer('10px'),
            $blockService->listBlock([
                'Email: {email}',
                'Username: {username}',
                'Registered: {registered_at}',
            ]),
            $blockService->spacer('20px'),
            $blockService->divider(),
            $blockService->text('<strong>Getting Started:</strong>', 'left', '#333333'),
            $blockService->listBlock([
                'Complete your profile to personalize your experience',
                'Explore the available features and modules',
                'Need help? Visit our documentation or contact support',
            ]),
            $blockService->spacer('30px'),
            $blockService->footer('{app_name}'),
        ];
    }

    /**
     * Get the block structure for the Email Verification email template.
     */
    private function getEmailVerificationBlocks(BlockService $blockService): array
    {
        return [
            $blockService->text('{site_icon_image}', 'center'),
            $blockService->spacer('10px'),
            $blockService->heading('Verify Your Email Address', 'h1', 'center', '#635bff', '28px'),
            $blockService->spacer('20px'),
            $blockService->text('Hello <strong>{full_name}</strong>,'),
            $blockService->spacer('10px'),
            $blockService->text('Please verify your email address to complete your registration and access all features of <strong>{app_name}</strong>.'),
            $blockService->spacer('10px'),
            $blockService->text('Click the button below to verify your email address:'),
            $blockService->spacer('20px'),
            $blockService->button('Verify Email Address', '{verification_url}', '#635bff'),
            $blockService->spacer('20px'),
            $blockService->quote('This verification link will expire in {expiry_time}. If the link expires, you can request a new verification email from your account settings.'),
            $blockService->spacer('20px'),
            $blockService->text('If the button above doesn\'t work, copy and paste this URL into your browser:', 'left', '#666666', '14px'),
            $blockService->text('{verification_url}', 'left', '#635bff', '13px'),
            $blockService->spacer('20px'),
            $blockService->divider(),
            $blockService->text('<strong>Why Verify Your Email?</strong>', 'left', '#333333'),
            $blockService->listBlock([
                'Secure your account with verified contact information',
                'Receive important notifications and updates',
                'Access all features including module submission',
                'Recover your account if you forget your password',
            ]),
            $blockService->spacer('20px'),
            $blockService->text('If you did not create an account with us, you can safely ignore this email.', 'left', '#666666', '14px'),
            $blockService->spacer('30px'),
            $blockService->footer('{app_name}'),
        ];
    }
};
