<?php

declare(strict_types=1);

namespace App\Http\Requests\Term;

use App\Enums\Hooks\TermFilterHook;
use App\Http\Requests\FormRequest;
use App\Http\Requests\Term\Concerns\ValidatesTermFeaturedImage;
use App\Support\Facades\Hook;
use Illuminate\Contracts\Validation\ValidationRule;

class StoreTermRequest extends FormRequest
{
    use ValidatesTermFeaturedImage;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Authorization is handled by the controller using policies.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $rules = [
            /** @example "Technology" */
            'name' => 'required|string|max:255|unique:terms,name',

            /** @example "technology" */
            'slug' => 'nullable|string|max:255|unique:terms,slug',

            /** @example "Articles related to technology and software development." */
            'description' => 'nullable|string',

            /** @example null */
            'parent_id' => 'nullable|exists:terms,id',

            /** @example "post" */
            'post_type' => 'nullable|string',

            /** @example null */
            'post_id' => 'nullable|numeric',

            /** @example null */
            'remove_featured_image' => 'nullable',
        ];

        // Add featured image validation if taxonomy supports it
        $rules = $this->withFeaturedImageRule($rules);

        return Hook::applyFilters(TermFilterHook::TERM_STORE_VALIDATION_RULES, $rules, $this->route('taxonomy'));
    }
}
