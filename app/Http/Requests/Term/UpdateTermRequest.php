<?php

declare(strict_types=1);

namespace App\Http\Requests\Term;

use App\Enums\Hooks\TermFilterHook;
use App\Http\Requests\FormRequest;
use App\Http\Requests\Term\Concerns\ValidatesTermFeaturedImage;
use App\Support\Facades\Hook;

class UpdateTermRequest extends FormRequest
{
    use ValidatesTermFeaturedImage;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Authorization is handled by the controller using policies
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $termId = $this->term ?? $this->route('id');

        $rules = [
            /** @example "Web Development" */
            'name' => 'required|string|max:255|unique:terms,name,'.$termId,

            /** @example "web-development" */
            'slug' => 'nullable|string|max:255|unique:terms,slug,'.$termId,

            /** @example "Topics related to web development and programming." */
            'description' => 'nullable|string',

            /** @example null */
            'parent_id' => 'nullable|exists:terms,id',

            /** @example null */
            'remove_featured_image' => 'nullable',
        ];

        // Add featured image validation if taxonomy supports it.
        $rules = $this->withFeaturedImageRule($rules);

        return Hook::applyFilters(TermFilterHook::TERM_UPDATE_VALIDATION_RULES, $rules, $this->route('taxonomy'));
    }
}
