<?php

declare(strict_types=1);

namespace App\Services\Charts;

use App\Models\Post;
use Carbon\Carbon;

class PostChartService
{
    /**
     * Get post statistics for the chart
     *
     * @param  string  $period  The time period for the chart (last_6_months, last_12_months, this_year, etc.)
     */
    public function getPostActivityData(string $period = 'last_6_months'): array
    {
        // Determine date range based on period
        $dateRange = $this->getDateRangeFromPeriod($period);
        $startDate = $dateRange['start'];
        $endDate = $dateRange['end'];
        $interval = $dateRange['interval'];

        // Initialize arrays for chart data
        $labels = [];
        $publishedData = [];
        $draftData = [];

        // Generate data points based on interval
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            // Format label based on interval
            if ($interval === 'month') {
                $labels[] = $currentDate->format('M Y');
                $nextDate = $currentDate->copy()->addMonth();

                // Count published posts for this month
                $publishedCount = Post::where('status', 'publish')
                    ->whereYear('created_at', $currentDate->year)
                    ->whereMonth('created_at', $currentDate->month)
                    ->count();

                // Count draft posts for this month
                $draftCount = Post::where('status', 'draft')
                    ->whereYear('created_at', $currentDate->year)
                    ->whereMonth('created_at', $currentDate->month)
                    ->count();
            } elseif ($interval === 'week') {
                $weekEnd = $currentDate->copy()->addDays(6);
                $labels[] = $currentDate->format('d M').' - '.$weekEnd->format('d M');
                $nextDate = $currentDate->copy()->addWeek();

                // Count published posts for this week
                $publishedCount = Post::where('status', 'publish')
                    ->whereBetween('created_at', [
                        $currentDate->startOfDay()->toDateTimeString(),
                        $weekEnd->endOfDay()->toDateTimeString(),
                    ])
                    ->count();

                // Count draft posts for this week
                $draftCount = Post::where('status', 'draft')
                    ->whereBetween('created_at', [
                        $currentDate->startOfDay()->toDateTimeString(),
                        $weekEnd->endOfDay()->toDateTimeString(),
                    ])
                    ->count();
            } else { // day
                $labels[] = $currentDate->format('d M');
                $nextDate = $currentDate->copy()->addDay();

                // Count published posts for this day
                $publishedCount = Post::where('status', 'publish')
                    ->whereDate('created_at', $currentDate->toDateString())
                    ->count();

                // Count draft posts for this day
                $draftCount = Post::where('status', 'draft')
                    ->whereDate('created_at', $currentDate->toDateString())
                    ->count();
            }

            $publishedData[] = $publishedCount;
            $draftData[] = $draftCount;

            $currentDate = $nextDate;
        }

        return [
            'labels' => $labels,
            'published' => $publishedData,
            'draft' => $draftData,
        ];
    }

    /**
     * Get date range based on period string
     */
    private function getDateRangeFromPeriod(string $period): array
    {
        $now = Carbon::now();

        switch ($period) {
            case 'last_7_days':
                return [
                    'start' => $now->copy()->subDays(6)->startOfDay(),
                    'end' => $now->copy()->endOfDay(),
                    'interval' => 'day',
                ];
            case 'last_30_days':
                return [
                    'start' => $now->copy()->subDays(29)->startOfDay(),
                    'end' => $now->copy()->endOfDay(),
                    'interval' => 'day',
                ];
            case 'last_3_months':
                return [
                    'start' => $now->copy()->subMonths(2)->startOfMonth(),
                    'end' => $now->copy()->endOfMonth(),
                    'interval' => 'month',
                ];
            case 'last_12_months':
                return [
                    'start' => $now->copy()->subMonths(11)->startOfMonth(),
                    'end' => $now->copy()->endOfMonth(),
                    'interval' => 'month',
                ];
            case 'this_year':
                return [
                    'start' => $now->copy()->startOfYear(),
                    'end' => $now->copy()->endOfYear(),
                    'interval' => 'month',
                ];
            case 'last_year':
                return [
                    'start' => $now->copy()->subYear()->startOfYear(),
                    'end' => $now->copy()->subYear()->endOfYear(),
                    'interval' => 'month',
                ];
            case 'last_6_months':
            default:
                return [
                    'start' => $now->copy()->subMonths(5)->startOfMonth(),
                    'end' => $now->copy()->endOfMonth(),
                    'interval' => 'month',
                ];
        }
    }
}
