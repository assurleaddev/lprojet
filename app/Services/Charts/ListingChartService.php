<?php

declare(strict_types=1);

namespace App\Services\Charts;

use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ListingChartService extends ChartService
{
    public function getNetworkValueData(string $period = 'last_6_months'): array
    {
        [$startDate, $endDate] = $this->getDateRange($period);

        $isLessThanMonth = $startDate->diffInMonths($endDate) < 1;

        $format = $isLessThanMonth ? 'd M Y' : 'M Y';
        $dbFormat = $isLessThanMonth ? 'Y-m-d' : 'Y-m';
        $intervalMethod = $isLessThanMonth ? 'addDay' : 'addMonth';
        $driver = DB::connection()->getDriverName();

        $dateTrunc = $isLessThanMonth
            ? 'DATE(created_at)'
            : ($driver === 'sqlite'
                ? "strftime('%Y-%m', created_at)"
                : "DATE_FORMAT(created_at, '%Y-%m')");

        $protectionPct = (float) config('settings.buyer_protection_fee_percentage', 5);
        $protectionFixed = (float) config('settings.buyer_protection_fee_fixed', 0.70);
        $shipping = (float) config('settings.delivery_fee_fixed', 25.00);

        $rows = Product::selectRaw("
                {$dateTrunc} as period,
                COUNT(*) as listing_count,
                SUM(price) as price_sum,
                SUM(price * (1 + {$protectionPct} / 100.0) + {$protectionFixed} + {$shipping}) as network_value
            ")
            ->where('status', 'approved')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->keyBy('period');

        $labels = $this->generateLabels($startDate, $endDate, $format, $intervalMethod);
        $series = [];
        $totalValue = 0;
        $totalListings = 0;

        foreach ($labels as $label) {
            $key = Carbon::createFromFormat($format, $label)->format($dbFormat);
            $row = $rows[$key] ?? null;
            $val = $row ? round((float) $row->network_value, 2) : 0;

            $series[] = $val;
            $totalValue += $val;
            $totalListings += $row ? (int) $row->listing_count : 0;
        }

        return [
            'labels' => $labels->values()->toArray(),
            'series' => $series,
            'total_value' => round($totalValue, 2),
            'total_listings' => $totalListings,
            'per_listing' => $totalListings > 0 ? round($totalValue / $totalListings, 2) : 0,
        ];
    }
}
