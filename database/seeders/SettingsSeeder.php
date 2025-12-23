<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Essential settings are already seeded by the migration.
     * This seeder only adds additional settings that don't already exist,
     * including INSTALLATION_COMPLETED for demo/development purposes.
     */
    public function run(): void
    {
        $options = [
            ['option_name' => Setting::APP_NAME, 'option_value' => 'Lara Dashboard'],

            // Theme colors
            ['option_name' => Setting::THEME_PRIMARY_COLOR, 'option_value' => '#635bff'],
            ['option_name' => Setting::THEME_SECONDARY_COLOR, 'option_value' => '#1f2937'],

            // Sidebar colors
            ['option_name' => Setting::SIDEBAR_BG_LITE, 'option_value' => '#FFFFFF'],
            ['option_name' => Setting::SIDEBAR_BG_DARK, 'option_value' => '#171f2e'],
            ['option_name' => Setting::SIDEBAR_LI_HOVER_LITE, 'option_value' => '#E8E6FF'],
            ['option_name' => Setting::SIDEBAR_LI_HOVER_DARK, 'option_value' => '#E8E6FF'],
            ['option_name' => Setting::SIDEBAR_TEXT_LITE, 'option_value' => '#090909'],
            ['option_name' => Setting::SIDEBAR_TEXT_DARK, 'option_value' => '#ffffff'],

            // Navbar colors
            ['option_name' => Setting::NAVBAR_BG_LITE, 'option_value' => '#FFFFFF'],
            ['option_name' => Setting::NAVBAR_BG_DARK, 'option_value' => '#171f2e'],
            ['option_name' => Setting::NAVBAR_TEXT_LITE, 'option_value' => '#090909'],
            ['option_name' => Setting::NAVBAR_TEXT_DARK, 'option_value' => '#ffffff'],

            // Text colors
            ['option_name' => Setting::TEXT_COLOR_LITE, 'option_value' => '#212529'],
            ['option_name' => Setting::TEXT_COLOR_DARK, 'option_value' => '#f8f9fa'],

            // Site logo and icons
            ['option_name' => Setting::SITE_LOGO_LITE, 'option_value' => '/images/logo/lara-dashboard.png'],
            ['option_name' => Setting::SITE_LOGO_DARK, 'option_value' => '/images/logo/lara-dashboard-dark.png'],
            ['option_name' => Setting::SITE_ICON, 'option_value' => '/images/logo/icon.png'],
            ['option_name' => Setting::SITE_FAVICON, 'option_value' => '/images/logo/icon.png'],

            // Additional default settings
            ['option_name' => Setting::DEFAULT_PAGINATION, 'option_value' => '10'],
            ['option_name' => Setting::GOOGLE_TAG_MANAGER_SCRIPT, 'option_value' => ''],
            ['option_name' => Setting::GOOGLE_ANALYTICS_SCRIPT, 'option_value' => ''],

            // Custom CSS and JS
            ['option_name' => Setting::GLOBAL_CUSTOM_CSS, 'option_value' => ''],
            ['option_name' => Setting::GLOBAL_CUSTOM_JS, 'option_value' => ''],

            // AI Integration settings
            ['option_name' => Setting::AI_DEFAULT_PROVIDER, 'option_value' => 'openai'],
            ['option_name' => Setting::AI_OPENAI_API_KEY, 'option_value' => ''],
            ['option_name' => Setting::AI_CLAUDE_API_KEY, 'option_value' => ''],

            // Installation completed (only for seeder - not in migration)
            ['option_name' => Setting::INSTALLATION_COMPLETED, 'option_value' => '1'],
        ];

        // Get existing settings to avoid duplicates
        $existingSettings = DB::table('settings')->pluck('option_name')->toArray();

        // Filter out settings that already exist from migration
        $newSettings = array_filter($options, function ($option) use ($existingSettings) {
            return ! in_array($option['option_name'], $existingSettings);
        });

        if (empty($newSettings)) {
            $this->command->info('All settings already exist from migration.');

            return;
        }

        // Add timestamps
        $timestamp = now();
        foreach ($newSettings as &$option) {
            $option['autoload'] = 1;
            $option['created_at'] = $timestamp;
            $option['updated_at'] = $timestamp;
        }

        DB::table('settings')->insert(array_values($newSettings));

        $count = count($newSettings);
        $this->command->info("Created {$count} additional setting(s).");
    }
}
