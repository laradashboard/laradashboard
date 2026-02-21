<?php

declare(strict_types=1);

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $settings = [
            Setting::SITE_TAGLINE => '',
            Setting::SOCIAL_LINKS => '[]',
            Setting::CONTACT_EMAIL => '',
            Setting::CONTACT_PHONE => '',
            Setting::CONTACT_ADDRESS => '',
            Setting::COPYRIGHT_TEXT => '',
            Setting::DEFAULT_HEADER_TEMPLATE => '',
            Setting::DEFAULT_FOOTER_TEMPLATE => '',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['option_name' => $key],
                ['option_value' => $value, 'autoload' => true]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $keys = [
            Setting::SITE_TAGLINE,
            Setting::SOCIAL_LINKS,
            Setting::CONTACT_EMAIL,
            Setting::CONTACT_PHONE,
            Setting::CONTACT_ADDRESS,
            Setting::COPYRIGHT_TEXT,
            Setting::DEFAULT_HEADER_TEMPLATE,
            Setting::DEFAULT_FOOTER_TEMPLATE,
        ];

        Setting::whereIn('option_name', $keys)->delete();
    }
};
