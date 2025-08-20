<?php

declare(strict_types=1);

namespace App\PageGenerator\Contracts;

interface PageContract
{
    public function render(): string;

    public function getTitle(): string;

    public function getBreadcrumbs(): array;

    public function getViewData(): array;

    public function getView(): ?string;

    public function authorize(): bool;
}
