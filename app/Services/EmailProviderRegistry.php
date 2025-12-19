<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\EmailProviderInterface;

class EmailProviderRegistry extends BaseTypeRegistry
{
    /**
     * Storage for provider class mappings.
     *
     * @var array<string, class-string<EmailProviderInterface>>
     */
    protected static array $providerClasses = [];

    /**
     * Cache for provider instances.
     *
     * @var array<string, EmailProviderInterface>
     */
    protected static array $providerInstances = [];

    protected static function getFilterName(): string
    {
        return 'email_provider_values';
    }

    /**
     * Register an email provider with its class.
     *
     * @param  class-string<EmailProviderInterface>  $providerClass
     */
    public static function registerProvider(string $providerClass): void
    {
        $instance = app($providerClass);
        $key = $instance->getKey();

        static::$providerClasses[$key] = $providerClass;
        static::$providerInstances[$key] = $instance;

        static::register($key, [
            'label' => fn () => $instance->getName(),
            'icon' => fn () => $instance->getIcon(),
            'description' => fn () => $instance->getDescription(),
        ]);
    }

    /**
     * Get a provider instance by key.
     */
    public static function getProvider(string $key): ?EmailProviderInterface
    {
        if (isset(static::$providerInstances[$key])) {
            return static::$providerInstances[$key];
        }

        if (isset(static::$providerClasses[$key])) {
            static::$providerInstances[$key] = app(static::$providerClasses[$key]);

            return static::$providerInstances[$key];
        }

        return null;
    }

    /**
     * Get all registered providers.
     *
     * @return array<string, EmailProviderInterface>
     */
    public static function getProviders(): array
    {
        $providers = [];
        foreach (array_keys(static::$providerClasses) as $key) {
            $providers[$key] = static::getProvider($key);
        }

        return $providers;
    }

    /**
     * Get provider cards for the selector UI.
     *
     * @return array<int, array{key: string, name: string, icon: string, description: string}>
     */
    public static function getProviderCards(): array
    {
        $cards = [];
        foreach (static::getProviders() as $key => $provider) {
            $cards[] = [
                'key' => $key,
                'name' => $provider->getName(),
                'icon' => $provider->getIcon(),
                'description' => $provider->getDescription(),
            ];
        }

        return $cards;
    }

    /**
     * Get description for a provider.
     */
    public static function getDescription(string $type): ?string
    {
        $meta = static::getMeta($type);
        if (empty($meta['description'])) {
            return null;
        }
        if (is_callable($meta['description'])) {
            return (string) call_user_func($meta['description'], $type);
        }

        return (string) $meta['description'];
    }

    /**
     * Check if a provider is registered.
     */
    public static function hasProvider(string $key): bool
    {
        return isset(static::$providerClasses[$key]);
    }

    /**
     * Clear all registrations (useful for testing).
     */
    public static function clear(): void
    {
        parent::clear();
        static::$providerClasses = [];
        static::$providerInstances = [];
    }
}
