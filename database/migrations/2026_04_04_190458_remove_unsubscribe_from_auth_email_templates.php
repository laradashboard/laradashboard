<?php

declare(strict_types=1);

use App\Enums\TemplateType;
use App\Services\Builder\BlockService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    /**
     * Remove unsubscribe links from authentication email templates.
     * Auth emails (forgot password, welcome, verification, account created) are transactional
     * and should not include unsubscribe links meant for CRM/promotional emails.
     */
    public function up(): void
    {
        $templates = DB::table('email_templates')
            ->where('type', TemplateType::AUTHENTICATION->value)
            ->get();

        $blockService = app(BlockService::class);

        foreach ($templates as $template) {
            $designJson = json_decode($template->design_json, true);

            if (! $designJson || empty($designJson['blocks'])) {
                continue;
            }

            $changed = false;

            foreach ($designJson['blocks'] as &$block) {
                if (($block['type'] ?? '') === 'footer' && ! empty($block['props']['unsubscribeText'])) {
                    $block['props']['unsubscribeText'] = '';
                    $block['props']['unsubscribeUrl'] = '';
                    $changed = true;
                }
            }

            if ($changed) {
                DB::table('email_templates')
                    ->where('id', $template->id)
                    ->update([
                        'design_json' => json_encode($designJson),
                        'body_html' => $blockService->generateEmailHtml(
                            $designJson['blocks'],
                            $designJson['canvasSettings'] ?? $blockService->getDefaultCanvasSettings()
                        ),
                        'updated_at' => now(),
                    ]);
            }
        }
    }

    public function down(): void
    {
        // Re-add unsubscribe links to authentication templates
        $templates = DB::table('email_templates')
            ->where('type', TemplateType::AUTHENTICATION->value)
            ->get();

        $blockService = app(BlockService::class);

        foreach ($templates as $template) {
            $designJson = json_decode($template->design_json, true);

            if (! $designJson || empty($designJson['blocks'])) {
                continue;
            }

            $changed = false;

            foreach ($designJson['blocks'] as &$block) {
                if (($block['type'] ?? '') === 'footer' && empty($block['props']['unsubscribeText'])) {
                    $block['props']['unsubscribeText'] = 'Unsubscribe from these emails';
                    $block['props']['unsubscribeUrl'] = '#unsubscribe';
                    $changed = true;
                }
            }

            if ($changed) {
                DB::table('email_templates')
                    ->where('id', $template->id)
                    ->update([
                        'design_json' => json_encode($designJson),
                        'body_html' => $blockService->generateEmailHtml(
                            $designJson['blocks'],
                            $designJson['canvasSettings'] ?? $blockService->getDefaultCanvasSettings()
                        ),
                        'updated_at' => now(),
                    ]);
            }
        }
    }
};
