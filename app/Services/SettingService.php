<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Setting;

class SettingService
{
    public function addSetting(string $optionName, mixed $optionValue, bool $autoload = false): Setting
    {
        return Setting::updateOrCreate(
            ['option_name' => $optionName],
            ['option_value' => $optionValue ?? '', 'autoload' => $autoload]
        );
    }

    public function updateSetting(string $optionName, mixed $optionValue, ?bool $autoload = null): bool
    {
        $setting = Setting::where('option_name', $optionName)->first();

        if ($setting) {
            $setting->update([
                'option_value' => $optionValue,
                'autoload' => $autoload ?? $setting->autoload,
            ]);
            return true;
        }

        return false;
    }

    public function deleteSetting(string $optionName): bool
    {
        return Setting::where('option_name', $optionName)->delete() > 0;
    }

    public function getSetting(string $optionName): mixed
    {
        return Setting::where('option_name', $optionName)->value('option_value');
    }

    public function getSettings(int|bool|null $autoload = true): array
    {
        if ($autoload === -1) {
            return Setting::all()->toArray();
        }

        return Setting::where('autoload', (bool) $autoload)->get()->toArray();
    }
}
