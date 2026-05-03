<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Services\Charts\IncomeChartService;
use Livewire\Component;

class GlobalIncomeCard extends Component
{
    public string $period = 'last_6_months';

    public function setPeriod(string $period): void
    {
        $this->period = $period;
    }

    public function render()
    {
        $data = app(IncomeChartService::class)->getGlobalIncome($this->period);

        return view('livewire.admin.global-income-card', [
            'gross' => number_format($data['gross'], 2),
            'order_count' => number_format($data['order_count']),
        ]);
    }
}
