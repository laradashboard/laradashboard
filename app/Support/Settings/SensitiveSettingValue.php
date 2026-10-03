<?php

declare(strict_types=1);

namespace App\Support\Settings;

use App\Enums\Hooks\SettingFilterHook;
use App\Support\Facades\Hook;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

/**
 * Central rules for sensitive setting values: masking in API responses,
 * encryption at rest, and skipping unchanged password fields on update.
 */
final class SensitiveSettingValue
{
    public const MASK = '••••••••';

    /**
     * Settings that must never appear in settings API list/show responses.
     *
     * @var list<string>
     */
    private const HIDDEN_FROM_API = [
        'module_licenses',
    ];

    /**
     * @var list<string>
     */
    private const SENSITIVE_SUFFIXES = [
        '_key',
        '_secret',
        '_password',
        '_token',
    ];

    public static function isHiddenFromApi(string $optionName): bool
    {
        $hidden = Hook::applyFilters(SettingFilterHook::SETTINGS_HIDDEN_FROM_API_KEYS, self::HIDDEN_FROM_API);

        return in_array($optionName, $hidden, true);
    }

    public static function isSensitive(string $optionName): bool
    {
        if (self::isHiddenFromApi($optionName)) {
            return true;
        }

        $extraKeys = Hook::applyFilters(SettingFilterHook::SETTINGS_SENSITIVE_KEYS, []);

        if (in_array($optionName, $extraKeys, true)) {
            return true;
        }

        $lower = strtolower($optionName);

        foreach (self::SENSITIVE_SUFFIXES as $suffix) {
            if (str_ends_with($lower, $suffix)) {
                return true;
            }
        }

        return false;
    }

    public static function isMaskedSubmission(mixed $value): bool
    {
        if (! is_scalar($value)) {
            return false;
        }

        $string = trim((string) $value);

        if ($string === '') {
            return false;
        }

        if ($string === self::MASK) {
            return true;
        }

        return (bool) preg_match('/^[•*]+$/u', $string);
    }

    /**
     * Value exposed in SettingResource / JSON API (never raw secrets).
     */
    public static function exposeForApi(string $optionName, mixed $storedValue): string
    {
        if (! self::isSensitive($optionName)) {
            return (string) ($storedValue ?? '');
        }

        $plaintext = self::resolveStoredValue($optionName, $storedValue);

        if ($plaintext === '') {
            return '';
        }

        return self::MASK;
    }

    /**
     * @return string|null Null means "do not persist this submission" (unchanged secret field).
     */
    public static function prepareForStorage(string $optionName, mixed $value): ?string
    {
        if (! is_scalar($value) && $value !== null) {
            $value = json_encode($value);
        }

        $string = (string) ($value ?? '');

        if (self::isSensitive($optionName) && self::isMaskedSubmission($string)) {
            return null;
        }

        if (! self::isSensitive($optionName)) {
            return $string;
        }

        if ($string === '') {
            return '';
        }

        return Crypt::encryptString($string);
    }

    public static function resolveStoredValue(string $optionName, mixed $storedValue): string
    {
        $string = (string) ($storedValue ?? '');

        if ($string === '' || ! self::isSensitive($optionName)) {
            return $string;
        }

        try {
            return Crypt::decryptString($string);
        } catch (DecryptException) {
            return $string;
        }
    }
}
