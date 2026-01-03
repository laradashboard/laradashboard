<?php

declare(strict_types=1);

namespace App\Services\Charts;

use Carbon\Carbon;

abstract class ChartService
{
    protected function getDateRange(string $period): array
    {
        switch ($period) {
            case 'last_7_days':
                return [Carbon::now()->subDays(7), Carbon::now()];
            case 'last_30_days':
                return [Carbon::now()->subDays(30), Carbon::now()];
            case 'this_month':
                return [Carbon::now()->startOfMonth(), Carbon::now()];
            case 'last_year':
                return [Carbon::now()->subYear()->startOfYear(), Carbon::now()->subYear()->endOfYear()];
            case 'last_6_months':
                return [Carbon::now()->subMonths(6)->startOfMonth(), Carbon::now()];
            case 'last_12_months':
                return [Carbon::now()->subMonths(12)->startOfMonth(), Carbon::now()];
            case 'this_year':
            default:
                return [Carbon::now()->startOfYear(), Carbon::now()];
        }
    }

    protected function generateLabels(Carbon $startDate, Carbon $endDate, string $format, string $intervalMethod): \Illuminate\Support\Collection
    {
        $labels = collect();
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $labels->push($currentDate->format($format));
            $currentDate->$intervalMethod();
        }

        return $labels;
    }
}
