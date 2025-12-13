<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\BulkDeleteException;
use App\Exceptions\BulkUpdateException;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

abstract class BaseService
{
    abstract protected function getModelClass(): string;

    public function create(array $data): ?Model
    {
        try {
            $model = $this->getModelClass();
            return $model::create($data);
        } catch (Exception $e) {
            Log::error('Error creating record: ' . $e->getMessage());
            return null;
        }
    }

    public function update(int $id, array $data): ?Model
    {
        try {
            $record = $this->findById($id);

            if (! $record) {
                return null;
            }

            $record->update($data);
            return $record->fresh();
        } catch (Exception $e) {
            Log::error("Error updating record {$id}: " . $e->getMessage());
            return null;
        }
    }

    public function delete(int $id): bool
    {
        try {
            $record = $this->findById($id);

            if (! $record) {
                return false;
            }

            return $record->delete();
        } catch (Exception $e) {
            Log::error("Error deleting record {$id}: " . $e->getMessage());
            return false;
        }
    }

    public function findById(int $id): ?Model
    {
        $model = $this->getModelClass();
        return $model::find($id);
    }

    public function getAll(array $with = []): Collection
    {
        $model = $this->getModelClass();
        $query = $model::query();

        if (! empty($with)) {
            $query->with($with);
        }

        return $query->get();
    }

    public function getPaginated(int $perPage = 15, array $with = []): LengthAwarePaginator
    {
        $model = $this->getModelClass();
        $query = $model::query();

        if (! empty($with)) {
            $query->with($with);
        }

        return $query->paginate($perPage);
    }

    public function bulkUpdate(array $ids, array $data): bool
    {
        try {
            $model = $this->getModelClass();
            $model::whereIn('id', $ids)->update($data);
            return true;
        } catch (BulkUpdateException $e) {
            Log::error('Error bulk updating records: ' . $e->getMessage());
            return false;
        }
    }

    public function bulkDelete(array $ids): bool
    {
        try {
            $model = $this->getModelClass();
            $model::whereIn('id', $ids)->delete();
            return true;
        } catch (BulkDeleteException $e) {
            Log::error('Error bulk deleting records: ' . $e->getMessage());
            return false;
        }
    }
}
