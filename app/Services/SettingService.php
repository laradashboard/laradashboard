<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Hooks\CommonFilterHook;
use App\Enums\Hooks\SettingActionHook;
use App\Models\Setting;
use App\Support\Facades\Hook;
use App\Support\Settings\SensitiveSettingValue;

class SettingService
{
    public array $excluded_settings = [];

    public function __construct()
    {
        $this->excluded_settings = Hook::applyFilters(CommonFilterHook::EXCLUDED_SETTING_KEYS, [
            '_token',
        ]);
    }

    public function addSetting(string $optionName, mixed $optionValue, bool $autoload = false): ?Setting
    {
        if (in_array($optionName, $this->excluded_settings)) {
            return null;
        }

        $storedValue = SensitiveSettingValue::prepareForStorage($optionName, $optionValue);

        if ($storedValue === null) {
            return Setting::where('option_name', $optionName)->first();
        }

        // Fire action before setting creation
        Hook::doAction(SettingActionHook::SETTING_CREATED_BEFORE, $optionName, $storedValue);

        $setting = Setting::updateOrCreate(
            ['option_name' => $optionName],
            ['option_value' => $storedValue, 'autoload' => $autoload]
        );

        // Fire action after setting creation
        Hook::doAction(SettingActionHook::SETTING_CREATED_AFTER, $setting);

        return $setting;
    }

    public function updateSetting(string $optionName, mixed $optionValue, ?bool $autoload = null): bool
    {
        if (in_array($optionName, $this->excluded_settings)) {
            return false;
        }

        $setting = Setting::where('option_name', $optionName)->first();

        if ($setting) {
            $oldValue = $setting->option_value;

            $storedValue = SensitiveSettingValue::prepareForStorage($optionName, $optionValue);

            if ($storedValue === null) {
                return true;
            }

            // Fire action before setting update
            Hook::doAction(SettingActionHook::SETTING_UPDATED_BEFORE, $setting, $storedValue);

            $setting->update([
                'option_value' => $storedValue,
                'autoload' => $autoload ?? $setting->autoload,
            ]);

            // Fire action after setting update
            Hook::doAction(SettingActionHook::SETTING_UPDATED_AFTER, $setting, $oldValue);

            return true;
        }

        return false;
    }

    public function deleteSetting(string $optionName): bool
    {
        $setting = Setting::where('option_name', $optionName)->first();

        if (! $setting) {
            return false;
        }

        // Fire action before setting deletion
        Hook::doAction(SettingActionHook::SETTING_DELETED_BEFORE, $setting);

        $deleted = $setting->delete();

        // Fire action after setting deletion
        Hook::doAction(SettingActionHook::SETTING_DELETED_AFTER, $optionName);

        return (bool) $deleted;
    }

    public function getSetting(string $optionName): mixed
    {
        try {
            $stored = Setting::where('option_name', $optionName)->value('option_value');

            if ($stored === null) {
                return null;
            }

            return SensitiveSettingValue::resolveStoredValue($optionName, $stored);
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle case when settings table doesn't exist (e.g., during testing)
            return null;
        }
    }

    public function getSettings(int|bool|null $autoload = true): array
    {
        if ($autoload === -1) {
            return Setting::all()->toArray();
        }

        return Setting::where('autoload', (bool) $autoload)->get()->toArray();
    }

    /**
     * Get all settings with optional group filter
     */
    public function getAllSettings(?string $search = null, $autoload = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = Setting::query();

        if ($search) {
            $query->where('option_name', 'like', "%{$search}%");
        }

        if ($autoload !== null) {
            $query->where('autoload', (bool) $autoload);
        }

        return $query->get();
    }

    /**
     * Settings safe to expose via the HTTP API (excludes internal secrets blob keys).
     */
    public function getAllSettingsForApi(?string $search = null, $autoload = null): \Illuminate\Database\Eloquent\Collection
    {
        return $this->getAllSettings($search, $autoload)
            ->reject(fn (Setting $setting) => SensitiveSettingValue::isHiddenFromApi($setting->option_name))
            ->values();
    }

    /**
     * Update or create a setting
     */
    public function updateOrCreateSetting(string $key, mixed $value): ?Setting
    {
        if (SensitiveSettingValue::isHiddenFromApi($key)) {
            return null;
        }

        $storedValue = SensitiveSettingValue::prepareForStorage($key, $value);

        if ($storedValue === null) {
            return Setting::where('option_name', $key)->first();
        }

        return Setting::updateOrCreate(
            ['option_name' => $key],
            ['option_value' => $storedValue]
        );
    }

    /**
     * Get setting by key
     */
    public function getSettingByKey(string $key): ?Setting
    {
        return Setting::where('option_name', $key)->first();
    }
}
