<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class GoogleLoginRequest extends FormRequest
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
            /** The Google ID token returned by Google Sign-In on the device. */
            'id_token' => ['required', 'string', 'max:4096'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_token.required' => 'Le jeton Google est requis.',
        ];
    }
}
