<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FetchPopulationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'country' => ['required', 'string', 'max:100'],
            'year'    => ['nullable', 'integer', 'min:1950', 'max:' . date('Y')],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'country.required' => 'Please enter a country name.',
            'year.integer'     => 'The year must be a valid number.',
        ];
    }
}
