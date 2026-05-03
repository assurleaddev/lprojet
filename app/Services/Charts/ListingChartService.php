<?php

declare(strict_types=1);

namespace App\Services\Charts;

use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ListingChartService extends ChartService
{
    public function getListingData(string $period = 'last_6_months'): array
    {
        [$startDate, $endDate] = $this->getDateRange($period);

        $isLessThanMonth = $startDate->diffInMonths($endDate) < 1;

        $format = $isLessThanMonth ? 'd M Y' : 'M Y';
        $dbFormat = $isLessThanMonth ? 'Y-m-d' : 'Y-m';
        $intervalMethod = $isLessThanMonth ? 'addDay' : 'addMonth';
        $driver = DB::connection()->getDriverName();

        $dateTrunc = $isLessThanMonth
            ? 'DATE(created_at)'
            : ($driver === 'sqlite' ? "strftime('%Y-%m', created_at)" : "DATE_FORMAT(created_at, '%Y-%m')");

        $rows = Product::selectRaw("
                {$dateTrunc} as period,
                SUM(status = 'approved') as approved,
                SUM(status = 'pending')  as pending,
                SUM(status = 'sold')     as sold,
                COUNT(*)                 as total
            ")
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->keyBy('period');

        $labels = $this->generateLabels($startDate, $endDate, $format, $intervalMethod);

        $approved = [];
        $pending = [];
        $sold = [];

        foreach ($labels as $label) {
            $key = Carbon::createFromFormat($format, $label)->format($dbFormat);
            $row = $rows[$key] ?? null;

            $approved[] = $row ? (int) $row->approved : 0;
            $pending[] = $row ? (int) $row->pending : 0;
            $sold[] = $row ? (int) $row->sold : 0;
        }

        return [
            'labels' => $labels->values()->toArray(),
            'approved' => $approved,
            'pending' => $pending,
            'sold' => $sold,
        ];
    }

    public function getTotals(string $period = 'last_6_months'): array
    {
        [$startDate, $endDate] = $this->getDateRange($period);

        $row = Product::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw("
                SUM(status = 'approved') as approved,
                SUM(status = 'pending')  as pending,
                SUM(status = 'sold')     as sold,
                COUNT(*)                 as total
            ")
            ->first();

        return [
            'approved' => (int) ($row->approved ?? 0),
            'pending' => (int) ($row->pending ?? 0),
            'sold' => (int) ($row->sold ?? 0),
            'total' => (int) ($row->total ?? 0),
        ];
    }
}
