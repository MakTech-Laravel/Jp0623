<?php

namespace App\Http\Requests\City;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'city' => trim((string) $this->input('city')),
            'state' => strtoupper(trim((string) $this->input('state'))),
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'city' => [
                'required',
                'string',
                'max:255',
                Rule::unique('cities', 'city')->where('state', $this->input('state')),
            ],
            'state' => ['required', 'string', 'size:2', 'alpha'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'city.unique' => 'This city already exists for that state.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'city' => 'City',
            'state' => 'State',
            'sort_order' => 'Sort order',
            'is_active' => 'Active',
        ];
    }
}
