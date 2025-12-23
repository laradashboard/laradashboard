<?php

declare(strict_types=1);

namespace App\Ai\Registry;

use App\Ai\Contracts\AiActionInterface;
use InvalidArgumentException;

/**
 * Registry for AI actions.
 */
class ActionRegistry
{
    /**
     * @var array<string, class-string<AiActionInterface>>
     */
    private static array $actions = [];

    public static function register(string $actionClass): void
    {
        if (! is_subclass_of($actionClass, AiActionInterface::class)) {
            throw new InvalidArgumentException(
                "Action class {$actionClass} must implement AiActionInterface"
            );
        }

        self::$actions[$actionClass::name()] = $actionClass;
    }

    public static function registerMany(array $actionClasses): void
    {
        foreach ($actionClasses as $actionClass) {
            self::register($actionClass);
        }
    }

    public static function resolve(string $name): ?AiActionInterface
    {
        $actionClass = self::$actions[$name] ?? null;

        if ($actionClass === null) {
            return null;
        }

        return app($actionClass);
    }

    public static function has(string $name): bool
    {
        return isset(self::$actions[$name]);
    }

    public static function all(): array
    {
        return self::$actions;
    }

    public static function getActionsForAi(): array
    {
        $result = [];

        foreach (self::$actions as $name => $actionClass) {
            $result[] = [
                'name' => $name,
                'description' => $actionClass::description(),
                'schema' => $actionClass::payloadSchema(),
                'permission' => $actionClass::permission(),
            ];
        }

        return $result;
    }

    public static function getActionsForUser(?object $user = null): array
    {
        $user ??= auth()->user();
        $result = [];

        foreach (self::$actions as $name => $actionClass) {
            $permission = $actionClass::permission();

            if ($permission === null || ($user && $user->can($permission))) {
                $result[] = [
                    'name' => $name,
                    'description' => $actionClass::description(),
                    'schema' => $actionClass::payloadSchema(),
                ];
            }
        }

        return $result;
    }

    public static function clear(): void
    {
        self::$actions = [];
    }
}
