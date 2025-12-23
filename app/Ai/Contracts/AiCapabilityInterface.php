<?php

declare(strict_types=1);

namespace App\Ai\Contracts;

/**
 * Contract for AI capabilities (grouped actions).
 */
interface AiCapabilityInterface
{
    public function name(): string;

    public function description(): string;

    /**
     * @return array<class-string<AiActionInterface>>
     */
    public function actions(): array;

    public function isEnabled(): bool;
}
