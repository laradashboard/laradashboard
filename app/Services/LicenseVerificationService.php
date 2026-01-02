<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Setting;

/**
 * Service for managing locally stored module licenses.
 *
 * Note: License activation/deactivation is handled by JavaScript calling
 * the marketplace API directly. This service only manages local storage.
 */
class LicenseVerificationService
{
    /**
     * Get stored license for a module.
     */
    public function getStoredLicense(string $moduleSlug): ?array
    {
        $licenses = $this->getAllStoredLicenses();

        return $licenses[$moduleSlug] ?? null;
    }

    /**
     * Get all stored licenses.
     */
    public function getAllStoredLicenses(): array
    {
        $setting = Setting::where('option_name', 'module_licenses')->first();

        if (! $setting) {
            return [];
        }

        return json_decode($setting->option_value, true) ?? [];
    }

    /**
     * Store a license locally (called after successful marketplace activation).
     */
    public function storeLicenseLocally(string $moduleSlug, string $licenseKey, ?string $moduleName = null): void
    {
        $licenses = $this->getAllStoredLicenses();

        $licenses[$moduleSlug] = [
            'license_key' => $licenseKey,
            'module_name' => $moduleName ?? $moduleSlug,
            'activated_at' => now()->toIso8601String(),
            'domain' => $this->getCurrentDomain(),
        ];

        Setting::updateOrCreate(
            ['option_name' => 'module_licenses'],
            ['option_value' => json_encode($licenses)]
        );
    }

    /**
     * Remove a license locally (called after successful marketplace deactivation).
     */
    public function removeLicenseLocally(string $moduleSlug): void
    {
        $licenses = $this->getAllStoredLicenses();

        unset($licenses[$moduleSlug]);

        Setting::updateOrCreate(
            ['option_name' => 'module_licenses'],
            ['option_value' => json_encode($licenses)]
        );
    }

    /**
     * Get the current domain.
     */
    protected function getCurrentDomain(): string
    {
        return parse_url(config('app.url'), PHP_URL_HOST) ?? 'localhost';
    }
}
