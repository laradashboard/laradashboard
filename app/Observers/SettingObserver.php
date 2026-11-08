<?php

declare(strict_types=1);

namespace App\Observers;

use App\Concerns\HasActionLogTrait;
use App\Enums\ActionType;
use App\Models\Setting;
use App\Support\Settings\SensitiveSettingValue;

class SettingObserver
{
    use HasActionLogTrait;

    public function created(Setting $setting): void
    {
        $this->storeActionLog(ActionType::CREATED, self::logData($setting));
    }

    public function updated(Setting $setting): void
    {
        $this->storeActionLog(ActionType::UPDATED, self::logData($setting));
    }

    public function deleted(Setting $setting): void
    {
        $this->storeActionLog(ActionType::DELETED, self::logData($setting));
    }

    /**
     * What the action log keeps about a setting change. Secret values (API keys, passwords,
     * client secrets, tokens) are replaced with a mask: the log is readable with
     * actionlog.view alone, which must never reveal what only settings managers may see.
     *
     * @return array{setting: array{id: int|null, option_name: string, option_value: mixed, autoload: mixed}}
     */
    public static function logData(Setting $setting): array
    {
        $name = (string) $setting->option_name;
        $value = $setting->option_value;

        if (SensitiveSettingValue::isSensitive($name)) {
            $value = filled($value) ? SensitiveSettingValue::MASK : '';
        }

        return [
            'setting' => [
                'id' => $setting->getKey(),
                'option_name' => $name,
                'option_value' => $value,
                'autoload' => $setting->autoload,
            ],
        ];
    }
}
