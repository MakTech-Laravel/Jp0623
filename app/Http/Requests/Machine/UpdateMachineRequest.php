<?php

namespace App\Http\Requests\Machine;

use App\Models\Machine;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMachineRequest extends FormRequest
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
        /** @var Machine $machine */
        $machine = $this->route('machine');

        return [
            'machine_category_id' => ['required', 'integer', 'exists:machine_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('machines', 'slug')->ignore($machine->id),
            ],
            'kind' => ['required', 'string', 'max:100'],
            'tags' => ['nullable'],
            'description' => ['nullable', 'string', 'max:5000'],
            'image' => ['nullable', 'image', 'max:4096'],
            'capacity' => ['nullable', 'string', 'max:255'],
            'dimensions' => ['nullable', 'string', 'max:255'],
            'best_for' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:255'],
            'is_featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'remove_image' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'machine_category_id' => 'Category',
            'name' => 'Name',
            'slug' => 'Slug',
            'kind' => 'Kind',
            'tags' => 'Tags',
            'description' => 'Description',
            'image' => 'Image',
            'capacity' => 'Capacity',
            'dimensions' => 'Dimensions',
            'best_for' => 'Best for',
            'status' => 'Status',
            'is_featured' => 'Featured',
            'is_active' => 'Active',
            'sort_order' => 'Sort order',
        ];
    }
}
