<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Services\Charts\IncomeChartService;
use Livewire\Component;

class IncomeChartCommission extends Component
{
    public string $period = 'last_6_months';

    public function setPeriod(string $period): void
    {
        $this->period = $period;
        $data = app(IncomeChartService::class)->getIncomeData($this->period);
        $this->dispatch(
            'income-chart-commission-update',
            labels: $data['labels'],
            series: $data['commission'],
        );
    }

    public function render()
    {
        $data = app(IncomeChartService::class)->getIncomeData($this->period);

        return view('livewire.admin.income-chart-commission', [
            'labels' => $data['labels'],
            'series' => $data['commission'],
            'total' => number_format(array_sum($data['commission']), 2),
        ]);
    }
}
