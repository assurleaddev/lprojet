<?php

namespace App\Livewire\Auth;

use Livewire\Component;

class VerifyPhoneCode extends Component
{
    public $code = '';

    public function verify()
    {
        $this->validate(['code' => 'required|numeric|digits:6']);

        $user = auth()->user();

        if (
            $user->phone_verification_code === $this->code &&
            $user->phone_verification_code_expires_at &&
            $user->phone_verification_code_expires_at->isFuture()
        ) {
            $user->phone_verified_at = now();
            $user->phone_verification_code = null;
            $user->phone_verification_code_expires_at = null;
            $user->save();

            return redirect()->route('home'); // Or where ever next
        }

        $this->addError('code', __('Le code est invalide ou a expiré.'));
    }

    public function resend()
    {
        // Re-use logic or redirect back to phone input
        return redirect()->route('auth.verify_phone');
    }

    public function render()
    {
        return view('livewire.auth.verify-phone-code')->layout('layouts.auth-simple');
    }
}
