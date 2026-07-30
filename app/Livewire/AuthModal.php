<?php

namespace App\Livewire;

use App\Livewire\Concerns\InteractsWithAuthModal;
use Livewire\Component;

class AuthModal extends Component
{
    use InteractsWithAuthModal;

    public function render()
    {
        return view('livewire.auth-modal');
    }
}
