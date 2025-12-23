<?php

declare(strict_types=1);

namespace App\Ai\Data;

/**
 * Represents AI-extracted intent from user command.
 */
readonly class AiIntent
{
    public function __construct(
        public string $intent,
        public ?string $module,
        public string $goal,
        public array $parameters = [],
        public float $confidence = 1.0,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            intent: $data['intent'] ?? 'unknown',
            module: $data['module'] ?? null,
            goal: $data['goal'] ?? '',
            parameters: $data['parameters'] ?? [],
            confidence: (float) ($data['confidence'] ?? 1.0),
        );
    }

    public function toArray(): array
    {
        return [
            'intent' => $this->intent,
            'module' => $this->module,
            'goal' => $this->goal,
            'parameters' => $this->parameters,
            'confidence' => $this->confidence,
        ];
    }
}
