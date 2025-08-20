<?php

declare(strict_types=1);

namespace App\PageGenerator\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ListPageContract extends PageContract
{
    public function getColumns(): array;

    public function getFilters(): array;

    public function getSortableColumns(): array;

    public function getSearchableColumns(): array;

    public function getBulkActions(): array;

    public function getActions(): array;

    public function getData(): LengthAwarePaginator|Collection;

    public function getPerPage(): int;

    public function showCheckboxes(): bool;

    public function showSearch(): bool;

    public function showFilters(): bool;

    public function showBulkActions(): bool;

    public function showPagination(): bool;

    public function showActions(): bool;

    public function getEmptyMessage(): string;
}
