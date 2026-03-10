<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use App\Models\UserBundleDiscount;
use Illuminate\Support\Facades\Auth;

use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class BundleDiscounts extends Component
{
    // Fixed preset tiers (Vinted-style)
    public bool $tier2Enabled = false;
    public int $tier2Percentage = 5;

    public bool $tier3Enabled = false;
    public int $tier3Percentage = 10;

    public bool $tier5Enabled = false;
    public int $tier5Percentage = 20;

    public array $percentageOptions = [5, 10, 15, 20, 25, 30];

    public function mount()
    {
        $this->loadTiers();
    }

    public function loadTiers()
    {
        $discounts = UserBundleDiscount::where('user_id', Auth::id())->get()->keyBy('min_items');

        if ($tier = $discounts->get(2)) {
            $this->tier2Enabled = true;
            $this->tier2Percentage = $tier->discount_percentage;
        }
        if ($tier = $discounts->get(3)) {
            $this->tier3Enabled = true;
            $this->tier3Percentage = $tier->discount_percentage;
        }
        if ($tier = $discounts->get(5)) {
            $this->tier5Enabled = true;
            $this->tier5Percentage = $tier->discount_percentage;
        }
    }

    public function save()
    {
        $userId = Auth::id();
        $tiers = [
            2 => ['enabled' => $this->tier2Enabled, 'percentage' => $this->tier2Percentage],
            3 => ['enabled' => $this->tier3Enabled, 'percentage' => $this->tier3Percentage],
            5 => ['enabled' => $this->tier5Enabled, 'percentage' => $this->tier5Percentage],
        ];

        foreach ($tiers as $minItems => $config) {
            if ($config['enabled']) {
                UserBundleDiscount::updateOrCreate(
                    ['user_id' => $userId, 'min_items' => $minItems],
                    ['discount_percentage' => $config['percentage']]
                );
            } else {
                UserBundleDiscount::where('user_id', $userId)
                    ->where('min_items', $minItems)
                    ->delete();
            }
        }

        $this->dispatch('toast', message: 'Bundle discounts saved!', type: 'success');
    }

    public function render()
    {
        return view('livewire.settings.bundle-discounts');
    }
}
