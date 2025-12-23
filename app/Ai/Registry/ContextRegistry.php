<?php

declare(strict_types=1);

namespace App\Ai\Registry;

use App\Ai\Contracts\AiContextProviderInterface;
use InvalidArgumentException;

/**
 * Registry for AI context providers.
 */
class ContextRegistry
{
    /**
     * @var array<string, AiContextProviderInterface>
     */
    private static array $providers = [];

    public static function register(AiContextProviderInterface $provider): void
    {
        self::$providers[$provider->key()] = $provider;
    }

    public static function registerClass(string $providerClass): void
    {
        if (! is_subclass_of($providerClass, AiContextProviderInterface::class)) {
            throw new InvalidArgumentException(
                "Provider class {$providerClass} must implement AiContextProviderInterface"
            );
        }

        self::register(app($providerClass));
    }

    public static function getAllContext(): array
    {
        $context = [];

        foreach (self::$providers as $key => $provider) {
            $context[$key] = $provider->context();
        }

        return $context;
    }

    public static function getContext(string $key): ?array
    {
        $provider = self::$providers[$key] ?? null;

        return $provider?->context();
    }

    public static function all(): array
    {
        return self::$providers;
    }

    public static function clear(): void
    {
        self::$providers = [];
    }
}
