<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('settings')->insert([
            // Site title
            ['option_name' => 'app_name', 'option_value' => 'Lara Dashboard'],

            // theme colors.
            ['option_name' => 'theme_primary_color', 'option_value' => '#635bff'],
            ['option_name' => 'theme_secondary_color', 'option_value' => '#1f2937'],

            // Sidebar colors
            ['option_name' => 'sidebar_bg_lite', 'option_value' => '#171f2e'],
            ['option_name' => 'sidebar_bg_dark', 'option_value' => '#171f2e'],

            ['option_name' => 'sidebar_text_lite', 'option_value' => '#ffffff'],
            ['option_name' => 'sidebar_text_dark', 'option_value' => '#ffffff'],

            // Navbar colors
            ['option_name' => 'navbar_bg_lite', 'option_value' => '#ffffff'],
            ['option_name' => 'navbar_bg_dark', 'option_value' => '#171f2e'],
            ['option_name' => 'navbar_text_lite', 'option_value' => '#374151'],
            ['option_name' => 'navbar_text_dark', 'option_value' => '#ffffff'],

            // Text colors
            ['option_name' => 'text_color_lite', 'option_value' => '#212529'],
            ['option_name' => 'text_color_dark', 'option_value' => '#f8f9fa'],

            // Site logo and icons
            // ['option_name' => 'site_logo_lite', 'option_value' => '/images/logo/lara-dashboard.png'],
            ['option_name' => 'site_logo_lite', 'option_value' => '/images/logo/lara-dashboard-dark.png'],
            ['option_name' => 'site_logo_dark', 'option_value' => '/images/logo/lara-dashboard-dark.png'],
            ['option_name' => 'site_icon', 'option_value' => '/images/logo/icon.png'],
            ['option_name' => 'site_favicon', 'option_value' => '/images/logo/icon.png'],

            // Additional default settings can be added here
            ['option_name' => 'default_pagination', 'option_value' => '10'],
        ]);
    }
}
