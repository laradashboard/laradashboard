<?php

declare(strict_types=1);

namespace App\Ai\Data;

/**
 * Represents an AI execution plan.
 */
readonly class AiPlan
{
    public function __construct(
        public AiIntent $intent,
        public array $steps = [],
        public string $summary = '',
    ) {
    }

    public static function fromArray(array $data): self
    {
        $intent = AiIntent::fromArray($data['intent'] ?? []);
        $steps = [];

        foreach (($data['steps'] ?? []) as $index => $stepData) {
            $steps[] = AiStep::fromArray($stepData, $index);
        }

        return new self(
            intent: $intent,
            steps: $steps,
            summary: $data['summary'] ?? '',
        );
    }

    public function toArray(): array
    {
        return [
            'intent' => $this->intent->toArray(),
            'steps' => array_map(fn (AiStep $step) => $step->toArray(), $this->steps),
            'summary' => $this->summary,
        ];
    }

    public function stepCount(): int
    {
        return count($this->steps);
    }
}
