<?php

declare(strict_types=1);

namespace App\Ai\Registry;

use App\Ai\Contracts\AiCapabilityInterface;
use InvalidArgumentException;

/**
 * Registry for AI capabilities.
 */
class CapabilityRegistry
{
    /**
     * @var array<string, AiCapabilityInterface>
     */
    private static array $capabilities = [];

    public static function register(AiCapabilityInterface $capability): void
    {
        self::$capabilities[$capability->name()] = $capability;

        if ($capability->isEnabled()) {
            ActionRegistry::registerMany($capability->actions());
        }
    }

    public static function registerClass(string $capabilityClass): void
    {
        if (! is_subclass_of($capabilityClass, AiCapabilityInterface::class)) {
            throw new InvalidArgumentException(
                "Capability class {$capabilityClass} must implement AiCapabilityInterface"
            );
        }

        self::register(app($capabilityClass));
    }

    public static function all(): array
    {
        return self::$capabilities;
    }

    public static function enabled(): array
    {
        return array_filter(
            self::$capabilities,
            fn (AiCapabilityInterface $cap) => $cap->isEnabled()
        );
    }

    public static function getCapabilitiesForAi(): array
    {
        $result = [];

        foreach (self::enabled() as $capability) {
            $actions = [];
            foreach ($capability->actions() as $actionClass) {
                $actions[] = $actionClass::name();
            }

            $result[] = [
                'name' => $capability->name(),
                'description' => $capability->description(),
                'actions' => $actions,
            ];
        }

        return $result;
    }

    public static function clear(): void
    {
        self::$capabilities = [];
    }
}
