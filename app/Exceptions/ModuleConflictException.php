<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class ModuleConflictException extends Exception
{
    /**
     * Create a new module conflict exception.
     *
     * @param array<string, mixed> $currentModule The existing module info
     * @param array<string, mixed> $uploadedModule The uploaded module info
     * @param string $tempPath Temporary path where uploaded module was extracted
     */
    public function __construct(
        string $message,
        public readonly array $currentModule,
        public readonly array $uploadedModule,
        public readonly string $tempPath,
    ) {
        parent::__construct($message);
    }

    /**
     * Get the comparison data for the conflict.
     *
     * @return array<string, mixed>
     */
    public function getComparisonData(): array
    {
        return [
            'current' => $this->currentModule,
            'uploaded' => $this->uploadedModule,
            'temp_path' => $this->tempPath,
        ];
    }
}
