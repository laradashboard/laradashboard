<?php

declare(strict_types=1);

namespace App\Enums\Builder;

/**
 * Builder Contexts
 *
 * Defines the different contexts where the builder can be used.
 * Each context may have different blocks available and different output adapters.
 */
enum BuilderContext: string
{
    case EMAIL = 'email';
    case PAGE = 'page';
    case CAMPAIGN = 'campaign';

    /**
     * Get the display label for the context
     */
    public function label(): string
    {
        return match ($this) {
            self::EMAIL => 'Email Template',
            self::PAGE => 'Page',
            self::CAMPAIGN => 'Campaign',
        };
    }

    /**
     * Get the adapter key for the context
     */
    public function adapter(): string
    {
        return match ($this) {
            self::EMAIL, self::CAMPAIGN => 'email',
            self::PAGE => 'page',
        };
    }

    /**
     * Check if the context supports a specific feature
     */
    public function supports(string $feature): bool
    {
        $features = match ($this) {
            self::EMAIL => ['inline-styles', 'tables', 'mso-conditionals', 'video-thumbnails'],
            self::PAGE => ['css-classes', 'native-video', 'responsive', 'sections'],
            self::CAMPAIGN => ['inline-styles', 'tables', 'mso-conditionals', 'video-thumbnails', 'personalization'],
        };

        return in_array($feature, $features);
    }

    /**
     * Get all contexts as an array
     */
    public static function toArray(): array
    {
        return array_map(fn ($case) => [
            'value' => $case->value,
            'label' => $case->label(),
            'adapter' => $case->adapter(),
        ], self::cases());
    }
}
