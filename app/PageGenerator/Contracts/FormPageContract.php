<?php

declare(strict_types=1);

namespace App\PageGenerator\Contracts;

use Illuminate\Database\Eloquent\Model;

interface FormPageContract extends PageContract
{
    public function getFields(): array;

    public function getFormAction(): string;

    public function getFormMethod(): string;

    public function getModel(): ?Model;

    public function getValidationRules(): array;

    public function getSubmitButtonText(): string;

    public function getCancelButtonText(): string;

    public function getCancelRoute(): string;

    public function showCancelButton(): bool;

    public function isAjaxForm(): bool;

    public function getFormId(): string;

    public function getFormClasses(): string;

    public function getSections(): array;

    public function beforeSave(array $data): array;

    public function afterSave(Model $model): void;
}
