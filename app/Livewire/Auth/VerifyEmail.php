<?php

namespace App\Livewire\Auth;

use Livewire\Component;

class VerifyEmail extends Component
{
    public $code = '';
    public $message = '';

    public function mount()
    {
        if (auth()->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('home'));
        }
    }

    public function verify()
    {
        $this->validate([
            'code' => 'required|digits:4',
        ]);

        $user = auth()->user();

        if ($user->verification_code === $this->code && now()->lt($user->verification_code_expires_at)) {
            $user->markEmailAsVerified();
            $user->verification_code = null;
            $user->verification_code_expires_at = null;
            $user->save();

            return redirect()->intended(route('auth.secure_account'));
        }

        $this->addError('code', __('Le code de vérification est invalide ou a expiré.'));
    }

    public function resend()
    {
        try {
            $code = auth()->user()->generateVerificationCode();
            auth()->user()->notify(new \App\Notifications\SendVerificationCode($code));

            $this->message = __('Un nouveau code a été envoyé.');
            $this->code = ''; // Clear input
        } catch (\Exception $e) {
            $this->message = __('Erreur lors de l\'envoi de l\'e-mail.');
        }
    }

    public function render()
    {
        return view('livewire.auth.verify-email')->layout('layouts.auth-simple');
    }
}
