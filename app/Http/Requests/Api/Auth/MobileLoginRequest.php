<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * API login: the identifier may be an email address or a username.
 * New clients send it as `login`; older app builds still send `email`.
 */
class MobileLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            /** @example "superadmin@example.com" */
            'login' => ['required_without:email', 'nullable', 'string', 'max:255'],

            /** @example "superadmin@example.com" */
            'email' => ['required_without:login', 'nullable', 'string', 'max:255'],

            /** @example "12345678" */
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'login.required_without' => "L'e-mail ou le nom d'utilisateur est requis.",
            'email.required_without' => "L'e-mail ou le nom d'utilisateur est requis.",
            'password.required' => 'Le mot de passe est requis.',
        ];
    }

    /** The email or username the user typed, trimmed. */
    public function identifier(): string
    {
        return trim((string) ($this->input('login') ?? $this->input('email')));
    }
}
