<?php

declare(strict_types=1);

namespace App\Ai\Data;

/**
 * Represents the result of an AI action execution.
 */
readonly class AiResult
{
    public const STATUS_SUCCESS = 'success';

    public const STATUS_PARTIAL = 'partial';

    public const STATUS_FAILED = 'failed';

    public function __construct(
        public string $status,
        public string $message,
        public array $data = [],
        public array $actions = [],
        public array $completedSteps = [],
    ) {
    }

    public static function success(
        string $message,
        array $data = [],
        array $actions = [],
        array $completedSteps = [],
    ): self {
        return new self(
            status: self::STATUS_SUCCESS,
            message: $message,
            data: $data,
            actions: $actions,
            completedSteps: $completedSteps,
        );
    }

    public static function partial(
        string $message,
        array $data = [],
        array $actions = [],
        array $completedSteps = [],
    ): self {
        return new self(
            status: self::STATUS_PARTIAL,
            message: $message,
            data: $data,
            actions: $actions,
            completedSteps: $completedSteps,
        );
    }

    public static function failed(string $message, array $data = []): self
    {
        return new self(
            status: self::STATUS_FAILED,
            message: $message,
            data: $data,
        );
    }

    public function isSuccess(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'message' => $this->message,
            'data' => $this->data,
            'actions' => $this->actions,
            'completed_steps' => $this->completedSteps,
        ];
    }
}
