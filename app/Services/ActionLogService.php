<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ActionLog;
use Illuminate\Pagination\LengthAwarePaginator;

class ActionLogService
{
    public function getPaginatedActionLogs(?array $filters = null, ?int $perPage = null): LengthAwarePaginator
    {
        $query = ActionLog::with('user');
        $search = $filters['search'] ?? request()->input('search');

        if ($search) {
            $query->where('type', 'like', "%{$search}%")
                ->orWhere('title', 'like', "%{$search}%");
        }

        $type = $filters['type'] ?? request()->input('type');
        if ($type) {
            $query->where('type', $type);
        }

        $dateFrom = $filters['date_from'] ?? request()->input('date_from');
        $dateTo = $filters['date_to'] ?? request()->input('date_to');
        if ($dateFrom) {
            $query->where('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->where('created_at', '<=', $dateTo);
        }

        return $query->latest()->paginate($perPage ?? 20);
    }

    /**
     * Get action log by ID
     */
    public function getActionLogById(int $id): ?ActionLog
    {
        return ActionLog::with('user')->find($id);
    }
}
