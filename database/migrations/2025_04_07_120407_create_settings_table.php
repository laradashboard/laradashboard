<?php

declare(strict_types=1);

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('option_name')->unique();
            $table->text('option_value');
            $table->boolean('autoload')->default(false);
            $table->timestamps();
        });

        $this->seedEssentialSettings();
    }

    /**
     * Seed essential default settings that the application needs to function.
     * These are the minimum required settings - additional settings can be
     * added via the SettingsSeeder for demo/development purposes.
     *
     * Note: INSTALLATION_COMPLETED is NOT seeded here - it's set by the
     * installation wizard when installation is complete.
     */
    private function seedEssentialSettings(): void
    {
        $timestamp = now();

        $settings = [
            // App name default
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

            // Security settings
            ['option_name' => 'hide_admin_url', 'option_value' => '0'],
        ];

        foreach ($settings as &$setting) {
            $setting['autoload'] = 1;
            $setting['created_at'] = $timestamp;
            $setting['updated_at'] = $timestamp;
        }

        DB::table('settings')->insert($settings);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
