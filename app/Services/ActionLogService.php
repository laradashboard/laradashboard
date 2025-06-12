<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ActionLog;
use Illuminate\Pagination\LengthAwarePaginator;

class ActionLogService
{
    public function getActionLogs(): LengthAwarePaginator
    {
        $query = ActionLog::query();
        $search = request()->input('search');

        if ($search) {
            $query->where('type', 'like', "%{$search}%")
                ->orWhere('title', 'like', "%{$search}%");
        }

        $type = request()->input('type');
        if ($type) {
            $query->where('type', $type);
        }

        $dateFrom = request()->input('date_from');
        $dateTo = request()->input('date_to');
        if ($dateFrom) {
            $query->where('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->where('created_at', '<=', $dateTo);
        }

        return $query->latest()->paginate(20);
    }
}
