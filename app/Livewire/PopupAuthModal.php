<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class PopupAuthModal extends Component
{
    public bool $modalOpen = false;
    public string $view = 'signup-social'; // Default view

    // Form properties
    public string $username = '';
    public string $email = '';
    public string $password = '';
    public bool $termsAccepted = false;
    public bool $offersAccepted = false;

    // Listen for a global event to open the modal
    protected $listeners = ['openAuthModal'];

    // Validation rules
    protected function rules()
    {
        if ($this->view === 'signup-email') {
            return [
                'username' => 'required|string|max:255|unique:users',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:7|regex:/^(?=.*[A-Za-z])(?=.*\d).+$/', // At least 1 letter, 1 number
                'termsAccepted' => 'accepted',
            ];
        }

        if ($this->view === 'login-email') {
            return [
                'email' => 'required|email',
                'password' => 'required',
            ];
        }

        return [];
    }

    // Custom validation messages
    protected $messages = [
        'password.regex' => 'Password must have at least 1 letter and 1 number.',
        'termsAccepted.accepted' => 'You must accept the terms and conditions.',
    ];

    public function render()
    {
        return view('livewire.popup-auth-modal');
    }

    /**
     * Listener method to open the modal.
     */
    public function openAuthModal($view = 'signup-social')
    {
        $this->resetValidation();
        $this->resetInputFields();
        $this->view = $view;
        $this->modalOpen = true;
    }

    /**
     * Change the current view of the modal.
     */
    public function setView(string $newView)
    {
        $this->resetValidation();
        $this->view = $newView;
    }

    /**
     * Handle the registration form submission.
     */
    public function register()
    {
        $this->validate();

        $user = User::create([
            'name' => $this->username,
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ]);

        Auth::login($user);

        $this->modalOpen = false;
        return redirect()->intended('/');
    }

    /**
     * Handle the login form submission.
     */
    public function login()
    {
        $credentials = $this->validate();

        if (Auth::attempt($credentials)) {
            session()->regenerate();
            $this->modalOpen = false;
            return redirect()->intended('/');
        }

        $this->addError('email', 'The provided credentials do not match our records.');
    }

    /**
     * Reset form fields when closing or switching views.
     */
    private function resetInputFields()
    {
        $this->username = '';
        $this->email = '';
        $this->password = '';
        $this->termsAccepted = false;
        $this->offersAccepted = false;
    }
}