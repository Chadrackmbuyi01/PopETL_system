<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FetchPopulationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'country' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\p{L}\s\-\.\']+$/u'],
            'year'    => ['sometimes', 'nullable', 'integer', 'between:1900,2025'],
        ];
    }

    public function messages(): array
    {
        return [
            'country.required' => 'A country name is required.',
            'country.regex'    => 'Country name may only contain letters, spaces, hyphens, and dots.',
            'country.min'      => 'Country name must be at least 2 characters.',
            'country.max'      => 'Country name must not exceed 100 characters.',
            'year.integer'     => 'Year must be a valid integer.',
            'year.between'     => 'Year must be between 1900 and 2025.',
        ];
    }

    /**
     * Normalise country name to title case before validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('country')) {
            $this->merge([
                'country' => trim($this->input('country')),
            ]);
        }
    }
}