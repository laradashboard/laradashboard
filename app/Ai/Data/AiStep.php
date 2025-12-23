<?php

declare(strict_types=1);

namespace App\Ai\Data;

/**
 * Represents a single step in an AI execution plan.
 */
readonly class AiStep
{
    public function __construct(
        public string $action,
        public array $payload = [],
        public string $description = '',
        public int $order = 0,
    ) {
    }

    public static function fromArray(array $data, int $order = 0): self
    {
        return new self(
            action: $data['action'] ?? '',
            payload: $data['payload'] ?? [],
            description: $data['description'] ?? '',
            order: $data['order'] ?? $order,
        );
    }

    public function toArray(): array
    {
        return [
            'action' => $this->action,
            'payload' => $this->payload,
            'description' => $this->description,
            'order' => $this->order,
        ];
    }
}
