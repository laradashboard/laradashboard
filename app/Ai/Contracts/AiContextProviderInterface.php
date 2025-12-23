<?php

declare(strict_types=1);

namespace App\Ai\Contracts;

/**
 * Contract for providing context to AI.
 */
interface AiContextProviderInterface
{
    public function key(): string;

    /**
     * @return array<string, mixed>
     */
    public function context(): array;
}
