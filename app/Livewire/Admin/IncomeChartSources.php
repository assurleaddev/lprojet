<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Services\Charts\IncomeChartService;
use Livewire\Component;

class IncomeChartSources extends Component
{
    public string $period = 'last_6_months';

    public function setPeriod(string $period): void
    {
        $this->period = $period;
        $counts = app(IncomeChartService::class)->getOrderSourceData($this->period);
        $this->dispatch(
            'income-chart-sources-update',
            direct: $counts['direct'],
            offer: $counts['offer'],
            live: $counts['live'],
            total: $counts['direct'] + $counts['offer'] + $counts['live'],
        );
    }

    public function render()
    {
        $counts = app(IncomeChartService::class)->getOrderSourceData($this->period);
        $total = $counts['direct'] + $counts['offer'] + $counts['live'];

        return view('livewire.admin.income-chart-sources', [
            'direct' => $counts['direct'],
            'offer' => $counts['offer'],
            'live' => $counts['live'],
            'total' => $total,
        ]);
    }
}
