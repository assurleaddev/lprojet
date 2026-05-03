<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Services\Charts\ListingChartService;
use Livewire\Component;

class ActiveListingsChart extends Component
{
    public string $period = 'last_6_months';

    public function setPeriod(string $period): void
    {
        $this->period = $period;
        $data = app(ListingChartService::class)->getNetworkValueData($this->period);
        $this->dispatch(
            'active-listings-update',
            labels: $data['labels'],
            series: $data['series'],
        );
    }

    public function render()
    {
        $data = app(ListingChartService::class)->getNetworkValueData($this->period);

        return view('livewire.admin.active-listings-chart', [
            'labels' => $data['labels'],
            'series' => $data['series'],
            'total_value' => number_format($data['total_value'], 2),
            'total_listings' => number_format($data['total_listings']),
            'per_listing' => number_format($data['per_listing'], 2),
        ]);
    }
}
