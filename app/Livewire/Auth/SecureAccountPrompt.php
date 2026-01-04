<?php

namespace App\Livewire\Auth;

use Livewire\Component;

class SecureAccountPrompt extends Component
{
    public function render()
    {
        return view('livewire.auth.secure-account-prompt')->layout('layouts.auth-simple');
    }
}
