<?php

namespace App\Livewire;

use App\Livewire\Concerns\InteractsWithAuthModal;
use Livewire\Component;

class PopupAuthModal extends Component
{
    use InteractsWithAuthModal;

    public function render()
    {
        return view('livewire.popup-auth-modal');
    }
}
