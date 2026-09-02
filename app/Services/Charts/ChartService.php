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
                // subDays(6)->startOfDay() yields exactly 7 daily buckets incl. today.
                return [Carbon::now()->subDays(6)->startOfDay(), Carbon::now()];
            case 'last_30_days':
                return [Carbon::now()->subDays(29)->startOfDay(), Carbon::now()];
            case 'this_month':
                return [Carbon::now()->startOfMonth(), Carbon::now()];
            case 'last_year':
                return [Carbon::now()->subYear()->startOfYear(), Carbon::now()->subYear()->endOfYear()];
            case 'last_6_months':
                // The default period of every admin chart — was silently falling
                // through to this_year, mislabelling all charts.
                return [Carbon::now()->subMonths(5)->startOfMonth(), Carbon::now()];
            case 'last_12_months':
                // subMonths(11) yields exactly 12 monthly buckets incl. the current one.
                return [Carbon::now()->subMonths(11)->startOfMonth(), Carbon::now()];
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
