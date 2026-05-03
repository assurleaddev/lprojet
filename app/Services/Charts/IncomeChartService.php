<?php

declare(strict_types=1);

namespace App\Services\Charts;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class IncomeChartService extends ChartService
{
    public function getIncomeData(string $period = 'last_6_months'): array
    {
        [$startDate, $endDate] = $this->getDateRange($period);

        $isLessThanMonth = $startDate->diffInMonths($endDate) < 1;

        $format = $isLessThanMonth ? 'd M Y' : 'M Y';
        $dbFormat = $isLessThanMonth ? 'Y-m-d' : 'Y-m';
        $intervalMethod = $isLessThanMonth ? 'addDay' : 'addMonth';

        $labels = $this->generateLabels($startDate, $endDate, $format, $intervalMethod);

        $rows = $this->fetchRows($startDate, $endDate, $isLessThanMonth);

        $commission = [];
        $protection = [];
        $verification = [];
        $total = [];

        foreach ($labels as $label) {
            $key = Carbon::createFromFormat($format, $label)->format($dbFormat);
            $row = $rows[$key] ?? null;

            $commission[] = $row ? round((float) $row->commission, 2) : 0;
            $protection[] = $row ? round((float) $row->protection, 2) : 0;
            $verification[] = $row ? round((float) $row->verification, 2) : 0;
            $total[] = $row ? round((float) $row->total, 2) : 0;
        }

        return [
            'labels' => $labels->values()->toArray(),
            'commission' => $commission,
            'protection' => $protection,
            'verification' => $verification,
            'total' => $total,
        ];
    }

    public function getGlobalIncome(string $period = 'last_6_months'): array
    {
        [$startDate, $endDate] = $this->getDateRange($period);

        $row = Order::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('SUM(total_amount) as gross, COUNT(*) as order_count')
            ->first();

        return [
            'gross' => round((float) ($row->gross ?? 0), 2),
            'order_count' => (int) ($row->order_count ?? 0),
        ];
    }

    public function getOrderSourceData(string $period = 'last_6_months'): array
    {
        [$startDate, $endDate] = $this->getDateRange($period);

        $counts = Order::selectRaw('source, COUNT(*) as total')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('source')
            ->pluck('total', 'source');

        return [
            'direct' => (int) ($counts['direct'] ?? 0),
            'offer' => (int) ($counts['offer'] ?? 0),
            'live' => (int) ($counts['live'] ?? 0),
        ];
    }

    private function fetchRows(Carbon $startDate, Carbon $endDate, bool $isLessThanMonth): \Illuminate\Support\Collection
    {
        $driver = DB::connection()->getDriverName();

        if ($isLessThanMonth) {
            $dateTrunc = 'DATE(created_at)';
            $groupKey = 'period';
        } else {
            $dateTrunc = $driver === 'sqlite'
                ? "strftime('%Y-%m', created_at)"
                : "DATE_FORMAT(created_at, '%Y-%m')";
            $groupKey = 'period';
        }

        return Order::selectRaw("
                {$dateTrunc} as period,
                SUM(platform_commission)  as commission,
                SUM(buyer_protection_fee) as protection,
                SUM(verification_fee)     as verification,
                SUM(platform_commission + buyer_protection_fee + verification_fee) as total
            ")
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->keyBy('period');
    }
}
