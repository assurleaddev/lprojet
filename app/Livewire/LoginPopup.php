<?php

namespace App\Livewire;

use Livewire\Component;

class LoginPopup extends Component
{
    public $open = false;
    public $view = 'menu'; // menu, register, login

    // Registration inputs
    public $username = '';
    public $email = '';
    public $password = '';
    public $newsletter = false;
    public $terms = false;

    // Login inputs
    public $login_identifier = '';
    public $login_password = '';

    // Forgot Password inputs
    public $forgot_email = '';
    public $status = ''; // For success messages

    protected $listeners = ['open-login-popup' => 'openPopup'];

    public function openPopup()
    {
        $this->open = true;
        $this->view = 'menu'; // Reset to menu
    }

    public function closePopup()
    {
        $this->open = false;
    }

    public function setView($view)
    {
        $this->view = $view;
    }

    public function register()
    {
        $this->validate([
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:7', // Vinted style: 7+ chars, 1 letter, 1 number usually
            'terms' => 'accepted',
        ]);

        $user = \App\Models\User::create([
            'username' => $this->username,
            'email' => $this->email,
            'password' => bcrypt($this->password),
            'first_name' => $this->username, // Fallback
            'last_name' => '', // Fallback
        ]);

        // Newsletter logic can be added here (e.g. saving to separate table or user preference)

        auth()->login($user);

        // Send verification code
        $code = $user->generateVerificationCode();
        $user->notify(new \App\Notifications\SendVerificationCode($code));

        return redirect()->route('verify-email');
    }

    public function login()
    {
        $this->validate([
            'login_identifier' => 'required|string',
            'login_password' => 'required|string',
        ]);

        $fieldType = filter_var($this->login_identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (auth()->attempt([$fieldType => $this->login_identifier, 'password' => $this->login_password])) {
            return redirect()->intended(route('home'));
        }

        $this->addError('login_identifier', __('Identifiants incorrects.'));
    }

    public function sendResetLink()
    {
        $this->validate(['forgot_email' => 'required|email']);

        $status = \Illuminate\Support\Facades\Password::sendResetLink(['email' => $this->forgot_email]);

        if ($status === \Illuminate\Support\Facades\Password::RESET_LINK_SENT) {
            $this->status = __($status);
        } else {
            $this->addError('forgot_email', __($status));
        }
    }

    public function render()
    {
        return view('livewire.auth.login-popup');
    }
}