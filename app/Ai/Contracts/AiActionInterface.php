<?php

declare(strict_types=1);

namespace App\Ai\Contracts;

use App\Ai\Data\AiResult;

/**
 * Contract for AI-executable actions.
 *
 * Modules implement this interface to register actions
 * that can be executed by the AI command system.
 */
interface AiActionInterface
{
    /**
     * Unique identifier for this action (e.g., 'posts.create').
     */
    public static function name(): string;

    /**
     * Human-readable description for the AI to understand what this action does.
     */
    public static function description(): string;

    /**
     * JSON schema for the expected payload parameters.
     *
     * @return array<string, mixed>
     */
    public static function payloadSchema(): array;

    /**
     * Execute the action with the given payload.
     *
     * @param  array<string, mixed>  $payload
     */
    public function handle(array $payload): AiResult;

    /**
     * Permission required to execute this action.
     */
    public static function permission(): ?string;
}
