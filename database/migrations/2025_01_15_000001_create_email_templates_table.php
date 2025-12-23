<?php

declare(strict_types=1);

use App\Enums\TemplateType;
use App\Services\Builder\BlockService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class () extends Migration {
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

        $blocks = $this->getForgotPasswordBlocks($blockService);
        $canvasSettings = $blockService->getDefaultCanvasSettings();
        $designJson = [
            'blocks' => $blocks,
            'canvasSettings' => $canvasSettings,
            'version' => 1,
        ];

        // Temporarily disable foreign key checks for seeding (no users exist yet during migration)
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        DB::table('email_templates')->insert([
            'uuid' => (string) Str::uuid(),
            'name' => 'Forgot Password',
            'subject' => 'Reset Your Password - {app_name}',
            'body_html' => $blockService->generateEmailHtml($blocks, $canvasSettings),
            'design_json' => json_encode($designJson),
            'type' => TemplateType::AUTHENTICATION->value,
            'description' => 'Password reset email with security tips',
            'is_active' => true,
            'is_default' => false,
            'is_deleteable' => false,
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
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
};
