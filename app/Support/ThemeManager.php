<?php

declare(strict_types=1);

namespace App\Support;

class ThemeManager
{
    /**
     * Get the active theme alias.
     */
    public function active(): ?string
    {
        return config('settings.active_theme');
    }

    /**
     * Get a theme-related setting value.
     */
    public function setting(string $key, mixed $default = null): mixed
    {
        return config('settings.' . $key, $default);
    }

    /**
     * Get the layout view path for the active theme.
     */
    public function layout(): string
    {
        return $this->active() . '::layouts.app';
    }

    /**
     * Get the view prefix for the active theme.
     */
    public function viewPrefix(): string
    {
        return $this->active() . '::';
    }

    /**
     * Check if a given theme alias is the active theme.
     */
    public function isActive(string $alias): bool
    {
        return $this->active() === $alias;
    }

    /**
     * Register default settings values, only setting them if not already configured.
     */
    public function registerDefaults(array $defaults): void
    {
        foreach ($defaults as $key => $value) {
            if (config('settings.' . $key) === null) {
                config(['settings.' . $key => $value]);
            }
        }
    }
}
