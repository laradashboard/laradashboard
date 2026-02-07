<?php

declare(strict_types=1);

namespace App\Contracts;

/**
 * Result object returned by inbound email handlers.
 */
class InboundEmailHandlerResult
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $message = null,
        public readonly ?string $modelType = null,
        public readonly ?int $modelId = null,
    ) {
    }

    /**
     * Create a successful result.
     */
    public static function success(
        ?string $message = null,
        ?string $modelType = null,
        ?int $modelId = null,
    ): self {
        return new self(
            success: true,
            message: $message,
            modelType: $modelType,
            modelId: $modelId,
        );
    }

    /**
     * Create a failed result.
     */
    public static function failure(string $message): self
    {
        return new self(
            success: false,
            message: $message,
        );
    }

    /**
     * Create a skipped result (not an error, just not handled).
     */
    public static function skipped(string $message = 'Email not matched by handler'): self
    {
        return new self(
            success: false,
            message: $message,
        );
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function isFailure(): bool
    {
        return ! $this->success;
    }
}
