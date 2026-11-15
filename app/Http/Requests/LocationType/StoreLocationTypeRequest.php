<?php

namespace App\Http\Requests\LocationType;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLocationTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->hasFile('image') && $this->input('image') === '') {
            $this->merge(['image' => null]);
        }
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('location_types', 'name')],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('location_types', 'slug')],
            'description' => ['nullable', 'string', 'max:1000'],
            'kind' => ['nullable', 'string', 'max:100'],
            'image' => ['nullable', 'image', 'max:4096'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
            'show_on_industries' => ['nullable', 'boolean'],
            'remove_image' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'Name',
            'slug' => 'Slug',
            'description' => 'Description',
            'kind' => 'Machine kind',
            'image' => 'Image',
            'sort_order' => 'Sort order',
            'is_active' => 'Active',
            'show_on_industries' => 'Show on industries',
        ];
    }
}
