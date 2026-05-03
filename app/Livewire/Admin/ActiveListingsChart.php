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
        $data = app(ListingChartService::class)->getListingData($this->period);
        $this->dispatch(
            'active-listings-update',
            labels: $data['labels'],
            approved: $data['approved'],
            pending: $data['pending'],
            sold: $data['sold'],
        );
    }

    public function render()
    {
        $service = app(ListingChartService::class);
        $data = $service->getListingData($this->period);
        $totals = $service->getTotals($this->period);

        return view('livewire.admin.active-listings-chart', [
            'labels' => $data['labels'],
            'approved' => $data['approved'],
            'pending' => $data['pending'],
            'sold' => $data['sold'],
            'totals' => $totals,
        ]);
    }
}
